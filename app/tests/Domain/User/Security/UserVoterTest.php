<?php

namespace App\Tests\Domain\User\Security;

use App\Domain\Entity\User\User;
use App\Domain\Entity\User\UserRepository;
use App\Domain\User\Security\UserVoter;
use App\Test\SecurityUserTrait;
use App\Test\UserDomainTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\NullToken;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 * The only voter in the application with real branching logic: it protects
 * against deleting yourself and against deleting the last administrator.
 */
class UserVoterTest extends TestCase
{
    use UserDomainTrait, SecurityUserTrait;

    private UserRepository&MockObject $userRepository;

    private UserVoter $voter;

    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepository::class);
        $this->voter = new UserVoter($this->userRepository);
    }

    public static function supportedAttributes(): iterable
    {
        yield 'list' => ['USER_LIST'];
        yield 'create' => ['USER_CREATE'];
        yield 'show' => ['USER_SHOW'];
        yield 'edit' => ['USER_EDIT'];
        yield 'delete' => ['USER_DELETE'];
        yield 'undelete' => ['USER_UNDELETE'];
    }

    #[DataProvider('supportedAttributes')]
    public function testSupportsItsOwnAttributes(string $attribute): void
    {
        self::assertTrue($this->voter->supportsAttribute($attribute));
    }

    public function testDoesNotSupportForeignAttributes(): void
    {
        self::assertFalse($this->voter->supportsAttribute('CONNECT_LIST'));
    }

    public function testSupportsUserAndNullSubjectTypes(): void
    {
        self::assertTrue($this->voter->supportsType(User::class));
        self::assertTrue($this->voter->supportsType('null'));
        self::assertFalse($this->voter->supportsType(\stdClass::class));
    }

    public function testAbstainsOnUnsupportedAttribute(): void
    {
        $token = self::createTokenForUser(self::createAdmin());

        self::assertSame(
            VoterInterface::ACCESS_ABSTAIN,
            $this->voter->vote($token, null, ['SOMETHING_ELSE'])
        );
    }

    public function testAbstainsWhenSubjectIsRequiredButMissing(): void
    {
        $token = self::createTokenForUser(self::createAdmin());

        self::assertSame(
            VoterInterface::ACCESS_ABSTAIN,
            $this->voter->vote($token, null, ['USER_EDIT'])
        );
    }

    #[DataProvider('supportedAttributes')]
    public function testDeniesEverythingToNonAdministrators(string $attribute): void
    {
        $token = self::createTokenForUser(self::createUser());

        self::assertSame(
            VoterInterface::ACCESS_DENIED,
            $this->voter->vote($token, self::createUser('other', 'other@greyface.test'), [$attribute])
        );
    }

    public function testDeniesEverythingToAnonymousTokens(): void
    {
        self::assertSame(
            VoterInterface::ACCESS_DENIED,
            $this->voter->vote(new NullToken(), null, ['USER_LIST'])
        );
    }

    public function testAllowsAdministratorToListAndCreate(): void
    {
        $token = self::createTokenForUser(self::createAdmin());

        self::assertSame(VoterInterface::ACCESS_GRANTED, $this->voter->vote($token, null, ['USER_LIST']));
        self::assertSame(VoterInterface::ACCESS_GRANTED, $this->voter->vote($token, null, ['USER_CREATE']));
    }

    public function testAllowsAdministratorToShowAnyUser(): void
    {
        $token = self::createTokenForUser(self::createAdmin());
        $deleted = self::createUser('gone', 'gone@greyface.test')->delete();

        self::assertSame(VoterInterface::ACCESS_GRANTED, $this->voter->vote($token, $deleted, ['USER_SHOW']));
    }

    public function testAllowsEditingAnActiveUserButNotADeletedOne(): void
    {
        $token = self::createTokenForUser(self::createAdmin());

        $active = self::createUser('active', 'active@greyface.test');
        $deleted = self::createUser('gone', 'gone@greyface.test')->delete();

        self::assertSame(VoterInterface::ACCESS_GRANTED, $this->voter->vote($token, $active, ['USER_EDIT']));
        self::assertSame(VoterInterface::ACCESS_DENIED, $this->voter->vote($token, $deleted, ['USER_EDIT']));
    }

    public function testAllowsDeletingAnOrdinaryUser(): void
    {
        $token = self::createTokenForUser(self::createAdmin());
        $victim = self::createUser('victim', 'victim@greyface.test');

        $this->userRepository->expects(self::never())->method('countAdministrators');

        self::assertSame(VoterInterface::ACCESS_GRANTED, $this->voter->vote($token, $victim, ['USER_DELETE']));
    }

    public function testDeniesDeletingAnAlreadyDeletedUser(): void
    {
        $token = self::createTokenForUser(self::createAdmin());
        $deleted = self::createUser('gone', 'gone@greyface.test')->delete();

        self::assertSame(VoterInterface::ACCESS_DENIED, $this->voter->vote($token, $deleted, ['USER_DELETE']));
    }

    public function testDeniesDeletingYourself(): void
    {
        $admin = self::createAdmin();
        $token = self::createTokenForUser($admin);

        self::assertSame(VoterInterface::ACCESS_DENIED, $this->voter->vote($token, $admin, ['USER_DELETE']));
    }

    public function testAllowsDeletingAnotherAdministratorWhenMoreThanOneRemains(): void
    {
        $token = self::createTokenForUser(self::createAdmin());
        $otherAdmin = self::createAdmin('second', 'second@greyface.test');

        $this->userRepository->method('countAdministrators')->willReturn(2);

        self::assertSame(VoterInterface::ACCESS_GRANTED, $this->voter->vote($token, $otherAdmin, ['USER_DELETE']));
    }

    public function testDeniesDeletingTheLastAdministrator(): void
    {
        $token = self::createTokenForUser(self::createAdmin());
        $otherAdmin = self::createAdmin('second', 'second@greyface.test');

        $this->userRepository->method('countAdministrators')->willReturn(1);

        self::assertSame(VoterInterface::ACCESS_DENIED, $this->voter->vote($token, $otherAdmin, ['USER_DELETE']));
    }

    public function testUndeleteRequiresADeletedUser(): void
    {
        $token = self::createTokenForUser(self::createAdmin());

        $active = self::createUser('active', 'active@greyface.test');
        $deleted = self::createUser('gone', 'gone@greyface.test')->delete();

        self::assertSame(VoterInterface::ACCESS_GRANTED, $this->voter->vote($token, $deleted, ['USER_UNDELETE']));
        self::assertSame(VoterInterface::ACCESS_DENIED, $this->voter->vote($token, $active, ['USER_UNDELETE']));
    }
}

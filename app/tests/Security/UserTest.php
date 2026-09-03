<?php

namespace App\Tests\Security;

use App\Domain\Entity\User\User as DomainUser;
use App\Security\User;
use App\Test\UserDomainTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\User\InMemoryUser;

class UserTest extends TestCase
{
    use UserDomainTrait;

    public function testIsBuiltFromADomainUser(): void
    {
        $domainUser = self::createUser('admin', 'admin@greyface.test');

        $user = User::fromUser($domainUser);

        self::assertSame($domainUser->getId(), $user->getId());
        self::assertSame('admin', $user->getUsername());
        self::assertSame('admin', $user->getUserIdentifier());
        self::assertSame('admin@greyface.test', $user->getEmail());
        self::assertSame($domainUser->getPassword(), $user->getPassword());
    }

    public function testPrefixesAndUppercasesRolesForSymfony(): void
    {
        $user = User::fromUser(self::createUser());
        self::assertSame(['ROLE_USER'], $user->getRoles());

        $admin = User::fromUser(self::createAdmin());
        self::assertSame(['ROLE_USER', 'ROLE_ADMIN'], $admin->getRoles());
    }

    public function testRolesAreMemoizedAndStable(): void
    {
        $user = User::fromUser(self::createAdmin());

        self::assertSame($user->getRoles(), $user->getRoles());
    }

    public function testKnowsWhetherItIsAnAdministrator(): void
    {
        self::assertTrue(User::fromUser(self::createAdmin())->isAdministrator());
        self::assertFalse(User::fromUser(self::createUser())->isAdministrator());
    }

    public function testEqualityIsByIdentity(): void
    {
        $domainUser = self::createUser();

        $user = User::fromUser($domainUser);

        self::assertTrue($user->equals($domainUser), 'the same identity must compare equal');
        self::assertTrue($user->equals(User::fromUser($domainUser)));
        self::assertFalse($user->equals(self::createUser('someone-else', 'else@greyface.test')));
    }

    /**
     * isEqualTo() is what Symfony calls on every request to decide whether a
     * session token is still valid, so a changed username or role must
     * invalidate it.
     */
    public function testIsEqualToRejectsAChangedUsernameOrRole(): void
    {
        $domainUser = self::createUser('admin', 'admin@greyface.test');
        $user = User::fromUser($domainUser);

        self::assertTrue($user->isEqualTo(User::fromUser($domainUser)));

        $renamed = User::fromUser(
            DomainUser::createLocalUser($domainUser->getId(), 'renamed', 'admin@greyface.test', DomainUser::ROLE_USER)
        );
        self::assertFalse($user->isEqualTo($renamed));

        $promoted = User::fromUser(
            DomainUser::createLocalUser($domainUser->getId(), 'admin', 'admin@greyface.test', DomainUser::ROLE_ADMIN)
        );
        self::assertFalse($user->isEqualTo($promoted));
    }

    public function testIsEqualToRejectsForeignUserClasses(): void
    {
        self::assertFalse(
            User::fromUser(self::createUser())->isEqualTo(new InMemoryUser('someone', 'password'))
        );
    }

    public function testErasingCredentialsIsANoOp(): void
    {
        $user = User::fromUser(self::createUser());
        $password = $user->getPassword();

        $user->eraseCredentials();

        self::assertSame($password, $user->getPassword());
    }
}

<?php

namespace App\Tests\Domain\User\Validator;

use App\Domain\Entity\User\UserRepository;
use App\Domain\User\Validator\UniqueUsername;
use App\Domain\User\Validator\UniqueUsernameValidator;
use App\Test\UserDomainTrait;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class UniqueUsernameValidatorTest extends ConstraintValidatorTestCase
{
    use UserDomainTrait;

    private UserRepository&MockObject $userRepository;

    protected function createValidator(): UniqueUsernameValidator
    {
        $this->userRepository = $this->createMock(UserRepository::class);

        return new UniqueUsernameValidator($this->userRepository);
    }

    public function testRejectsAForeignConstraint(): void
    {
        $this->expectException(UnexpectedTypeException::class);
        $this->validator->validate('admin', new NotBlank());
    }

    public function testIgnoresNull(): void
    {
        $this->validator->validate(null, new UniqueUsername());
        $this->assertNoViolation();
    }

    public function testIgnoresAnEmptyString(): void
    {
        $this->validator->validate('', new UniqueUsername());
        $this->assertNoViolation();
    }

    public function testRejectsANonStringValue(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->validator->validate(42, new UniqueUsername());
    }

    public function testAcceptsAnUnusedUsername(): void
    {
        $this->userRepository->method('findByUsername')->willReturn(null);

        $this->validator->validate('brand-new', new UniqueUsername());

        $this->assertNoViolation();
    }

    public function testRejectsAUsernameTakenBySomeoneElse(): void
    {
        $this->setObject(self::createUser('me', 'me@greyface.test'));
        $conflicting = self::createUser('taken', 'taken@greyface.test');
        $this->userRepository->method('findByUsername')->willReturn($conflicting);

        $constraint = new UniqueUsername();
        $this->validator->validate('taken', $constraint);

        $this->buildViolation($constraint->message)
             ->setParameter('{{ value }}', 'taken')
             ->setInvalidValue('taken')
             ->setCause($conflicting)
             ->assertRaised();
    }

    /**
     * Saving a user without changing its username must not report a conflict
     * with itself — the branch that made editing a user possible at all.
     */
    public function testAcceptsAUserKeepingItsOwnUsername(): void
    {
        $user = self::createUser('me', 'me@greyface.test');
        $this->setObject($user);
        $this->userRepository->method('findByUsername')->willReturn($user);

        $this->validator->validate('me', new UniqueUsername());

        $this->assertNoViolation();
    }

    public function testReportsAConflictWhenThereIsNoObjectContext(): void
    {
        $this->setObject(null);
        $conflicting = self::createUser('taken', 'taken@greyface.test');
        $this->userRepository->method('findByUsername')->willReturn($conflicting);

        $constraint = new UniqueUsername();
        $this->validator->validate('taken', $constraint);

        $this->buildViolation($constraint->message)
             ->setParameter('{{ value }}', 'taken')
             ->setInvalidValue('taken')
             ->setCause($conflicting)
             ->assertRaised();
    }

    public function testIsSameObjectRequiresBothSidesToBeIdentifiable(): void
    {
        $user = self::createUser();

        self::assertTrue($this->validator->isSameObject($user, $user));
        self::assertFalse($this->validator->isSameObject($user, self::createUser('other', 'other@greyface.test')));
        self::assertFalse($this->validator->isSameObject(new \stdClass(), $user));
        self::assertFalse($this->validator->isSameObject($user, new \stdClass()));
    }
}

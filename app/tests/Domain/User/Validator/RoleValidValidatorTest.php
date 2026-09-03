<?php

namespace App\Tests\Domain\User\Validator;

use App\Domain\Entity\User\User;
use App\Domain\Entity\User\UserRepository;
use App\Domain\User\Validator\RoleValid;
use App\Domain\User\Validator\RoleValidValidator;
use App\Test\UserDomainTrait;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

/**
 * Guards the rule that the last administrator cannot be demoted, which had no
 * coverage at all. Locking yourself out of the application is a one-way door.
 */
class RoleValidValidatorTest extends ConstraintValidatorTestCase
{
    use UserDomainTrait;

    private UserRepository&MockObject $userRepository;

    protected function createValidator(): RoleValidValidator
    {
        $this->userRepository = $this->createMock(UserRepository::class);

        return new RoleValidValidator($this->userRepository);
    }

    public function testRejectsAForeignConstraint(): void
    {
        $this->expectException(UnexpectedTypeException::class);
        $this->validator->validate(User::ROLE_USER, new NotBlank());
    }

    public function testIgnoresNull(): void
    {
        $this->validator->validate(null, new RoleValid());
        $this->assertNoViolation();
    }

    public function testIgnoresAnEmptyString(): void
    {
        $this->validator->validate('', new RoleValid());
        $this->assertNoViolation();
    }

    public function testRejectsANonStringValue(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->validator->validate(['admin'], new RoleValid());
    }

    public function testIgnoresContextsThatAreNotIdentifiable(): void
    {
        $this->setObject(new \stdClass());
        $this->userRepository->expects(self::never())->method('findById');

        $this->validator->validate(User::ROLE_USER, new RoleValid());

        $this->assertNoViolation();
    }

    public function testAllowsPromotingSomeoneToAdministrator(): void
    {
        $user = self::createUser();
        $this->setObject($user);
        $this->userRepository->method('findById')->willReturn($user);
        $this->userRepository->expects(self::never())->method('countAdministrators');

        $this->validator->validate(User::ROLE_ADMIN, new RoleValid());

        $this->assertNoViolation();
    }

    public function testAllowsChangingTheRoleOfAnOrdinaryUser(): void
    {
        $user = self::createUser();
        $this->setObject($user);
        $this->userRepository->method('findById')->willReturn($user);

        $this->validator->validate(User::ROLE_USER, new RoleValid());

        $this->assertNoViolation();
    }

    public function testAllowsDemotingAnAdministratorWhileAnotherRemains(): void
    {
        $admin = self::createAdmin();
        $this->setObject($admin);
        $this->userRepository->method('findById')->willReturn($admin);
        $this->userRepository->method('countAdministrators')->willReturn(2);

        $this->validator->validate(User::ROLE_USER, new RoleValid());

        $this->assertNoViolation();
    }

    public function testRefusesToDemoteTheLastAdministrator(): void
    {
        $admin = self::createAdmin();
        $this->setObject($admin);
        $this->userRepository->method('findById')->willReturn($admin);
        $this->userRepository->method('countAdministrators')->willReturn(1);

        $constraint = new RoleValid();
        $this->validator->validate(User::ROLE_USER, $constraint);

        $this->buildViolation($constraint->message)
             ->setParameter('{{ value }}', '"user"')
             ->setInvalidValue(User::ROLE_USER)
             ->setCause($admin)
             ->assertRaised();
    }

    public function testFailsLoudlyWhenTheUserBeingValidatedNoLongerExists(): void
    {
        $this->setObject(self::createUser());
        $this->userRepository->method('findById')->willReturn(null);

        $this->expectException(UnexpectedValueException::class);
        $this->validator->validate(User::ROLE_USER, new RoleValid());
    }
}

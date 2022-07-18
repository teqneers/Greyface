<?php

namespace App\Domain\User\Validator;

use App\Domain\Entity\User\User;
use App\Domain\Entity\User\UserRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use App\Domain\Identifiable;

class RoleValidValidator extends ConstraintValidator
{
    public function __construct(
        private readonly UserRepository $userRepository
    ) {
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof RoleValid) {
            throw new UnexpectedTypeException($constraint, RoleValid::class);
        }
        if ($value === null) {
            return;
        }
        if (!is_string($value)) {
            throw new UnexpectedValueException($value, 'string');
        }
        if ($value === '') {
            return;
        }

        $object = $this->context->getObject();
        if (!$object instanceof Identifiable) {
            return;
        }
        $targetUser = $this->userRepository->findById($object->getId());
        if (!$targetUser) {
            throw new UnexpectedValueException($targetUser, User::class);
        }
        if ($value === User::ROLE_ADMIN
            || !$targetUser->isAdministrator()
            || $this->userRepository->countAdministrators() > 1
        ) {
            return;
        }
        $this->context->buildViolation($constraint->message)
                      ->setParameter('{{ value }}', $this->formatValue($value))
                      ->setCause($targetUser)
                      ->setInvalidValue($value)
                      ->addViolation();
    }
}

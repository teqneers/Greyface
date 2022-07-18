<?php

namespace App\Domain\User\Validator;

use App\Domain\Entity\User\UserRepository;
use App\Domain\Identifiable;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class UniqueUsernameValidator extends ConstraintValidator
{
    public function __construct(
        private readonly UserRepository $userRepository
    )
    {
    }

    /**
     * @param object $context
     * @param object $other
     * @return bool
     */
    public function isSameObject(object $context, object $other): bool
    {
        return $context instanceof Identifiable
            && $other instanceof Identifiable
            && $context->getId() === $other->getId();
    }

     public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof UniqueUsername) {
            throw new UnexpectedTypeException($constraint, UniqueUsername::class);
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
        $try = $this->userRepository->findByUsername($value);
        if (!$try || ($object && $this->isSameObject($object, $try))) {
            return;
        }

        $this->context->buildViolation($constraint->message)
            ->setParameter('{{ value }}', $value)
            ->setCause($try)
            ->setInvalidValue($value)
            ->addViolation();
    }
}

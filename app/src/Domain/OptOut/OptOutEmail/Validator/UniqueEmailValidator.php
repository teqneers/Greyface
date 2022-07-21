<?php

namespace App\Domain\OptOut\OptOutEmail\Validator;

use App\Domain\Entity\OptOut\OptOutEmail\OptOutEmailRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class UniqueEmailValidator extends ConstraintValidator
{
    public function __construct(
        private readonly OptOutEmailRepository $optOutEmailRepository
    ) {
    }

     public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof UniqueEmail) {
            throw new UnexpectedTypeException($constraint, UniqueEmail::class);
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

        $try = $this->optOutEmailRepository->findById($value);
        if (!$try) {
            return;
        }

        $this->context->buildViolation($constraint->message)
            ->setParameter('{{ value }}', $value)
            ->setCause($try)
            ->setInvalidValue($value)
            ->addViolation();
    }
}

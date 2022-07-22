<?php

namespace App\Domain\OptIn\OptInEmail\Validator;

use App\Domain\Entity\OptIn\OptInEmail\OptInEmailRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class UniqueEmailValidator extends ConstraintValidator
{
    public function __construct(
        private readonly OptInEmailRepository $optInEmailRepository
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

        $try = $this->optInEmailRepository->findById($value);
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

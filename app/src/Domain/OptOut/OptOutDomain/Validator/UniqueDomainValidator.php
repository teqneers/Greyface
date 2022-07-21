<?php

namespace App\Domain\OptOut\OptOutDomain\Validator;

use App\Domain\Entity\OptOut\OptOutDomain\OptOutDomainRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class UniqueDomainValidator extends ConstraintValidator
{
    public function __construct(
        private readonly OptOutDomainRepository $optOutDomainRepository
    ) {
    }

     public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof UniqueDomain) {
            throw new UnexpectedTypeException($constraint, UniqueDomain::class);
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

        $try = $this->optOutDomainRepository->findById($value);
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

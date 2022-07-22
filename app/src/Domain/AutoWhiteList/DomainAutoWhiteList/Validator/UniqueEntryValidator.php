<?php

namespace App\Domain\AutoWhiteList\DomainAutoWhiteList\Validator;

use App\Domain\Entity\AutoWhiteList\DomainAutoWhiteList\DomainAutoWhiteListRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class UniqueEntryValidator extends ConstraintValidator
{
    public function __construct(
        private readonly DomainAutoWhiteListRepository $domainAutoWhiteListRepository
    ) {
    }

     public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof UniqueEntry) {
            throw new UnexpectedTypeException($constraint, UniqueEntry::class);
        }
        if ($value === null) {
            return;
        }

        $try = $this->domainAutoWhiteListRepository->find([
            'domain' => $value->domain,
            'source' => $value->source
        ]);
        if (!$try) {
            return;
        }

        $this->context->buildViolation($constraint->message)
            ->setCause($try)
            ->setInvalidValue($value)
            ->addViolation();
    }
}

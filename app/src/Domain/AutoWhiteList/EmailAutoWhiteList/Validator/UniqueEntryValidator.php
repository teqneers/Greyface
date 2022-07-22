<?php

namespace App\Domain\AutoWhiteList\EmailAutoWhiteList\Validator;

use App\Domain\Entity\AutoWhiteList\EmailAutoWhiteList\EmailAutoWhiteListRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class UniqueEntryValidator extends ConstraintValidator
{
    public function __construct(
        private readonly EmailAutoWhiteListRepository $emailAutoWhiteListRepository
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

        $try = $this->emailAutoWhiteListRepository->find([
            'name' => $value->name,
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

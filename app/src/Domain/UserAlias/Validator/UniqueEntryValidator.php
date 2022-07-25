<?php

namespace App\Domain\UserAlias\Validator;

use App\Domain\Entity\User\UserRepository;
use App\Domain\Entity\UserAlias\UserAliasRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class UniqueEntryValidator extends ConstraintValidator
{

    public function __construct(
        private readonly UserAliasRepository $userAliasRepository
    )
    {
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof UniqueEntry) {
            throw new UnexpectedTypeException($constraint, UniqueEntry::class);
        }
        if ($value === null) {
            return;
        }

        $try = $this->userAliasRepository->findByAliasNameForUser($value->user, $value->aliasName);
        if (!$try) {
            return;
        }

        $this->context->buildViolation($constraint->message)
            ->setParameter('{{ value }}', $value->aliasName)
            ->setCause($try)
            ->setInvalidValue($value)
            ->addViolation();
    }
}

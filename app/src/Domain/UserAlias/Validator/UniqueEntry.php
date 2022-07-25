<?php

namespace App\Domain\UserAlias\Validator;

use Attribute;
use Symfony\Component\Validator\Constraint;

#[Attribute(Attribute::TARGET_CLASS)]
class UniqueEntry extends Constraint
{
    public string $message = 'Duplicate Entry - {{ value }}';

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}

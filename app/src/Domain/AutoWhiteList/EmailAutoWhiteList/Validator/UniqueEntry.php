<?php

namespace App\Domain\AutoWhiteList\EmailAutoWhiteList\Validator;

use Attribute;
use Symfony\Component\Validator\Constraint;

#[Attribute(Attribute::TARGET_CLASS)]
class UniqueEntry extends Constraint
{
    public string $message = 'Duplicate Entry - this data is already exists.';

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}

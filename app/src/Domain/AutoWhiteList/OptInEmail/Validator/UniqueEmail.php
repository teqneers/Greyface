<?php

namespace App\Domain\OptIn\OptInEmail\Validator;

use Attribute;
use Symfony\Component\Validator\Constraint;

#[Attribute(Attribute::TARGET_PROPERTY)]
class UniqueEmail extends Constraint
{
    public string $message = 'This "{{ value }}" is already used.';
}

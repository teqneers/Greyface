<?php

namespace App\Domain\OptOut\OptOutEmail\Validator;

use Attribute;
use Symfony\Component\Validator\Constraint;

#[Attribute(Attribute::TARGET_PROPERTY)]
class UniqueEmail extends Constraint
{
    public string $message = 'This "{{ value }}" is already used.';
}

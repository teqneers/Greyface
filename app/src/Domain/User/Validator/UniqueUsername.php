<?php

namespace App\Domain\User\Validator;

use Attribute;
use Symfony\Component\Validator\Constraint;

#[Attribute(Attribute::TARGET_PROPERTY)]
class UniqueUsername extends Constraint
{
    public string $message = 'This username "{{ value }}" is already used.';
}

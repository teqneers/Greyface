<?php

namespace App\Domain\OptOut\OptOutDomain\Validator;

use Attribute;
use Symfony\Component\Validator\Constraint;

#[Attribute(Attribute::TARGET_PROPERTY)]
class UniqueDomain extends Constraint
{
    public string $message = 'This "{{ value }}" is already used.';
}

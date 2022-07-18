<?php

namespace App\Domain\User\Validator;

use Attribute;
use Symfony\Component\Validator\Constraint;

#[Attribute(Attribute::TARGET_PROPERTY)]
class RoleValid extends Constraint
{
    public string $message = 'Changing the role from admin to {{ value }} is not possible because this will remove the last admin.';
}

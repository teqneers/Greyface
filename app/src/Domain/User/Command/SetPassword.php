<?php

namespace App\Domain\User\Command;

use App\Domain\Entity\User\User;
use App\Domain\IdAware;
use JMS\Serializer\Annotation as Serializer;

#[Serializer\ExclusionPolicy(Serializer\ExclusionPolicy::ALL)]
class SetPassword
{
    use IdAware, PasswordAware;

    public static function set(User $user): self
    {
        return new self($user->getId());
    }
}

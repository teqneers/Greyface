<?php

namespace App\Domain\User\Command;

use App\Domain\Entity\User\User;
use JMS\Serializer\Annotation as Serializer;
use App\Domain\IdAware;

#[Serializer\ExclusionPolicy(Serializer\ExclusionPolicy::ALL)]
class UndeleteUser
{
    use IdAware;

    public static function undelete(User $user): self
    {
        return new self($user->getId());
    }
}

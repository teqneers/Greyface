<?php

namespace App\Domain\UserAlias\Command;

use App\Domain\Entity\User\User;
use App\Domain\Entity\UserAlias\UserAlias;
use JMS\Serializer\Annotation as Serializer;
use App\Domain\IdAware;

#[Serializer\ExclusionPolicy(Serializer\ExclusionPolicy::ALL)]
class DeleteUserAlias
{
    use IdAware;

    public static function delete(UserAlias $userAlias): self
    {
        return new self($userAlias->getId(), false);
    }

    private function __construct(
        string $id
    ) {
        $this->id = $id;
    }

}

<?php

namespace App\Test;

use App\Domain\Entity\User\User;
use App\Domain\Entity\UserAlias\UserAlias;
use Ramsey\Uuid\Uuid;

trait UserAliasTrait
{
    use UserDomainTrait;

    public static function createUserAlias(
        User $user = null,
        string $aliasName = 'alias@example.de',
    ): UserAlias {
        return new UserAlias(
            (string)Uuid::uuid4(),
            $user ?? self::createUser(),
            $aliasName,
        );
    }
}
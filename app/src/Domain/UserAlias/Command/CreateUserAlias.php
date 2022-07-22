<?php


namespace App\Domain\UserAlias\Command;

use JMS\Serializer\Annotation as Serializer;
use App\Domain\IdAware;

#[Serializer\ExclusionPolicy(Serializer\ExclusionPolicy::ALL)]
class CreateUserAlias
{
    use IdAware, UserAliasProperties;

    public static function create(): self
    {
        return new self(self::createUuid());
    }
}

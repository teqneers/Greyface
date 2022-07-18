<?php


namespace App\Domain\User\Command;

use JMS\Serializer\Annotation as Serializer;
use App\Domain\IdAware;

#[Serializer\ExclusionPolicy(Serializer\ExclusionPolicy::ALL)]
class CreateUser
{
    use IdAware, UserProperties, PasswordAware;

    public static function create(): self
    {
        return new self(self::createUuid());
    }
}

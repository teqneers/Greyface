<?php

namespace App\Domain\UserAlias\Command;

use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

#[Serializer\ExclusionPolicy(Serializer\ExclusionPolicy::ALL)]
trait UserAliasProperties
{
    #[Assert\Type('string')]
    #[Assert\Uuid]
    #[Assert\NotBlank]
    #[Serializer\Expose]
    #[Serializer\Type('string')]
    public string $userId = '';

    #[Assert\Type('string')]
    #[Assert\Length(max: 128)]
    #[Assert\NotBlank]
    #[Assert\Email(mode: 'strict')]
    #[Serializer\Expose]
    #[Serializer\Type('string')]
    public string $aliasName = '';

}

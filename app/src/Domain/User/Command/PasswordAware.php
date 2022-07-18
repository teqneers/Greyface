<?php

namespace App\Domain\User\Command;

use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

#[Serializer\ExclusionPolicy(Serializer\ExclusionPolicy::ALL)]
trait PasswordAware
{
    #[Assert\Type('string')]
    #[Assert\Length(max: 4096)]
    #[Assert\NotBlank]
    #[Serializer\Expose]
    #[Serializer\Type('string')]
    public string $password = '';
}

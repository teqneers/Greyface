<?php

namespace App\Domain\User\Command;

use App\Domain\Entity\User\User;
use App\Domain\User\Validator\RoleValid;
use App\Domain\User\Validator\UniqueUsername;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

#[Serializer\ExclusionPolicy(Serializer\ExclusionPolicy::ALL)]
trait UserProperties
{
    #[Assert\Type('string')]
    #[Assert\Length(max: 128)]
    #[Assert\NotBlank]
    #[UniqueUsername]
    #[Serializer\Expose]
    #[Serializer\Type('string')]
    public string $username = '';

    #[Assert\Type('string')]
    #[Assert\Length(max: 128)]
    #[Assert\NotBlank]
    #[Assert\Email(mode: 'strict')]
    #[Serializer\Expose]
    #[Serializer\Type('string')]
    public string $email = '';

    #[Assert\Type('string')]
    #[Assert\Choice(choices: User::AVAILABLE_ROLES)]
    #[Assert\NotBlank]
    #[RoleValid]
    #[Serializer\Expose]
    #[Serializer\Type('string')]
    public string $role = User::ROLE_USER;
}

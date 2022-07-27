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


    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }
}

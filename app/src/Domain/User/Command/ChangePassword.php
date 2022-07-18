<?php

namespace App\Domain\User\Command;

use App\Domain\User\UserInterface;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Security\Core\Validator\Constraints as SecurityAssert;
use Symfony\Component\Validator\Constraints as Assert;
use App\Domain\IdAware;

#[Serializer\ExclusionPolicy(Serializer\ExclusionPolicy::ALL)]
class ChangePassword
{
    use IdAware, PasswordAware;

    #[Assert\Type('string')]
    #[Assert\Length(max: 4096)]
    #[Assert\NotBlank]
    #[SecurityAssert\UserPassword]
    #[Serializer\Expose]
    #[Serializer\Type('string')]
    private string $currentPassword = '';

    public static function change(UserInterface $user): self
    {
        return new self($user->getId());
    }

    public function getCurrentPassword(): string
    {
        return $this->currentPassword;
    }

    public function setCurrentPassword(string $currentPassword): self
    {
        $this->currentPassword = $currentPassword;
        return $this;
    }
}

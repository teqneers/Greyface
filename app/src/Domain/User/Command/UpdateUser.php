<?php


namespace App\Domain\User\Command;

use App\Domain\Entity\User\User;
use JMS\Serializer\Annotation as Serializer;
use App\Domain\IdAware;
use App\Domain\Identifiable;

#[Serializer\ExclusionPolicy(Serializer\ExclusionPolicy::ALL)]
class UpdateUser implements Identifiable
{
    use IdAware, UserProperties;

    public static function update(User $user): self
    {
        return new self(
            $user->getId(),
            $user->getUsername(),
            $user->getEmail(),
            $user->getRole(),
        );
    }

    private function __construct(
        string $id,
        string $username,
        string $email,
        string $role,
    )
    {
        $this->id = $id;
        $this->username = $username;
        $this->email = $email;
        $this->role = $role;
    }
}

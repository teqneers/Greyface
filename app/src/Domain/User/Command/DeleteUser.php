<?php

namespace App\Domain\User\Command;

use App\Domain\Entity\User\User;
use JMS\Serializer\Annotation as Serializer;
use App\Domain\IdAware;

#[Serializer\ExclusionPolicy(Serializer\ExclusionPolicy::ALL)]
class DeleteUser
{
    use IdAware;

    public static function delete(User $user): self
    {
        return new self($user->getId(), false);
    }

    public static function softDelete(User $user): self
    {
        return new self($user->getId(), true);
    }

    private function __construct(
        string $id,
        private readonly bool $softDelete
    ) {
        $this->id = $id;
    }

    public function isSoftDelete(): bool
    {
        return $this->softDelete;
    }
}

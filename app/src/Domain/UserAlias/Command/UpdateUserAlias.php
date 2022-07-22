<?php


namespace App\Domain\UserAlias\Command;

use App\Domain\Entity\User\User;
use App\Domain\Entity\UserAlias\UserAlias;
use JMS\Serializer\Annotation as Serializer;
use App\Domain\IdAware;
use App\Domain\Identifiable;

#[Serializer\ExclusionPolicy(Serializer\ExclusionPolicy::ALL)]
class UpdateUserAlias implements Identifiable
{
    use IdAware, UserAliasProperties;

    public static function update(UserAlias $userAlias): self
    {
        return new self(
            $userAlias->getId(),
            $userAlias->getUser(),
            $userAlias->getAliasName()
        );
    }

    private function __construct(
        string $id,
        User   $user,
        string $aliasName
    )
    {
        $this->id = $id;
        $this->userId = $user?->getId();
        $this->aliasName = $aliasName;
    }
}

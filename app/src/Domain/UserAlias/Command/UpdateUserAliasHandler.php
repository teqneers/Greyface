<?php

namespace App\Domain\UserAlias\Command;

use App\Domain\Entity\User\UserRepository;
use App\Domain\Entity\UserAlias\UserAliasRepository;
use App\Domain\User\UserFinder;

class UpdateUserAliasHandler extends UserAliasCommandHandler
{
    use UserFinder;

    public function __construct(
        UserRepository $userRepository,
        UserAliasRepository $userAliasRepository
    ) {
        parent::__construct($userAliasRepository);
        $this->userRepository      = $userRepository;
    }

    public function __invoke(UpdateUserAlias $command): void
    {
        $userAlias = $this->getUserAliasById($command->getId());

        $userAlias->setUser($command->userId ? $this->getUserById($command->userId) : $command->userId)
             ->setAliasName($command->aliasName);
        $this->userAliasRepository->save($userAlias);
    }
}

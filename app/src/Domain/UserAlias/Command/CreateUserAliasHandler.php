<?php

namespace App\Domain\UserAlias\Command;

use App\Domain\Entity\User\User;
use App\Domain\Entity\User\UserRepository;
use App\Domain\Entity\UserAlias\UserAlias;
use App\Domain\Entity\UserAlias\UserAliasRepository;
use App\Domain\User\UserFinder;

class CreateUserAliasHandler extends UserAliasCommandHandler
{
    use UserFinder;

    public function __construct(
        UserRepository      $userRepository,
        UserAliasRepository $userAliasRepository
    )
    {
        parent::__construct($userAliasRepository);
        $this->userRepository = $userRepository;
    }

    public function __invoke(CreateUserAlias $command): void
    {
        $user = $command->userId ? $this->getUserById($command->userId) : null;

        $userAlias = new UserAlias(
            $command->getId(),
            $user,
            $command->aliasName
        );
        $this->userAliasRepository->save($userAlias);
    }
}

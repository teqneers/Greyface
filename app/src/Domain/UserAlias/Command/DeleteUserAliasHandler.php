<?php

namespace App\Domain\UserAlias\Command;


class DeleteUserAliasHandler extends UserAliasCommandHandler
{
    public function __invoke(DeleteUserAlias $command): void
    {
        $userAlias = $this->getUserAliasById($command->getId());
        $this->userAliasRepository->delete($userAlias);
    }
}

<?php

namespace App\Domain\User\Command;

class UndeleteUserHandler extends UserCommandHandler
{
    public function __invoke(UndeleteUser $command): void
    {
        $user = $this->getUserById($command->getId(), true);
        $user->undelete();
        $this->userRepository->save($user);
    }
}

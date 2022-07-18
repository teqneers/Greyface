<?php

namespace App\Domain\User\Command;

use Webmozart\Assert\Assert;

class UpdateUserHandler extends UserCommandHandler
{
    public function __invoke(UpdateUser $command): void
    {
        $user = $this->getUserById($command->getId());

        $user->setUsername($command->username)
             ->setEmail($command->email)
             ->setRole($command->role);
        $this->userRepository->save($user);
    }
}

<?php

namespace App\Domain\User\Command;

use App\Domain\Entity\User\User;

class CreateUserHandler extends UserPasswordCommandHandler
{
    public function __invoke(CreateUser $command): void
    {
        $user = User::createLocalUser(
            $command->getId(),
            $command->username,
            $command->email,
            $command->role
        );
        $user->setPassword($this->hashPassword($command->password));
        $this->userRepository->save($user);
    }
}

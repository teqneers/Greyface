<?php

namespace App\Domain\User\Command;

class SetPasswordHandler extends UserPasswordCommandHandler
{
    public function __invoke(SetPassword $command): void
    {
        $user = $this->getUserById($command->getId());
        $user->setPassword($this->hashPassword($command->getPassword()));
        $this->userRepository->save($user);
    }
}

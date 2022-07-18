<?php

namespace App\Domain\User\Command;

use Webmozart\Assert\Assert;

class ChangePasswordHandler extends UserPasswordCommandHandler
{
    public function __invoke(ChangePassword $command): void
    {
        $user = $this->getUserById($command->getId());
        $user->setPassword($this->hashPassword($command->password));
        $this->userRepository->save($user);
    }
}

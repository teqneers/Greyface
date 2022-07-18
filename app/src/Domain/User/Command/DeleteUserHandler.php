<?php

namespace App\Domain\User\Command;

use App\Domain\Entity\User\UserRepository;

class DeleteUserHandler extends UserCommandHandler
{
    public function __construct(
        UserRepository $userRepository
    ) {
        parent::__construct($userRepository);
    }

    public function __invoke(DeleteUser $command): void
    {
        $user = $this->getUserById($command->getId());
        if ($command->isSoftDelete()) {
            $user->delete();
            $this->userRepository->save($user);
        } else {
            $this->userRepository->delete($user);
        }
    }
}

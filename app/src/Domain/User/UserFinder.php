<?php

namespace App\Domain\User;

use App\Domain\Entity\User\User;
use App\Domain\Entity\User\UserRepository;
use OutOfBoundsException;

trait UserFinder
{
    protected readonly UserRepository $userRepository;

    protected function getUserById(string $id, bool $allowDeleted = false): User
    {
        $user = $this->userRepository->findById($id, $allowDeleted);
        if (!$user) {
            throw new OutOfBoundsException('No user found for id ' . $id);
        }
        return $user;
    }
}

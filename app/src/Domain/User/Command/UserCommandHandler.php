<?php

namespace App\Domain\User\Command;

use App\Domain\Entity\User\UserRepository;
use App\Domain\User\UserFinder;

abstract class UserCommandHandler
{
    use UserFinder;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }
}

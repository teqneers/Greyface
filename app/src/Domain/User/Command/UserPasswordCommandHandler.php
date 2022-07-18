<?php

namespace App\Domain\User\Command;

use App\Domain\Entity\User\UserRepository;
use App\Domain\User\UserInterface;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;

abstract class UserPasswordCommandHandler extends UserCommandHandler
{
    public function __construct(
        UserRepository $userRepository,
        private readonly PasswordHasherFactoryInterface $passwordHasherFactory
    ) {
        parent::__construct($userRepository);
    }

    protected function hashPassword(string $password): string
    {
        return $this->passwordHasherFactory->getPasswordHasher(UserInterface::class)->hash($password);
    }
}

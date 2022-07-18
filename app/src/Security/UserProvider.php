<?php

namespace App\Security;

use App\Domain\Entity\User\UserRepository;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Throwable;

class UserProvider implements UserProviderInterface, PasswordUpgraderInterface
{
    public function __construct(
        private readonly UserRepository $userRepository
    ) {
    }

    public function loadUserByUsername($username): UserInterface
    {
        return $this->fetchUser($username);
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        return $this->fetchUser($identifier);
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(
                sprintf('Instances of "%s" are not supported.', get_class($user))
            );
        }
        $username = $user->getUsername();
        return $this->fetchUser($username);
    }

    public function supportsClass($class): bool
    {
        return $class === User::class;
    }

    private function fetchUser(string $username): User
    {
        $user = $this->userRepository->findByUsername($username);
        if ($user) {
            return User::fromUser($user);
        }
        throw new UserNotFoundException(sprintf('Username "%s" does not exist.', $username));
    }

    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            return;
        }
        $domainUser = $this->userRepository->findById($user->getId());
        if (!$domainUser) {
            return;
        }
        try {
            $domainUser->setPassword($newHashedPassword, false);
            $this->userRepository->save($domainUser);
        } catch (Throwable) {
            return;
        }
    }
}

<?php

namespace App\Security;

use App\Domain\Entity\User\User as DomainUser;
use App\Domain\User\UserInterface as DomainUserInterface;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class User implements UserInterface, PasswordAuthenticatedUserInterface, EquatableInterface, DomainUserInterface
{
    private ?array $internalRoles = null;

    public static function fromUser(DomainUser $user): self
    {
        return new self(
            $user->getId(),
            $user->getUsername(),
            $user->getEmail(),
            $user->getPassword(),
            $user->getAllRoles()
        );
    }

    private function __construct(
        private readonly string $id,
        private readonly string $username,
        private readonly string $email,
        private readonly ?string $password,
        private readonly array $roles
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function isAdministrator(): bool
    {
        return in_array(DomainUser::ROLE_ADMIN, $this->roles, true);
    }

    public function equals(DomainUserInterface $other): bool
    {
        return $this->id === $other->getId();
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getUserIdentifier(): string
    {
        return $this->username;
    }

    public function getRoles(): array
    {
        if ($this->internalRoles === null) {
            $this->internalRoles = array_map(
                static fn(string $role): string => 'ROLE_' . strtoupper($role),
                $this->roles
            );
        }
        return $this->internalRoles;
    }

    public function isEqualTo(UserInterface $user): bool
    {
        if (!$user instanceof self) {
            return false;
        }
        if ($this->username !== $user->getUsername()) {
            return false;
        }
        if ($this->getRoles() !== $user->getRoles()) {
            return false;
        }
        return true;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function eraseCredentials(): void
    {
    }
}

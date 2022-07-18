<?php

namespace App\Domain\Entity\User;

use App\Domain\Entity\CreateTracking;
use App\Domain\Entity\DeleteTracking;
use App\Domain\Entity\HasId;
use App\Domain\Entity\UpdateTracking;
use App\Domain\User\UserInterface;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Ramsey\Uuid\Uuid;
use Webmozart\Assert\Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'users')]
#[ORM\UniqueConstraint(name: 'uniq_username', columns: ['username', 'deleted_at'])]
#[Serializer\ExclusionPolicy(Serializer\ExclusionPolicy::ALL)]
#[Serializer\ReadOnlyProperty]
class User implements UserInterface
{
    use HasId, CreateTracking, UpdateTracking, DeleteTracking;

    public const ROLE_USER = 'user';
    public const  ROLE_ADMIN = 'admin';
    public const  AVAILABLE_ROLES = [
        self::ROLE_ADMIN,
        self::ROLE_USER
    ];

    #[ORM\Column(name: 'username', type: 'string', length: 128)]
    #[Serializer\Expose]
    #[Serializer\Type('string')]
    private string $username = '';

    #[ORM\Column(name: 'email', type: 'string', length: 128)]
    #[Serializer\Expose]
    #[Serializer\Type('string')]
    private string $email = '';

    #[ORM\Column(name: 'role', type: 'string', length: 16)]
    #[Serializer\Expose]
    #[Serializer\Type('string')]
    private string $role = self::ROLE_USER;

    #[ORM\Column(name: 'password', type: 'string', length: 255, nullable: true)]
    private ?string $password = null;

    #[ORM\Column(name: 'deleted_at', type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $deletedAt = null;

    private ?array $allRoles = null;

    public static function createLocalUser(
        string $id,
        string $username,
        string $email,
        string $role
    ): self
    {
        return new self($id, $username, $email, $role);
    }

    private function __construct(string $id, string $username, string $email, string $role)
    {
        $this->setId($id)
            ->setUsername($username)
            ->setEmail($email)
            ->setRole($role)
            ->initCreateTracking()
            ->initUpdateTracking();
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        Assert::lengthBetween($username, 1, 128);
        $this->username = $username;
        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        Assert::lengthBetween($email, 1, 128);
        Assert::email($email);
        $this->email = $email;
        return $this;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function setRole(string $role): self
    {
        Assert::lengthBetween($role, 1, 16);
        Assert::oneOf($role, self::AVAILABLE_ROLES);
        $this->role = $role;
        return $this;
    }

    #[Serializer\VirtualProperty]
    #[Serializer\SerializedName('all_roles')]
    public function getAllRoles(): array
    {
        if ($this->allRoles === null) {
            $roles = [self::ROLE_USER];
            if ($this->isAdministrator()) {
                $roles[] = self::ROLE_ADMIN;
            }
            $this->allRoles = $roles;
        }
        return $this->allRoles;
    }

    #[Serializer\VirtualProperty]
    #[Serializer\SerializedName('is_administrator')]
    public function isAdministrator(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        Assert::lengthBetween($password, 1, 255);
        $this->password = $password;
        return $this;
    }

    #[Serializer\VirtualProperty]
    #[Serializer\SerializedName('is_deleted')]
    public function isDeleted(): bool
    {
        return $this->deletedAt !== null;
    }

    public function delete(?DateTimeImmutable $deletedAt = null): self
    {
        $this->deletedAt = $deletedAt ?? new DateTimeImmutable('now');
        return $this;
    }

    public function undelete(): self
    {
        $this->deletedAt = null;
        return $this;
    }

    public function equals(UserInterface $other): bool
    {
        return $this->id === $other->getId();
    }
}

<?php

namespace App\Test;

use App\Domain\Entity\User\User;
use Ramsey\Uuid\Uuid;
use ReflectionClass;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;
use Webmozart\Assert\Assert;

trait UserDomainTrait
{
    public static function createAdmin(
        string $username = 'admingreyface',
        string $email = 'admin@greyface.test',
    ): User {
        return self::createUser($username, $email, User::ROLE_ADMIN);
    }

    public static function createUser(
        string $username = 'usergreyface',
        string $email = 'user@greyface.test',
        string $role = User::ROLE_USER,
    ): User {
        return User::createLocalUser((string)Uuid::uuid4(), $username, $email, $role);
    }

    public static function setEncodedUserPassword(User $user, string $password): User
    {
        $password = static::getContainer()
            ->get(PasswordHasherFactoryInterface::class)
            ->getPasswordHasher(User::class)
            ->hash($password);
        return $user->setPassword($password);
    }

    public static function assertUserPasswordEquals(string $expected, string $actual): void
    {
        /** @var PasswordHasherInterface $passwordHasher */
        $passwordHasher = static::getContainer()
            ->get(PasswordHasherFactoryInterface::class)
            ->getPasswordHasher(User::class);
        self::assertTrue($passwordHasher->verify($actual, $expected));
    }
}

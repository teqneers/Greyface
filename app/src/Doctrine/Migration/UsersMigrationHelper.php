<?php

namespace App\Doctrine\Migration;

use App\Domain\Entity\User\User;
use App\Domain\User\UserInterface;
use DateTimeImmutable;

/**
 * Trait UsersMigrationHelper
 *
 * @package App\Doctrine\Migration
 */
trait UsersMigrationHelper
{

    /**
     * @param array $user
     */
    private function addUser(array $user, $passHasher): void
    {
        $hashedPass = $passHasher->getPasswordHasher(UserInterface::class)->hash($user['password'] ?? 'admin');

        $this->addSql(
            <<<'SQL'
INSERT INTO tq_users(id, username, email, role, password, created_at, created_by, updated_at, updated_by, deleted_at)
VALUES (:id, :username, :email, :role, :password, :now, NULL, :now, NULL, :deleted_at)
SQL
            ,
            [
                'id' => $user['id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'role' => $user['is_admin'] === 1 ? User::ROLE_ADMIN : User::ROLE_USER,
                'password' => $hashedPass,
                'now' => new DateTimeImmutable('now'),
                'deleted_at' => $user['is_deleted'] ? new DateTimeImmutable('now') : null,
            ],
            [
                'id' => 'string',
                'username' => 'string',
                'email' => 'string',
                'role' => 'string',
                'password' => 'string',
                'now' => 'datetime_immutable_utc',
                'deleted_at' => 'datetime_immutable_utc',
            ]
        );
    }

    /**
     * @param array $users
     */
    private function addUsers(array $users, $passHasher): void
    {
        foreach ($users as $user) {
            $this->addUser($user, $passHasher);
        }
    }

}

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
    private function addUser(array $user): void
    {
        $hashedPass = '$2y$13$y/U3H9zVG7tMq6sHl1jDdOEqMl9AArQayDi8F4eO2GJs/3klD.cwm'; // admin password hashed using security:hash-password command

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
                'deleted_at' => isset($user['is_deleted']) ? new DateTimeImmutable('now') : null,
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
    private function addUsers(array $users): void
    {
        foreach ($users as $user) {
            $this->addUser($user);
        }
    }

}

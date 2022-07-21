<?php

namespace App\Tests\Controller\Api;

use App\Domain\Entity\User\User;
use App\Test\ApiTestTrait;
use App\Test\DatabaseTestTrait;
use App\Test\UserDomainTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class UserControllerTest extends WebTestCase
{
    use ApiTestTrait, DatabaseTestTrait, UserDomainTrait;

    public function testListUsersNotAllowedForNonAdmins(): void
    {
        $user = self::createUser();
        $client = self::createApiClient($user);

        self::initializeDatabaseWithEntities($user);

        $client->request('GET', '/api/users');
        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testListUsersWithoutPagination(): void
    {
        $admin = self::createAdmin();
        $client = self::createApiClient($admin);

        $user1 = self::createUser('login_1', 'user1@greyface.test');
        $user2 = self::createUser('login_2', 'user2@greyface.test');

        self::initializeDatabaseWithEntities($admin, $user1, $user2);

        $client->request('GET', '/api/users');
        $result = self::getSuccessfulJsonResponse($client);
        self::assertEquals(4, $result['count']);
        self::assertCount(4, $result['results']);
        self::assertEquals(
            ['root@localhost', 'admin@greyface.test', 'user1@greyface.test', 'user2@greyface.test'],
            array_map(
                static fn(array $r): string => $r['email'],
                $result['results']
            )
        );
    }

    public function testListUsersWithPagination(): void
    {
        $admin = self::createAdmin();
        $client = self::createApiClient($admin);

        $user1 = self::createUser('login_1', 'user1@greyface.test');
        $user2 = self::createUser('login_2', 'user2@greyface.test');

        self::initializeDatabaseWithEntities($admin, $user1, $user2);

        $client->request('GET', '/api/users?start=0&max=2');
        $result = self::getSuccessfulJsonResponse($client);
        self::assertEquals(4, $result['count']);
        self::assertCount(2, $result['results']);
        self::assertEquals(
            ['root@localhost', 'admin@greyface.test'],
            array_map(
                static fn(array $r): string => $r['email'],
                $result['results']
            )
        );
    }

    public function testShowUser(): void
    {
        $admin = self::createAdmin();
        $client = self::createApiClient($admin);

        self::initializeDatabaseWithEntities($admin);

        $client->request('GET', '/api/users/' . $admin->getId());
        $result = self::getSuccessfulJsonResponse($client);
        self::assertEquals('admin@greyface.test', $result['email']);
    }

    public function testCreateUser(): void
    {
        $admin = self::createAdmin();
        $client = self::createApiClient($admin);

        self::initializeDatabaseWithEntities($admin);

        self::sendApiJsonRequest(
            $client,
            'POST',
            '/api/users',
            [
                'username' => 'user',
                'email' => 'user@greyface.test',
                'password' => 'testpassword',
                'role' => User::ROLE_USER,
            ]
        );
        $result = self::getSuccessfulJsonResponse($client);
        self::assertArrayHasKey('user', $result);
        self::clearEntityManager();
        /** @var User|null $user */
        $user = self::loadDatabaseEntity(User::class, $result['user']);
        self::assertNotNull($user);
        self::assertSame('user@greyface.test', $user->getEmail());
        self::assertSame('user', $user->getUsername());
        self::assertFalse($user->isAdministrator());
        self::assertUserPasswordEquals('testpassword', $user->getPassword());
    }


    public function testUpdateUser(): void
    {
        $admin = self::createAdmin();
        $client = self::createApiClient($admin);

        $user = self::createUser();

        self::initializeDatabaseWithEntities($admin, $user);

        self::sendApiJsonRequest(
            $client,
            'PUT',
            '/api/users/' . $user->getId(),
            [
                'username' => 'user up',
                'email' => 'user1@greyface.test',
                'role' => User::ROLE_ADMIN,
            ]
        );
        self::assertResponseIsSuccessful();
        self::clearEntityManager();
        /** @var User|null $user */
        [$user] = self::reloadDatabaseEntities($user);
        self::assertNotNull($user);
        self::assertSame('user1@greyface.test', $user->getEmail());
        self::assertSame('user up', $user->getUsername());
        self::assertTrue($user->isAdministrator());
    }

    public function testDeleteUser(): void
    {
        $admin = self::createAdmin();
        $client = self::createApiClient($admin);

        $user = self::createUser();

        self::initializeDatabaseWithEntities($admin, $user);

        $client->request('DELETE', '/api/users/' . $user->getId());

        self::assertResponseIsSuccessful();

        self::clearEntityManager();
        /** @var User|null $user */
        [$user] = self::reloadDatabaseEntities($user);
        self::assertNotNull($user);
        self::assertTrue($user->isDeleted());
    }
}
<?php

namespace App\Tests\Controller\Api;

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
        $admin  = self::createAdmin();
        $client = self::createApiClient($admin);

        self::initializeDatabaseWithEntities($admin);

        $client->request('GET', '/api/users/' . $admin->getId());
        $result = self::getSuccessfulJsonResponse($client);
        self::assertEquals('admin@greyface.test', $result['email']);
    }
}
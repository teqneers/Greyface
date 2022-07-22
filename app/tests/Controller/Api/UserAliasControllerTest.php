<?php

namespace App\Tests\Controller\Api;

use App\Domain\Entity\User\User;
use App\Domain\Entity\UserAlias\UserAlias;
use App\Test\ApiTestTrait;
use App\Test\DatabaseTestTrait;
use App\Test\UserAliasTrait;
use App\Test\UserDomainTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class UserAliasControllerTest extends WebTestCase
{
    use ApiTestTrait, DatabaseTestTrait, UserDomainTrait, UserAliasTrait;

    public function testListUserAliasesNotAllowedForNonAdmins(): void
    {
        $user = self::createUser();
        $client = self::createApiClient($user);

        self::initializeDatabaseWithEntities($user);

        $client->request('GET', '/api/users-aliases');
        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testListUserAliasesWithoutPagination(): void
    {
        $admin = self::createAdmin();
        $client = self::createApiClient($admin);
        $user = self::createUser();

        $alias1 = self::createUserAlias($user, 'alias1@example.de');
        $alias2 = self::createUserAlias($user, 'alias2@example.de');

        self::initializeDatabaseWithEntities($admin, $user, $alias1, $alias2);

        $client->request('GET', '/api/users-aliases');
        $result = self::getSuccessfulJsonResponse($client);
        self::assertEquals(2, $result['count']);
        self::assertCount(2, $result['results']);
        self::assertEquals(
            ['alias1@example.de', 'alias2@example.de'],
            array_map(
                static fn(array $r): string => $r['alias_name'],
                $result['results']
            )
        );
    }

    public function testListUserAliasesWithPagination(): void
    {
        $admin = self::createAdmin();
        $client = self::createApiClient($admin);

        $user = self::createUser();

        $alias1 = self::createUserAlias($user, 'alias1@example.de');
        $alias2 = self::createUserAlias($user, 'alias2@example.de');

        self::initializeDatabaseWithEntities($admin, $user, $alias1, $alias2);

        $client->request('GET', '/api/users-aliases?start=0&max=2');
        $result = self::getSuccessfulJsonResponse($client);
        self::assertEquals(2, $result['count']);
        self::assertCount(2, $result['results']);
        self::assertEquals(
            ['alias1@example.de', 'alias2@example.de'],
            array_map(
                static fn(array $r): string => $r['alias_name'],
                $result['results']
            )
        );
    }

    public function testShowUserAlias(): void
    {
        $admin = self::createAdmin();
        $client = self::createApiClient($admin);

        $user = self::createUser();

        $alias1 = self::createUserAlias($user, 'alias1@example.de');

        self::initializeDatabaseWithEntities($admin, $user, $alias1);

        $client->request('GET', '/api/users-aliases/' . $alias1->getId());
        $result = self::getSuccessfulJsonResponse($client);
        self::assertEquals('alias1@example.de', $result['alias_name']);
    }

    public function testCreateUserAlias(): void
    {
        $admin = self::createAdmin();
        $client = self::createApiClient($admin);

        self::initializeDatabaseWithEntities($admin);

        self::sendApiJsonRequest(
            $client,
            'POST',
            '/api/users-aliases',
            [
                'user_id' => $admin->getId(),
                'alias_name' => 'user@greyface.test'
            ]
        );
        self::assertResponseIsSuccessful();
    }

    public function testCreateMultipleUserAlias(): void
    {
        $admin = self::createAdmin();
        $client = self::createApiClient($admin);

        self::initializeDatabaseWithEntities($admin);

        self::sendApiJsonRequest(
            $client,
            'POST',
            '/api/users-aliases',
            [
                'user_id' => $admin->getId(),
                'alias_name' => ['user@greyface.test', 'alias@greyface.de']
            ]
        );
        self::assertResponseIsSuccessful();
    }


    public function testUpdateUserAlias(): void
    {
        $admin = self::createAdmin();
        $client = self::createApiClient($admin);

        $user = self::createUser();

        $alias = self::createUserAlias($user, 'alias1@example.de');

        self::initializeDatabaseWithEntities($admin, $user, $alias);

        self::sendApiJsonRequest(
            $client,
            'PUT',
            '/api/users-aliases/' . $alias->getId(),
            [
                'user_id' => $user->getId(),
                'alias_name' => 'user@greyface.test'
            ]
        );
        self::assertResponseIsSuccessful();
        self::clearEntityManager();
        /** @var UserAlias|null $alias */
        [$alias] = self::reloadDatabaseEntities($alias);
        self::assertNotNull($alias);
        self::assertSame('user@greyface.test', $alias->getAliasName());
    }

    public function testDeleteUserAlias(): void
    {
        $admin = self::createAdmin();
        $client = self::createApiClient($admin);

        $user = self::createUser();

        $alias = self::createUserAlias($user, 'alias1@example.de');

        self::initializeDatabaseWithEntities($admin, $user, $alias);

        $client->request('DELETE', '/api/users-aliases/' . $alias->getId());

        self::assertResponseIsSuccessful();

        self::clearEntityManager();
        /** @var UserAlias|null $alias */
        [$alias] = self::reloadDatabaseEntities($alias);
        self::assertNull($alias);
    }
}
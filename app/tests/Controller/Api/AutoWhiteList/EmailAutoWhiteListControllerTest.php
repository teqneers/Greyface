<?php

namespace App\Tests\Controller\Api\AutoWhiteList;

use App\Domain\Entity\AutoWhiteList\EmailAutoWhiteList\EmailAutoWhiteList;
use App\Test\ApiTestTrait;
use App\Test\AutoWhiteListTrait;
use App\Test\DatabaseTestTrait;
use App\Test\UserDomainTrait;
use DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class EmailAutoWhiteListControllerTest extends WebTestCase
{
    use ApiTestTrait, DatabaseTestTrait, UserDomainTrait, AutoWhiteListTrait;

    public function testListUsersNotAllowedForNonAdmins(): void
    {
        $user = self::createUser();
        $client = self::createApiClient($user);

        self::initializeDatabaseWithEntities($user);

        $client->request('GET', '/api/awl/emails');
        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testListUsersWithoutPagination(): void
    {
        $admin = self::createAdmin();
        $client = self::createApiClient($admin);

        $emailAwl = self::createAutoWhiteListEmail();

        self::initializeDatabaseWithEntities($admin, $emailAwl);

        $client->request('GET', '/api/awl/emails');
        $result = self::getSuccessfulJsonResponse($client);
        self::assertArrayHasKey('results', $result);
        self::assertEquals(1, $result['count']);
        self::assertCount(1, $result['results']);
        self::assertEquals(
            ['greyface.de'],
            array_map(
                static fn(array $r): string => $r['domain'],
                $result['results']
            )
        );
    }

    public function testListUsersWithPagination(): void
    {
        $admin = self::createAdmin();
        $client = self::createApiClient($admin);

        $emailAwl = self::createAutoWhiteListEmail();
        $emailAwl2 = self::createAutoWhiteListEmail('second-name@email.com','second.greyface.de', '123.123.123');

        self::initializeDatabaseWithEntities($admin, $emailAwl, $emailAwl2);

        $client->request('GET', '/api/awl/emails?start=0&max=1');
        $result = self::getSuccessfulJsonResponse($client);
        self::assertArrayHasKey('results', $result);
        self::assertEquals(2, $result['count']);
        self::assertCount(1, $result['results']);
        self::assertEquals(
            ['greyface.de'],
            array_map(
                static fn(array $r): string => $r['domain'],
                $result['results']
            )
        );
    }

    public function testCreateEmailAutoWhiteList(): void
    {
        $admin = self::createAdmin();
        $client = self::createApiClient($admin);

        self::initializeDatabaseWithEntities($admin);

        self::sendApiJsonRequest(
            $client,
            'POST',
            '/api/awl/emails',
            [
                'name' => 'whitelist@email.de',
                'domain' => 'testing.de',
                'source' => '123.123.123'
            ]
        );
        $result = self::getSuccessfulJsonResponse($client);

        self::assertArrayHasKey('name', $result);
        self::assertArrayHasKey('domain', $result);
        self::assertArrayHasKey('source', $result);
        self::clearEntityManager();

        /** @var EmailAutoWhiteList|null $emailAwl */
        $emailAwl = self::loadDatabaseEntity(
            EmailAutoWhiteList::class,
            [
                'name' => $result['name'],
                'domain' => $result['domain'],
                'source' => $result['source']
            ]);
        self::assertNotNull($emailAwl);
        self::assertSame('whitelist@email.de', $emailAwl->getName());
        self::assertSame('testing.de', $emailAwl->getDomain());
        self::assertSame('123.123.123', $emailAwl->getSource());
    }

    public function testCreateEmailAutoWhiteListWithFirstAndLastSeen(): void
    {
        $admin = self::createAdmin();
        $client = self::createApiClient($admin);

        self::initializeDatabaseWithEntities($admin);

        self::sendApiJsonRequest(
            $client,
            'POST',
            '/api/awl/emails',
            [
                'name' => 'whitelist@email.de',
                'domain' => 'testing.de',
                'source' => '123.123.123',
                'first_seen' => '2022-06-01 21:00:00',
                'last_seen' => '2022-06-01 22:00:00',
            ]
        );
        $result = self::getSuccessfulJsonResponse($client);

        self::assertArrayHasKey('name', $result);
        self::assertArrayHasKey('domain', $result);
        self::assertArrayHasKey('source', $result);
        self::clearEntityManager();

        /** @var EmailAutoWhiteList|null $emailAwl */
        $emailAwl = self::loadDatabaseEntity(
            EmailAutoWhiteList::class,
            [
                'name' => $result['name'],
                'domain' => $result['domain'],
                'source' => $result['source']
            ]);

        self::assertNotNull($emailAwl);
        self::assertSame('whitelist@email.de', $emailAwl->getName());
        self::assertSame('testing.de', $emailAwl->getDomain());
        self::assertSame('123.123.123', $emailAwl->getSource());
        self::assertEquals(new DateTimeImmutable('2022-06-01 21:00:00'), $emailAwl->getFirstSeen());
        self::assertEquals(new DateTimeImmutable('2022-06-01 22:00:00'), $emailAwl->getLastSeen());
    }

    public function testCreateEmailAutoWhiteListDuplicateFailed(): void
    {
        $admin = self::createAdmin();
        $client = self::createApiClient($admin);

        $emailAwl = self::createAutoWhiteListEmail();

        self::initializeDatabaseWithEntities($admin, $emailAwl);

        self::sendApiJsonRequest(
            $client,
            'POST',
            '/api/awl/emails',
            [
                'name' => 'whitelist@email.de',
                'domain' => 'greyface.de',
                'source' => '121.121.121.121'
            ]
        );
        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testUpdateEmailAutoWhiteList(): void
    {
        $admin = self::createAdmin();
        $client = self::createApiClient($admin);

        $emailAwl = self::createAutoWhiteListEmail();

        self::initializeDatabaseWithEntities($admin, $emailAwl);

        self::sendApiJsonRequest(
            $client,
            'PUT',
            '/api/awl/emails/edit',
            [
                'dynamicID' => [
                    'name' => $emailAwl->getName(),
                    'domain' => $emailAwl->getDomain(),
                    'source' => $emailAwl->getSource()
                ],
                'name' => 'whitelist@email.de',
                'domain' => 'teqneers-testing.de',
                'source' => '123.123.123'
            ]
        );
        $result = self::getSuccessfulJsonResponse($client);
        self::assertArrayHasKey('domain', $result);
        self::assertArrayHasKey('source', $result);
        self::clearEntityManager();
        /** @var EmailAutoWhiteList|null $emailAwl */
        $emailAwl = self::loadDatabaseEntity(
            EmailAutoWhiteList::class,
            [
                'name' => $result['name'],
                'domain' => $result['domain'],
                'source' => $result['source']
            ]);
        self::assertNotNull($emailAwl);
        self::assertSame('whitelist@email.de', $emailAwl->getName());
        self::assertSame('teqneers-testing.de', $emailAwl->getDomain());
        self::assertSame('123.123.123', $emailAwl->getSource());
    }

    public function testUpdateLastSeenEmailAutoWhiteList(): void
    {
        $admin = self::createAdmin();
        $client = self::createApiClient($admin);

        $emailAwl = self::createAutoWhiteListEmail();

        self::initializeDatabaseWithEntities($admin, $emailAwl);

        self::sendApiJsonRequest(
            $client,
            'PUT',
            '/api/awl/emails/last-seen',
            [
                'dynamicID' => [
                    'name' => $emailAwl->getName(),
                    'domain' => $emailAwl->getDomain(),
                    'source' => $emailAwl->getSource()
                ]
            ]
        );
        $result = self::getSuccessfulJsonResponse($client);
        self::assertArrayHasKey('domain', $result);
        self::assertArrayHasKey('source', $result);
        self::clearEntityManager();

        /** @var EmailAutoWhiteList|null $emailAwl */
        $emailAwl = self::loadDatabaseEntity(
            EmailAutoWhiteList::class,
            [
                'name' => $result['name'],
                'domain' => $result['domain'],
                'source' => $result['source']
            ]);

        self::assertNotNull($emailAwl);
        self::assertNotNull($emailAwl->getLastSeen());
    }

    public function testDeleteEmailAutoWhiteList(): void
    {
        $admin = self::createAdmin();
        $client = self::createApiClient($admin);

        $emailAwl = self::createAutoWhiteListEmail();

        self::initializeDatabaseWithEntities($admin, $emailAwl);

        self::sendApiJsonRequest(
            $client,
            'DELETE',
            '/api/awl/emails/delete',
            [
                    'name' => $emailAwl->getName(),
                    'domain' => $emailAwl->getDomain(),
                    'source' => $emailAwl->getSource()
            ]);

        self::assertResponseIsSuccessful();

        self::clearEntityManager();
        /** @var EmailAutoWhiteList|null $emailAwl */
        [$emailAwl] = self::reloadDatabaseEntities($emailAwl);
        self::assertNull($emailAwl);
    }
}
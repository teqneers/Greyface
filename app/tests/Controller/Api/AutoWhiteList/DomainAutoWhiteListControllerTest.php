<?php

namespace App\Tests\Controller\Api\AutoWhiteList;

use App\Domain\Entity\AutoWhiteList\DomainAutoWhiteList\DomainAutoWhiteList;
use App\Test\ApiTestTrait;
use App\Test\AutoWhiteListTrait;
use App\Test\DatabaseTestTrait;
use App\Test\UserDomainTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class DomainAutoWhiteListControllerTest extends WebTestCase
{
    use ApiTestTrait, DatabaseTestTrait, UserDomainTrait, AutoWhiteListTrait;

    public function testListUsersNotAllowedForNonAdmins(): void
    {
        $user = self::createUser();
        $client = self::createApiClient($user);

        self::initializeDatabaseWithEntities($user);

        $client->request('GET', '/api/awl/domains');
        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testListUsersWithoutPagination(): void
    {
        $admin = self::createAdmin();
        $client = self::createApiClient($admin);

        $domain = self::createAutoWhiteListDomain();

        self::initializeDatabaseWithEntities($admin, $domain);

        $client->request('GET', '/api/awl/domains');
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

        $domain = self::createAutoWhiteListDomain();
        $domain2 = self::createAutoWhiteListDomain('second.greyface.de', '123.123.123');

        self::initializeDatabaseWithEntities($admin, $domain, $domain2);

        $client->request('GET', '/api/awl/domains?start=0&max=1');
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

    public function testCreateDomainAutoWhiteList(): void
    {
        $admin = self::createAdmin();
        $client = self::createApiClient($admin);

        self::initializeDatabaseWithEntities($admin);

        self::sendApiJsonRequest(
            $client,
            'POST',
            '/api/awl/domains',
            [
                'domain' => 'testing.de',
                'source' => '123.123.123'
            ]
        );
        $result = self::getSuccessfulJsonResponse($client);

        self::assertArrayHasKey('domain', $result);
        self::assertArrayHasKey('source', $result);
        self::clearEntityManager();

        /** @var DomainAutoWhiteList|null $domain */
        $domain = self::loadDatabaseEntity(
            DomainAutoWhiteList::class,
            ['domain' => $result['domain'], 'source' => $result['source']]);

        self::assertNotNull($domain);
        self::assertSame('testing.de', $domain->getDomain());
        self::assertSame('123.123.123', $domain->getSource());
    }

    public function testCreateDomainAutoWhiteListDuplicateFailed(): void
    {
        $admin = self::createAdmin();
        $client = self::createApiClient($admin);

        $domain = self::createAutoWhiteListDomain();

        self::initializeDatabaseWithEntities($admin, $domain);

        self::sendApiJsonRequest(
            $client,
            'POST',
            '/api/awl/domains',
            [
                'domain' => 'greyface.de',
                'source' => '121.121.121.121'
            ]
        );
        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testUpdateDomainAutoWhiteList(): void
    {
        $admin = self::createAdmin();
        $client = self::createApiClient($admin);

        $domain = self::createAutoWhiteListDomain();

        self::initializeDatabaseWithEntities($admin, $domain);

        self::sendApiJsonRequest(
            $client,
            'PUT',
            '/api/awl/domains/edit',
            [
                'dynamicID' => [
                    'domain' => $domain->getDomain(),
                    'source' => $domain->getSource()
                ],
                'domain' => 'teqneers-testing.de',
                'source' => '123.123.123'
            ]
        );
        $result = self::getSuccessfulJsonResponse($client);
        self::assertArrayHasKey('domain', $result);
        self::assertArrayHasKey('source', $result);
        self::clearEntityManager();
        /** @var DomainAutoWhiteList|null $domain */
        $domain = self::loadDatabaseEntity(
            DomainAutoWhiteList::class,
            ['domain' => $result['domain'], 'source' => $result['source']]);
        self::assertNotNull($domain);
        self::assertSame('teqneers-testing.de', $domain->getDomain());
        self::assertSame('123.123.123', $domain->getSource());
    }

    public function testUpdateLastSeenDomainAutoWhiteList(): void
    {
        $admin = self::createAdmin();
        $client = self::createApiClient($admin);

        $domain = self::createAutoWhiteListDomain();

        self::initializeDatabaseWithEntities($admin, $domain);

        self::sendApiJsonRequest(
            $client,
            'PUT',
            '/api/awl/domains/last-seen',
            [
                'dynamicID' => [
                    'domain' => $domain->getDomain(),
                    'source' => $domain->getSource()
                ]
            ]
        );
        $result = self::getSuccessfulJsonResponse($client);
        self::assertArrayHasKey('domain', $result);
        self::assertArrayHasKey('source', $result);
        self::clearEntityManager();

        /** @var DomainAutoWhiteList|null $domain */
        $domain = self::loadDatabaseEntity(
            DomainAutoWhiteList::class,
            ['domain' => $result['domain'], 'source' => $result['source']]);

        self::assertNotNull($domain);
        self::assertNotNull($domain->getLastSeen());
    }

    public function testDeleteDomainAutoWhiteList(): void
    {
        $admin = self::createAdmin();
        $client = self::createApiClient($admin);

        $domain = self::createAutoWhiteListDomain();

        self::initializeDatabaseWithEntities($admin, $domain);

        self::sendApiJsonRequest(
            $client,
            'DELETE',
            '/api/awl/domains/delete',
            [
                    'domain' => $domain->getDomain(),
                    'source' => $domain->getSource()
            ]);

        self::assertResponseIsSuccessful();

        self::clearEntityManager();
        /** @var DomainAutoWhiteList|null $domain */
        [$domain] = self::reloadDatabaseEntities($domain);
        self::assertNull($domain);
    }
}
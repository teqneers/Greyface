<?php

namespace App\Tests\Controller\Api\OptIn;

use App\Domain\Entity\OptIn\OptInDomain\OptInDomain;
use App\Test\ApiTestTrait;
use App\Test\DatabaseTestTrait;
use App\Test\OptInOptOutTrait;
use App\Test\UserDomainTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class OptInDomainControllerTest extends WebTestCase
{
    use ApiTestTrait, DatabaseTestTrait, UserDomainTrait, OptInOptOutTrait;

    public function testListUsersNotAllowedForNonAdmins(): void
    {
        $user = self::createUser();
        $client = self::createApiClient($user);

        self::initializeDatabaseWithEntities($user);

        $client->request('GET', '/api/opt-in/domains');
        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testListUsersWithoutPagination(): void
    {
        $admin = self::createAdmin();
        $client = self::createApiClient($admin);

        $domain = self::createOptInDomain();

        self::initializeDatabaseWithEntities($admin, $domain);

        $client->request('GET', '/api/opt-in/domains');
        $result = self::getSuccessfulJsonResponse($client);
        self::assertArrayHasKey('results', $result);
        self::assertEquals(1, $result['count']);
        self::assertCount(1, $result['results']);
        self::assertEquals(
            ['optin.greyface.de'],
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

        $domain = self::createOptInDomain();
        $domain2 = self::createOptInDomain('second-optin.greyface.de');

        self::initializeDatabaseWithEntities($admin, $domain, $domain2);

        $client->request('GET', '/api/opt-in/domains?start=0&max=1');
        $result = self::getSuccessfulJsonResponse($client);
        self::assertArrayHasKey('results', $result);
        self::assertEquals(2, $result['count']);
        self::assertCount(1, $result['results']);
        self::assertEquals(
            ['optin.greyface.de'],
            array_map(
                static fn(array $r): string => $r['domain'],
                $result['results']
            )
        );
    }

    public function testCreateOptInDomain(): void
    {
        $admin = self::createAdmin();
        $client = self::createApiClient($admin);

        self::initializeDatabaseWithEntities($admin);

        self::sendApiJsonRequest(
            $client,
            'POST',
            '/api/opt-in/domains',
            [
                'domain' => 'testing.de'
            ]
        );
        $result = self::getSuccessfulJsonResponse($client);
        self::assertArrayHasKey('domain', $result);
        self::clearEntityManager();
        /** @var OptInDomain|null $domain */
        $domain = self::loadDatabaseEntity(OptInDomain::class, $result['domain']);
        self::assertNotNull($domain);
        self::assertSame('testing.de', $domain->getDomain());
    }

    public function testCreateOptInDomainDuplicateFailed(): void
    {
        $admin = self::createAdmin();
        $client = self::createApiClient($admin);

        $domain = self::createOptInDomain();

        self::initializeDatabaseWithEntities($admin, $domain);

        self::sendApiJsonRequest(
            $client,
            'POST',
            '/api/opt-in/domains',
            [
                'domain' => 'optin.greyface.de'
            ]
        );
        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
    }


    public function testUpdateOptInDomain(): void
    {
        $admin = self::createAdmin();
        $client = self::createApiClient($admin);

        $domain = self::createOptInDomain();

        self::initializeDatabaseWithEntities($admin, $domain);

        self::sendApiJsonRequest(
            $client,
            'PUT',
            '/api/opt-in/domains/' . $domain->getDomain(),
            [
                'domain' => 'teqneers-testing.de'
            ]
        );
        $result = self::getSuccessfulJsonResponse($client);
        self::assertArrayHasKey('domain', $result);
        self::clearEntityManager();
        /** @var OptInDomain|null $domain */
        $domain = self::loadDatabaseEntity(OptInDomain::class, $result['domain']);
        self::assertNotNull($domain);
        self::assertSame('teqneers-testing.de', $domain->getDomain());
    }

    public function testDeleteOptInDomain(): void
    {
        $admin = self::createAdmin();
        $client = self::createApiClient($admin);

        $domain = self::createOptInDomain();

        self::initializeDatabaseWithEntities($admin, $domain);

        $client->request('DELETE', '/api/opt-in/domains/' . $domain->getDomain());

        self::assertResponseIsSuccessful();

        self::clearEntityManager();
        /** @var OptInDomain|null $domain */
        [$domain] = self::reloadDatabaseEntities($domain);
        self::assertNull($domain);
    }
}
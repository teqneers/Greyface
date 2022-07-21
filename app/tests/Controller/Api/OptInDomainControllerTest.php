<?php

namespace App\Tests\Controller\Api;

use App\Domain\Entity\OptInDomain\OptInDomain;
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

        $client->request('GET', '/api/optin-domains');
        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testListUsersWithoutPagination(): void
    {
        $admin = self::createAdmin();
        $client = self::createApiClient($admin);

        self::initializeDatabaseWithEntities($admin);

        $client->request('GET', '/api/optin-domains');
        $result = self::getSuccessfulJsonResponse($client);
        self::assertArrayHasKey('results', $result);
    }

    public function testListUsersWithPagination(): void
    {
        $admin = self::createAdmin();
        $client = self::createApiClient($admin);

        self::initializeDatabaseWithEntities($admin);

        $client->request('GET', '/api/optin-domains?start=0&max=2');
        $result = self::getSuccessfulJsonResponse($client);
        self::assertArrayHasKey('results', $result);
    }

    public function testCreateOptInDomain(): void
    {
        $admin  = self::createAdmin();
        $client = self::createApiClient($admin);

        self::initializeDatabaseWithEntities($admin);

        self::sendApiJsonRequest(
            $client,
            'POST',
            '/api/optin-domains',
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


    public function testUpdateOptInDomain(): void
    {
        $admin  = self::createAdmin();
        $client = self::createApiClient($admin);

        $domain = self::createOptInDomain();

        self::initializeDatabaseWithEntities($admin, $domain);

        self::sendApiJsonRequest(
            $client,
            'PUT',
            '/api/optin-domains/' . $domain->getDomain(),
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
        $admin  = self::createAdmin();
        $client = self::createApiClient($admin);

        $domain = self::createOptInDomain();

        self::initializeDatabaseWithEntities($admin, $domain);

        $client->request('DELETE', '/api/optin-domains/' . $domain->getDomain());

        self::assertResponseIsSuccessful();

        self::clearEntityManager();
        /** @var OptInDomain|null $domain */
        [$domain] = self::reloadDatabaseEntities($domain);
        self::assertNull($domain);
    }
}
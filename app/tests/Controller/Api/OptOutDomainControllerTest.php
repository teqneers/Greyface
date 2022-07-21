<?php

namespace App\Tests\Controller\Api;

use App\Domain\Entity\OptOutDomain\OptOutDomain;
use App\Test\ApiTestTrait;
use App\Test\DatabaseTestTrait;
use App\Test\OptInOptOutTrait;
use App\Test\UserDomainTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class OptOutDomainControllerTest extends WebTestCase
{
    use ApiTestTrait, DatabaseTestTrait, UserDomainTrait, OptInOptOutTrait;

    public function testListUsersNotAllowedForNonAdmins(): void
    {
        $user = self::createUser();
        $client = self::createApiClient($user);

        self::initializeDatabaseWithEntities($user);

        $client->request('GET', '/api/optout-domains');
        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testListUsersWithoutPagination(): void
    {
        $admin = self::createAdmin();
        $client = self::createApiClient($admin);

        self::initializeDatabaseWithEntities($admin);

        $client->request('GET', '/api/optout-domains');
        $result = self::getSuccessfulJsonResponse($client);
        self::assertArrayHasKey('results', $result);
    }

    public function testListUsersWithPagination(): void
    {
        $admin = self::createAdmin();
        $client = self::createApiClient($admin);

        self::initializeDatabaseWithEntities($admin);

        $client->request('GET', '/api/optout-domains?start=0&max=2');
        $result = self::getSuccessfulJsonResponse($client);
        self::assertArrayHasKey('results', $result);
    }

    public function testCreateOptOutDomain(): void
    {
        $admin  = self::createAdmin();
        $client = self::createApiClient($admin);

        self::initializeDatabaseWithEntities($admin);

        self::sendApiJsonRequest(
            $client,
            'POST',
            '/api/optout-domains',
            [
                'domain' => 'testing.de'
            ]
        );
        $result = self::getSuccessfulJsonResponse($client);
        self::assertArrayHasKey('domain', $result);
        self::clearEntityManager();
        /** @var OptOutDomain|null $domain */
        $domain = self::loadDatabaseEntity(OptOutDomain::class, $result['domain']);
        self::assertNotNull($domain);
        self::assertSame('testing.de', $domain->getDomain());
    }


    public function testUpdateOptOutDomain(): void
    {
        $admin  = self::createAdmin();
        $client = self::createApiClient($admin);

        $domain = self::createOptOutDomain();

        self::initializeDatabaseWithEntities($admin, $domain);

        self::sendApiJsonRequest(
            $client,
            'PUT',
            '/api/optout-domains/' . $domain->getDomain(),
            [
                'domain' => 'teqneers-testing.de'
            ]
        );
        $result = self::getSuccessfulJsonResponse($client);
        self::assertArrayHasKey('domain', $result);
        self::clearEntityManager();
        /** @var OptOutDomain|null $domain */
        $domain = self::loadDatabaseEntity(OptOutDomain::class, $result['domain']);
        self::assertNotNull($domain);
        self::assertSame('teqneers-testing.de', $domain->getDomain());
    }

    public function testDeleteOptOutDomain(): void
    {
        $admin  = self::createAdmin();
        $client = self::createApiClient($admin);

        $domain = self::createOptOutDomain();

        self::initializeDatabaseWithEntities($admin, $domain);

        $client->request('DELETE', '/api/optout-domains/' . $domain->getDomain());

        self::assertResponseIsSuccessful();

        self::clearEntityManager();
        /** @var OptOutDomain|null $domain */
        [$domain] = self::reloadDatabaseEntities($domain);
        self::assertNull($domain);
    }
}
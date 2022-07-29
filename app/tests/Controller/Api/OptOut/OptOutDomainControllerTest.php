<?php

namespace App\Tests\Controller\Api\OptOut;

use App\Domain\Entity\OptOut\OptOutDomain\OptOutDomain;
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

        $client->request('GET', '/api/opt-out/domains');
        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testListUsersWithoutPagination(): void
    {
        $admin = self::createAdmin();
        $client = self::createApiClient($admin);

        $domain = self::createOptOutDomain();

        self::initializeDatabaseWithEntities($admin, $domain);

        $client->request('GET', '/api/opt-out/domains');
        $result = self::getSuccessfulJsonResponse($client);
        self::assertArrayHasKey('results', $result);
        self::assertEquals(1, $result['count']);
        self::assertCount(1, $result['results']);
        self::assertEquals(
            ['optout.greyface.de'],
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
        $domain = self::createOptOutDomain();
        $domain2 = self::createOptOutDomain('second-optout.greyface.de');

        self::initializeDatabaseWithEntities($admin, $domain, $domain2);

        $client->request('GET', '/api/opt-out/domains?start=0&max=1');
        $result = self::getSuccessfulJsonResponse($client);
        self::assertArrayHasKey('results', $result);
        self::assertEquals(2, $result['count']);
        self::assertCount(1, $result['results']);
        self::assertEquals(
            ['optout.greyface.de'],
            array_map(
                static fn(array $r): string => $r['domain'],
                $result['results']
            )
        );
    }

    public function testCreateOptOutDomain(): void
    {
        $admin = self::createAdmin();
        $client = self::createApiClient($admin);

        self::initializeDatabaseWithEntities($admin);

        self::sendApiJsonRequest(
            $client,
            'POST',
            '/api/opt-out/domains',
            [
                'domain' => 'testing.de'
            ]
        );
        self::assertResponseIsSuccessful();
    }

    public function testCreateMultipleOptOutDomain(): void
    {
        $admin = self::createAdmin();
        $client = self::createApiClient($admin);

        self::initializeDatabaseWithEntities($admin);

        self::sendApiJsonRequest(
            $client,
            'POST',
            '/api/opt-out/domains',
            [
                'domain' => [
                    'testing.de',
                    'testing.in'
                ]
            ]
        );
        self::assertResponseIsSuccessful();
    }

    public function testCreateMultipleOptOutDomainDuplicateFailed(): void
    {
        $admin = self::createAdmin();
        $client = self::createApiClient($admin);

        $domain = self::createOptOutDomain();

        self::initializeDatabaseWithEntities($admin, $domain);

        self::sendApiJsonRequest(
            $client,
            'POST',
            '/api/opt-out/domains',
            [
                'domain' =>
                    [
                        'testing-demo.de',
                        'optout.greyface.de'
                    ]
            ]
        );
        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testCreateOptOutDomainDuplicateFailed(): void
    {
        $admin = self::createAdmin();
        $client = self::createApiClient($admin);

        $domain = self::createOptOutDomain();

        self::initializeDatabaseWithEntities($admin, $domain);

        self::sendApiJsonRequest(
            $client,
            'POST',
            '/api/opt-out/domains',
            [
                'domain' => 'optout.greyface.de'
            ]
        );
        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testUpdateOptOutDomain(): void
    {
        $admin = self::createAdmin();
        $client = self::createApiClient($admin);

        $domain = self::createOptOutDomain();

        self::initializeDatabaseWithEntities($admin, $domain);

        self::sendApiJsonRequest(
            $client,
            'PUT',
            '/api/opt-out/domains/edit',
            [
                'dynamicID' => [
                    'domain' => $domain->getDomain()
                ],
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
        $admin = self::createAdmin();
        $client = self::createApiClient($admin);

        $domain = self::createOptOutDomain();

        self::initializeDatabaseWithEntities($admin, $domain);


        self::sendApiJsonRequest(
            $client,
            'DELETE',
            '/api/opt-out/domains/delete',
            [
                'domain' => $domain->getDomain()
            ]);
        self::assertResponseIsSuccessful();

        self::clearEntityManager();
        /** @var OptOutDomain|null $domain */
        [$domain] = self::reloadDatabaseEntities($domain);
        self::assertNull($domain);
    }
}
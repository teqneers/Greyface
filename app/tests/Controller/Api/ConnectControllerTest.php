<?php

namespace App\Tests\Controller\Api;

use App\Test\ApiTestTrait;
use App\Test\AutoWhiteListTrait;
use App\Test\DatabaseTestTrait;
use App\Test\UserAliasTrait;
use App\Test\UserDomainTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/*
    NOTE: for these tests we have already inserted dummy data in table,
    check migration file app/migrations/Version20220721081217.php
*/

class ConnectControllerTest extends WebTestCase
{
    use ApiTestTrait, DatabaseTestTrait, UserDomainTrait, AutoWhiteListTrait, UserAliasTrait;

    public function testList(): void
    {
        $admin = self::createAdmin();
        $client = self::createApiClient($admin);
        $user = self::createUser();

        $alias1 = self::createUserAlias($user, 'jobs@greyface.de');
        $alias2 = self::createUserAlias($user, 'contact@greyface.de');

        self::initializeDatabaseWithEntities($admin, $user, $alias1, $alias2);

        $client->request('GET', '/api/greylist');
        self::getSuccessfulJsonResponse($client);
    }

    public function testListWithPagination(): void
    {
        $admin = self::createAdmin();
        $client = self::createApiClient($admin);

        $user = self::createUser();

        $alias1 = self::createUserAlias($user, 'alias1@example.de');
        $alias2 = self::createUserAlias($user, 'alias2@example.de');

        self::initializeDatabaseWithEntities($admin, $user, $alias1, $alias2);

        $client->request('GET', '/api/greylist?start=0&max=2');
        $result = self::getSuccessfulJsonResponse($client);
        self::assertEquals(5, $result['count']);
        self::assertCount(2, $result['results']);
    }

    public function testMoveToWhiteList(): void
    {
        $admin = self::createAdmin();
        $client = self::createApiClient($admin);

        self::initializeDatabaseWithEntities($admin);

        self::sendApiJsonRequest(
            $client,
            'POST',
            '/api/greylist/toWhiteList',
            [
                'name' => 'greyface',
                'domain' => 'recruit-greyface.de',
                'source' => '15.215.255',
                'rcpt' => 'jobs@greyface.de'
            ]
        );
        self::getSuccessfulJsonResponse($client);
    }

    public function testDeleteToDate(): void
    {
        $admin = self::createAdmin();
        $client = self::createApiClient($admin);

        self::initializeDatabaseWithEntities($admin);

        $client->request('GET', '/api/greylist');
        $result = self::getSuccessfulJsonResponse($client);
        $oldCount = $result['count'];

        self::sendApiJsonRequest(
            $client,
            'DELETE',
            '/api/greylist/delete-to-date',
            [
                'date' => '2022-01-11'
            ]);

        self::assertResponseIsSuccessful();

        $client->request('GET', '/api/greylist');
        $result = self::getSuccessfulJsonResponse($client);
        $newCount = $result['count'];

        self::assertNotEquals($newCount, $oldCount);
    }

    public function testDelete(): void
    {
        $admin = self::createAdmin();
        $client = self::createApiClient($admin);

        self::initializeDatabaseWithEntities($admin);

        self::sendApiJsonRequest(
            $client,
            'DELETE',
            '/api/greylist/delete',
            [
                'name' => 'greyface',
                'domain' => 'recruit-greyface.de',
                'source' => '15.215.255',
                'rcpt' => 'jobs@greyface.de'
            ]);

        self::assertResponseIsSuccessful();
    }

    public function testListCountsEntriesUpToADate(): void
    {
        $admin = self::createAdmin();
        $client = self::createApiClient($admin);
        self::initializeDatabaseWithEntities($admin);

        // Seeded rows: 2022-01-11, 02-11, 03-12 and two on 07-11.
        $client->request('GET', '/api/greylist?start=0&max=1&before=2022-03-12');
        $result = self::getSuccessfulJsonResponse($client);
        self::assertEquals(3, $result['count']);
        self::assertCount(1, $result['results']);
    }

    public function testMoveToWhiteListCanBeUndone(): void
    {
        $admin = self::createAdmin();
        $client = self::createApiClient($admin);
        self::initializeDatabaseWithEntities($admin);

        $entry = [
            'name' => 'helpdesk',
            'domain' => 'greyface.ca',
            'source' => '111.127.0',
            'rcpt' => 'helpdesk@greyface.de',
        ];
        self::sendApiJsonRequest($client, 'POST', '/api/greylist/toWhiteList', $entry);
        $moved = self::getSuccessfulJsonResponse($client);
        self::assertTrue($moved['awlCreated']);
        self::assertSame('helpdesk', $moved['entry']['name']);
        self::assertStringStartsWith('2022-07-11T22:41:41', $moved['entry']['firstSeen']);

        $client->request('GET', '/api/greylist?start=0&max=50&query=helpdesk');
        self::assertEquals(0, self::getSuccessfulJsonResponse($client)['count']);
        $client->request('GET', '/api/awl/emails?start=0&max=50&query=helpdesk');
        self::assertEquals(1, self::getSuccessfulJsonResponse($client)['count']);

        self::sendApiJsonRequest($client, 'POST', '/api/greylist/undoToWhiteList', [
            'entry' => $moved['entry'],
            'removeAwl' => $moved['awlCreated'],
        ]);
        self::assertResponseIsSuccessful();

        $client->request('GET', '/api/greylist?start=0&max=50&query=helpdesk');
        $restored = self::getSuccessfulJsonResponse($client);
        self::assertEquals(1, $restored['count']);
        self::assertStringStartsWith('2022-07-11 22:41:41', $restored['results'][0]['connect']['firstSeen']['date']);
        $client->request('GET', '/api/awl/emails?start=0&max=50&query=helpdesk');
        self::assertEquals(0, self::getSuccessfulJsonResponse($client)['count']);
    }

    public function testBulkMoveToWhiteList(): void
    {
        $admin = self::createAdmin();
        $client = self::createApiClient($admin);
        self::initializeDatabaseWithEntities($admin);

        self::sendApiJsonRequest($client, 'POST', '/api/greylist/bulk/toWhiteList', [
            'entries' => [
                ['name' => 'mailyz', 'domain' => 'greyface.org', 'source' => '89.163.12', 'rcpt' => 'dummy@greyface.de'],
                ['name' => 'info', 'domain' => 'greyface.com', 'source' => '125.253.92', 'rcpt' => 'info@greyface.de'],
                ['name' => 'gone', 'domain' => 'nowhere.test', 'source' => '0.0.0', 'rcpt' => 'nobody@greyface.de'],
            ],
        ]);
        $result = self::getSuccessfulJsonResponse($client);
        self::assertSame(2, $result['moved']);

        $client->request('GET', '/api/greylist?start=0&max=50');
        self::assertEquals(3, self::getSuccessfulJsonResponse($client)['count']);
    }

    public function testBulkDelete(): void
    {
        $admin = self::createAdmin();
        $client = self::createApiClient($admin);
        self::initializeDatabaseWithEntities($admin);

        self::sendApiJsonRequest($client, 'DELETE', '/api/greylist/bulk/delete', [
            'entries' => [
                ['name' => 'mailyz', 'domain' => 'greyface.org', 'source' => '89.163.12', 'rcpt' => 'dummy@greyface.de'],
                ['name' => 'info', 'domain' => 'greyface.com', 'source' => '125.253.92', 'rcpt' => 'info@greyface.de'],
            ],
        ]);
        $result = self::getSuccessfulJsonResponse($client);
        self::assertSame(2, $result['deleted']);

        $client->request('GET', '/api/greylist?start=0&max=50');
        self::assertEquals(3, self::getSuccessfulJsonResponse($client)['count']);
    }

    public function testDeleteToDateWithoutDateIsRejected(): void
    {
        $admin = self::createAdmin();
        $client = self::createApiClient($admin);
        self::initializeDatabaseWithEntities($admin);

        self::sendApiJsonRequest($client, 'DELETE', '/api/greylist/delete-to-date', []);
        self::assertResponseStatusCodeSame(422);
    }
}

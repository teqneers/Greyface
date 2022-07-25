<?php

namespace App\Tests\Controller\Api;

use App\Domain\Entity\AutoWhiteList\EmailAutoWhiteList\EmailAutoWhiteList;
use App\Test\ApiTestTrait;
use App\Test\AutoWhiteListTrait;
use App\Test\DatabaseTestTrait;
use App\Test\UserDomainTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/*
    NOTE: for these tests we have already inserted dummy data in table,
    check migration file app/migrations/Version20220721081217.php
*/

class ConnectControllerTest extends WebTestCase
{
    use ApiTestTrait, DatabaseTestTrait, UserDomainTrait, AutoWhiteListTrait;

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
                'dynamicId' => [
                    'name' => 'greyface',
                    'domain' => 'recruit-greyface.de',
                    'source' => '15.215.255',
                    'rcpt' => 'jobs@greyface.de'
                ]
            ]);

        self::assertResponseIsSuccessful();
    }
}
<?php

namespace App\Tests\Controller\Api\OptIn;

use App\Domain\Entity\OptIn\OptInEmail\OptInEmail;
use App\Test\ApiTestTrait;
use App\Test\DatabaseTestTrait;
use App\Test\OptInOptOutTrait;
use App\Test\UserDomainTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class OptInEmailControllerTest extends WebTestCase
{
    use ApiTestTrait, DatabaseTestTrait, UserDomainTrait, OptInOptOutTrait;

    public function testListUsersNotAllowedForNonAdmins(): void
    {
        $user = self::createUser();
        $client = self::createApiClient($user);

        self::initializeDatabaseWithEntities($user);

        $client->request('GET', '/api/opt-in/emails');
        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testListUsersWithoutPagination(): void
    {
        $admin = self::createAdmin();
        $client = self::createApiClient($admin);

        $email = self::createOptInEmail();

        self::initializeDatabaseWithEntities($admin, $email);

        $client->request('GET', '/api/opt-in/emails');
        $result = self::getSuccessfulJsonResponse($client);
        self::assertArrayHasKey('results', $result);
        self::assertEquals(1, $result['count']);
        self::assertCount(1, $result['results']);
        self::assertEquals(
            ['optin@email.de'],
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

        $email = self::createOptInEmail();
        $email2 = self::createOptInEmail('optin2@email.de');

        self::initializeDatabaseWithEntities($admin, $email, $email2);

        $client->request('GET', '/api/opt-in/emails?start=0&max=1');
        $result = self::getSuccessfulJsonResponse($client);
        self::assertArrayHasKey('results', $result);
        self::assertEquals(2, $result['count']);
        self::assertCount(1, $result['results']);
        self::assertEquals(
            ['optin@email.de'],
            array_map(
                static fn(array $r): string => $r['email'],
                $result['results']
            )
        );
    }

    public function testCreateOptInEmail(): void
    {
        $admin = self::createAdmin();
        $client = self::createApiClient($admin);

        self::initializeDatabaseWithEntities($admin);

        self::sendApiJsonRequest(
            $client,
            'POST',
            '/api/opt-in/emails',
            [
                'email' => 'optin@email.de'
            ]
        );
        self::assertResponseIsSuccessful();
    }


    public function testCreateMultipleOptInEmail(): void
    {
        $admin = self::createAdmin();
        $client = self::createApiClient($admin);

        self::initializeDatabaseWithEntities($admin);

        self::sendApiJsonRequest(
            $client,
            'POST',
            '/api/opt-in/emails',
            [
                'email' => [
                    'optin@email.de',
                    'test@email.de'
                ]
            ]
        );
        self::assertResponseIsSuccessful();
    }


    public function testCreateMultipleOptInEmailDuplicateFailed(): void
    {
        $admin = self::createAdmin();
        $client = self::createApiClient($admin);

        $email = self::createOptInEmail();

        self::initializeDatabaseWithEntities($admin, $email);

        self::sendApiJsonRequest(
            $client,
            'POST',
            '/api/opt-in/emails',
            [
                'email' =>
                    [
                        'optin@email.de',
                        'test@email.de'
                    ]
            ]
        );
        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testCreateOptInEmailDuplicateFailed(): void
    {
        $admin = self::createAdmin();
        $client = self::createApiClient($admin);
        $email = self::createOptInEmail();

        self::initializeDatabaseWithEntities($admin, $email);

        self::sendApiJsonRequest(
            $client,
            'POST',
            '/api/opt-in/emails',
            [
                'email' => 'optin@email.de'
            ]
        );
        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testUpdateOptInEmail(): void
    {
        $admin = self::createAdmin();
        $client = self::createApiClient($admin);

        $email = self::createOptInEmail();

        self::initializeDatabaseWithEntities($admin, $email);

        self::sendApiJsonRequest(
            $client,
            'PUT',
            '/api/opt-in/emails/edit',
            [
                'dynamicID' => [
                    'email' => $email->getEmail()
                ],
                'email' => 'optinup@email.de'
            ]
        );
        $result = self::getSuccessfulJsonResponse($client);
        self::assertArrayHasKey('email', $result);
        self::clearEntityManager();
        /** @var OptInEmail|null $email */
        $email = self::loadDatabaseEntity(OptInEmail::class, $result['email']);
        self::assertNotNull($email);
        self::assertSame('optinup@email.de', $email->getEmail());
    }

    public function testDeleteOptInEmail(): void
    {
        $admin = self::createAdmin();
        $client = self::createApiClient($admin);

        $email = self::createOptInEmail();

        self::initializeDatabaseWithEntities($admin, $email);

        self::sendApiJsonRequest(
            $client,
            'DELETE',
            '/api/opt-in/emails/delete',
            [
                'email' => $email->getEmail()
            ]);

        self::assertResponseIsSuccessful();

        self::clearEntityManager();
        /** @var OptInEmail|null $email */
        [$email] = self::reloadDatabaseEntities($email);
        self::assertNull($email);
    }
}
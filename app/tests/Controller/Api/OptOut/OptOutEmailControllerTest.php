<?php

namespace App\Tests\Controller\Api\OptOut;

use App\Domain\Entity\OptOut\OptOutEmail\OptOutEmail;
use App\Test\ApiTestTrait;
use App\Test\DatabaseTestTrait;
use App\Test\OptInOptOutTrait;
use App\Test\UserDomainTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class OptOutEmailControllerTest extends WebTestCase
{
    use ApiTestTrait, DatabaseTestTrait, UserDomainTrait, OptInOptOutTrait;

    public function testListUsersNotAllowedForNonAdmins(): void
    {
        $user = self::createUser();
        $client = self::createApiClient($user);

        self::initializeDatabaseWithEntities($user);

        $client->request('GET', '/api/opt-out/emails');
        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testListUsersWithoutPagination(): void
    {
        $admin = self::createAdmin();
        $client = self::createApiClient($admin);

        $email = self::createOptOutEmail();

        self::initializeDatabaseWithEntities($admin, $email);

        $client->request('GET', '/api/opt-out/emails');
        $result = self::getSuccessfulJsonResponse($client);
        self::assertArrayHasKey('results', $result);
        self::assertEquals(1, $result['count']);
        self::assertCount(1, $result['results']);
        self::assertEquals(
            ['optout@email.de'],
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
        $email = self::createOptOutEmail();
        $email2 = self::createOptOutEmail('optout2@email.de');

        self::initializeDatabaseWithEntities($admin, $email, $email2);

        $client->request('GET', '/api/opt-out/emails?start=0&max=1');
        $result = self::getSuccessfulJsonResponse($client);
        self::assertArrayHasKey('results', $result);
        self::assertEquals(2, $result['count']);
        self::assertCount(1, $result['results']);
        self::assertEquals(
            ['optout@email.de'],
            array_map(
                static fn(array $r): string => $r['email'],
                $result['results']
            )
        );
    }

    public function testCreateOptOutEmail(): void
    {
        $admin = self::createAdmin();
        $client = self::createApiClient($admin);

        self::initializeDatabaseWithEntities($admin);

        self::sendApiJsonRequest(
            $client,
            'POST',
            '/api/opt-out/emails',
            [
                'email' => 'optout@email.de'
            ]
        );
        $result = self::getSuccessfulJsonResponse($client);
        self::assertArrayHasKey('email', $result);
        self::clearEntityManager();
        /** @var OptOutEmail|null $email */
        $email = self::loadDatabaseEntity(OptOutEmail::class, $result['email']);
        self::assertNotNull($email);
        self::assertSame('optout@email.de', $email->getEmail());
    }

    public function testCreateOptOutEmailDuplicateFailed(): void
    {
        $admin = self::createAdmin();
        $client = self::createApiClient($admin);

        $email = self::createOptOutEmail();

        self::initializeDatabaseWithEntities($admin, $email);

        self::sendApiJsonRequest(
            $client,
            'POST',
            '/api/opt-out/emails',
            [
                'email' => 'optout@email.de'
            ]
        );
        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testUpdateOptOutEmail(): void
    {
        $admin = self::createAdmin();
        $client = self::createApiClient($admin);

        $email = self::createOptOutEmail();

        self::initializeDatabaseWithEntities($admin, $email);

        self::sendApiJsonRequest(
            $client,
            'PUT',
            '/api/opt-out/emails/' . $email->getEmail(),
            [
                'email' => 'optout-up@email.de'
            ]
        );
        $result = self::getSuccessfulJsonResponse($client);
        self::assertArrayHasKey('email', $result);
        self::clearEntityManager();
        /** @var OptOutEmail|null $email */
        $email = self::loadDatabaseEntity(OptOutEmail::class, $result['email']);
        self::assertNotNull($email);
        self::assertSame('optout-up@email.de', $email->getEmail());
    }

    public function testDeleteOptOutEmail(): void
    {
        $admin = self::createAdmin();
        $client = self::createApiClient($admin);

        $email = self::createOptOutEmail();

        self::initializeDatabaseWithEntities($admin, $email);

        $client->request('DELETE', '/api/opt-out/emails/' . $email->getEmail());

        self::assertResponseIsSuccessful();

        self::clearEntityManager();
        /** @var OptOutEmail|null $email */
        [$email] = self::reloadDatabaseEntities($email);
        self::assertNull($email);
    }
}
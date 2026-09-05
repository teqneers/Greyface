<?php

namespace App\Tests\Controller\Api;

use App\Domain\Entity\User\UserRepository;
use App\Domain\Entity\UserAlias\UserAliasRepository;
use App\Test\ApiTestTrait;
use App\Test\DatabaseTestTrait;
use App\Test\UserAliasTrait;
use App\Test\UserDomainTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * The import endpoint behind the alias screen's paste box and file import. The
 * rules it enforces are the importer's and are tested there; this covers the
 * endpoint's own job: who may call it, what shapes it accepts, and that a dry
 * run really writes nothing.
 */
class UserAliasImportTest extends WebTestCase
{
    use ApiTestTrait, DatabaseTestTrait, UserDomainTrait, UserAliasTrait;

    /**
     * @return string[]
     */
    private function aliasesOf(string $username): array
    {
        $user = self::getContainer()->get(UserRepository::class)->findByUsername($username);
        self::assertNotNull($user);

        $names = self::getContainer()->get(UserAliasRepository::class)->findAliasNamesForUserId($user->getId());
        sort($names);

        return $names;
    }

    public function testImportsATwoColumnList(): void
    {
        $admin = self::createAdmin();
        $anna = self::createUser(username: 'anna');
        $client = self::createApiClient($admin);
        self::initializeDatabaseWithEntities($admin, $anna);

        self::sendApiJsonRequest($client, 'POST', '/api/users-aliases/import', [
            'content' => "anna@example.com,anna\nsales@example.com,anna",
        ]);
        $result = self::getSuccessfulJsonResponse($client);

        self::assertSame(2, $result['created']);
        self::assertSame([], $result['problems']);
        self::assertSame(['anna@example.com', 'sales@example.com'], $this->aliasesOf('anna'));
    }

    /**
     * The dialog's paste box: many addresses, one account, no username column.
     */
    public function testImportsAPlainAddressListForOneAccount(): void
    {
        $admin = self::createAdmin();
        $anna = self::createUser(username: 'anna');
        $client = self::createApiClient($admin);
        self::initializeDatabaseWithEntities($admin, $anna);

        self::sendApiJsonRequest($client, 'POST', '/api/users-aliases/import', [
            'content' => "anna@example.com\nsales@example.com",
            'username' => 'anna',
        ]);
        $result = self::getSuccessfulJsonResponse($client);

        self::assertSame(2, $result['created']);
        self::assertSame(['anna@example.com', 'sales@example.com'], $this->aliasesOf('anna'));
    }

    public function testADryRunReportsWithoutWriting(): void
    {
        $admin = self::createAdmin();
        $anna = self::createUser(username: 'anna');
        $client = self::createApiClient($admin);
        self::initializeDatabaseWithEntities($admin, $anna);

        self::sendApiJsonRequest($client, 'POST', '/api/users-aliases/import', [
            'content' => 'anna@example.com,anna',
            'dryRun' => true,
        ]);
        $result = self::getSuccessfulJsonResponse($client);

        self::assertSame(1, $result['created']);
        self::assertSame([], $this->aliasesOf('anna'), 'a preview must write nothing');
    }

    public function testReportsUnreadableLinesWithTheirLineNumbers(): void
    {
        $admin = self::createAdmin();
        $anna = self::createUser(username: 'anna');
        $client = self::createApiClient($admin);
        self::initializeDatabaseWithEntities($admin, $anna);

        self::sendApiJsonRequest($client, 'POST', '/api/users-aliases/import', [
            'content' => "anna@example.com,anna\nnonsense\nghost@example.com,nobody",
        ]);
        $result = self::getSuccessfulJsonResponse($client);

        self::assertSame(1, $result['created'], 'the readable line still applies');
        self::assertSame([2, 3], array_column($result['problems'], 'line'));
        self::assertSame(['missingUsername', 'unknownUser'], array_column($result['problems'], 'reason'));
    }

    public function testRefusesAnEmptyBody(): void
    {
        $admin = self::createAdmin();
        $client = self::createApiClient($admin);
        self::initializeDatabaseWithEntities($admin);

        self::sendApiJsonRequest($client, 'POST', '/api/users-aliases/import', ['content' => "   \n\n"]);
        self::assertResponseStatusCodeSame(422);
    }

    /**
     * Assigning addresses to accounts is administration, and the alias screen is
     * administrators-only already.
     */
    public function testOrdinaryUsersMayNotImport(): void
    {
        $user = self::createUser(username: 'anna');
        $client = self::createApiClient($user);
        self::initializeDatabaseWithEntities($user);

        self::sendApiJsonRequest($client, 'POST', '/api/users-aliases/import', [
            'content' => 'anna@example.com,anna',
        ]);
        self::assertResponseStatusCodeSame(403);

        self::assertSame([], $this->aliasesOf('anna'));
    }
}

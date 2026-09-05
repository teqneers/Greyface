<?php

namespace App\Tests\Controller\Api;

use App\Domain\Entity\AutoWhiteList\DomainAutoWhiteList\DomainAutoWhiteListRepository;
use App\Domain\Entity\Connect\ConnectRepository;
use App\Domain\Entity\OptIn\OptInDomain\OptInDomainRepository;
use App\Domain\Entity\OptIn\OptInEmail\OptInEmailRepository;
use App\Domain\Entity\OptOut\OptOutDomain\OptOutDomainRepository;
use App\Domain\Entity\OptOut\OptOutEmail\OptOutEmailRepository;
use App\Test\ApiTestTrait;
use App\Test\ConnectTrait;
use App\Test\DatabaseTestTrait;
use App\Test\UserAliasTrait;
use App\Test\UserDomainTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Sending a greylisted entry somewhere other than the auto-whitelist.
 *
 * The five destinations here all write to lists only administrators may edit,
 * which is the difference from the auto-whitelist action an ordinary user is
 * allowed to take on their own mail.
 *
 * One of the seeded rows is used throughout: greyface@recruit-greyface.de from
 * 15.215.255, addressed to jobs@greyface.de.
 */
class ConnectListTargetTest extends WebTestCase
{
    use ApiTestTrait, DatabaseTestTrait, UserDomainTrait, UserAliasTrait, ConnectTrait;

    private const ENTRY = [
        'name' => 'greyface',
        'domain' => 'recruit-greyface.de',
        'source' => '15.215.255',
        'rcpt' => 'jobs@greyface.de',
    ];

    public static function targets(): iterable
    {
        yield 'whitelist the sender' => ['whitelist-email', OptOutEmailRepository::class];
        yield 'whitelist the domain' => ['whitelist-domain', OptOutDomainRepository::class];
        yield 'blacklist the sender' => ['blacklist-email', OptInEmailRepository::class];
        yield 'blacklist the domain' => ['blacklist-domain', OptInDomainRepository::class];
        yield 'auto-whitelist the domain' => ['auto-whitelist-domain', DomainAutoWhiteListRepository::class];
    }

    #[DataProvider('targets')]
    public function testAdministratorCanSendAnEntryToEachList(string $target, string $repository): void
    {
        $admin = self::createAdmin();
        $client = self::createApiClient($admin);
        self::initializeDatabaseWithEntities($admin);

        self::sendApiJsonRequest($client, 'POST', '/api/greylist/toList', [
            'target' => $target,
            'entries' => [self::ENTRY],
        ]);
        $result = self::getSuccessfulJsonResponse($client);

        self::assertSame(1, $result['moved']);
        self::assertTrue($result['entries'][0]['created'], 'the list row should be new');
        self::assertCount(1, self::getContainer()->get($repository)->findAll());

        // The entry stops waiting, exactly as the auto-whitelist action does.
        self::assertNull(self::getContainer()->get(ConnectRepository::class)->find(self::ENTRY));
    }

    public function testOrdinaryUsersMayNotReachTheseLists(): void
    {
        $user = self::createUser();
        $alias = self::createUserAlias($user, self::ENTRY['rcpt']);
        $client = self::createApiClient($user);
        self::initializeDatabaseWithEntities($user, $alias);

        // The row is theirs — they can see it and could auto-whitelist it — but
        // the whitelist is policy for the whole server.
        self::sendApiJsonRequest($client, 'POST', '/api/greylist/toList', [
            'target' => 'whitelist-email',
            'entries' => [self::ENTRY],
        ]);
        self::assertResponseStatusCodeSame(403);

        self::assertNotNull(self::getContainer()->get(ConnectRepository::class)->find(self::ENTRY));
    }

    public function testAnUnknownTargetIsRefused(): void
    {
        $admin = self::createAdmin();
        $client = self::createApiClient($admin);
        self::initializeDatabaseWithEntities($admin);

        self::sendApiJsonRequest($client, 'POST', '/api/greylist/toList', [
            'target' => 'delete-everything',
            'entries' => [self::ENTRY],
        ]);
        self::assertResponseStatusCodeSame(422);
    }

    public function testUndoPutsTheEntryBackAndRemovesTheListRow(): void
    {
        $admin = self::createAdmin();
        $client = self::createApiClient($admin);
        self::initializeDatabaseWithEntities($admin);

        self::sendApiJsonRequest($client, 'POST', '/api/greylist/toList', [
            'target' => 'blacklist-email',
            'entries' => [self::ENTRY],
        ]);
        $moved = self::getSuccessfulJsonResponse($client);

        self::sendApiJsonRequest($client, 'POST', '/api/greylist/undoToList', [
            'target' => 'blacklist-email',
            'entries' => $moved['entries'],
        ]);
        self::getSuccessfulJsonResponse($client);

        self::assertNotNull(self::getContainer()->get(ConnectRepository::class)->find(self::ENTRY));
        self::assertCount(0, self::getContainer()->get(OptInEmailRepository::class)->findAll());
    }

    /**
     * An address that was already listed before the move was not put there by
     * this operator, so undo must leave it alone. Only the greylist row, which
     * this move did remove, comes back.
     */
    public function testUndoLeavesAnAddressThatWasAlreadyListed(): void
    {
        $admin = self::createAdmin();
        $client = self::createApiClient($admin);
        self::initializeDatabaseWithEntities($admin);

        // Move it, undo, then move and undo again: the second move finds the
        // address already listed only if the first undo removed it, so instead
        // list it up front by moving a second row with the same sender domain.
        self::sendApiJsonRequest($client, 'POST', '/api/greylist/toList', [
            'target' => 'blacklist-domain',
            'entries' => [self::ENTRY],
        ]);
        self::getSuccessfulJsonResponse($client);

        // A different row, same sender domain: the domain is already blacklisted.
        $second = ['name' => 'other', 'domain' => 'recruit-greyface.de', 'source' => '10.0.0.9', 'rcpt' => 'jobs@greyface.de'];
        self::initializeDatabaseWithEntities(self::createConnect(
            senderName: $second['name'],
            senderDomain: $second['domain'],
            source: $second['source'],
            rcpt: $second['rcpt']
        ));

        self::sendApiJsonRequest($client, 'POST', '/api/greylist/toList', [
            'target' => 'blacklist-domain',
            'entries' => [$second],
        ]);
        $moved = self::getSuccessfulJsonResponse($client);
        self::assertFalse($moved['entries'][0]['created'], 'the domain was already blacklisted');

        self::sendApiJsonRequest($client, 'POST', '/api/greylist/undoToList', [
            'target' => 'blacklist-domain',
            'entries' => $moved['entries'],
        ]);
        self::getSuccessfulJsonResponse($client);

        self::assertCount(
            1,
            self::getContainer()->get(OptInDomainRepository::class)->findAll(),
            'undo must not remove a listing it did not create'
        );
    }

    /**
     * SQLGrey rewrites the sender before storing it, so a VERP or SRS address
     * lists under the address a human would recognise rather than the one-shot
     * form that will never be seen again.
     */
    public function testTheSenderIsNormalisedBeforeBeingListed(): void
    {
        $admin = self::createAdmin();
        $client = self::createApiClient($admin);
        $verp = self::createConnect(
            senderName: 'newsletter+bounce-12345',
            senderDomain: 'lists.example.com',
            source: '203.0.113',
            rcpt: 'jobs@greyface.de'
        );
        self::initializeDatabaseWithEntities($admin, $verp);

        self::sendApiJsonRequest($client, 'POST', '/api/greylist/toList', [
            'target' => 'whitelist-email',
            'entries' => [[
                'name' => 'newsletter+bounce-12345',
                'domain' => 'lists.example.com',
                'source' => '203.0.113',
                'rcpt' => 'jobs@greyface.de',
            ]],
        ]);
        self::getSuccessfulJsonResponse($client);

        $listed = self::getContainer()->get(OptOutEmailRepository::class)->findAll();
        self::assertCount(1, $listed);
        self::assertSame('newsletter@lists.example.com', $listed[0]->getEmail());
    }
}

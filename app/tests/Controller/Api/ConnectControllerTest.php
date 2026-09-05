<?php

namespace App\Tests\Controller\Api;

use App\Domain\Entity\Connect\ConnectRepository;
use App\Test\ApiTestTrait;
use App\Test\ConnectTrait;
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
    use ApiTestTrait, DatabaseTestTrait, UserDomainTrait, AutoWhiteListTrait, UserAliasTrait, ConnectTrait;

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

    // ---------------------------------------------------------------- non-admins
    //
    // Every other test in this class signs in as an administrator, which is how
    // the greylist shipped with an open API: the listing was filtered per user,
    // but the write endpoints took their identifiers from the request body and
    // never checked who was asking. These sign in as an ordinary user instead.
    //
    // The five seeded rows are addressed to jobs@, dummy@, info@, helpdesk@ and
    // contact@greyface.de; the user below owns exactly one of them.

    private const OWN_ENTRY = [
        'name' => 'info',
        'domain' => 'greyface.com',
        'source' => '125.253.92',
        'rcpt' => 'info@greyface.de',
    ];

    private const SOMEBODY_ELSES_ENTRY = [
        'name' => 'helpdesk',
        'domain' => 'greyface.ca',
        'source' => '111.127.0',
        'rcpt' => 'helpdesk@greyface.de',
    ];

    /**
     * @return array{0: \Symfony\Bundle\FrameworkBundle\KernelBrowser}
     */
    private function signedInAsPlainUser(): array
    {
        $user = self::createUser();
        $alias = self::createUserAlias($user, self::OWN_ENTRY['rcpt']);
        $client = self::createApiClient($user);

        self::initializeDatabaseWithEntities($user, $alias);

        return [$client];
    }

    /**
     * The whole point of the product: a recipient releases their own held mail
     * without asking an administrator. It used to answer 403, because the
     * endpoint required the administrator-only EMAIL_AUTOWHITE_CREATE.
     */
    public function testUserMayWhitelistMailAddressedToThemselves(): void
    {
        [$client] = $this->signedInAsPlainUser();

        self::sendApiJsonRequest($client, 'POST', '/api/greylist/toWhiteList', self::OWN_ENTRY);
        self::getSuccessfulJsonResponse($client);

        $client->request('GET', '/api/greylist');
        $result = self::getSuccessfulJsonResponse($client);
        self::assertSame(0, $result['count'], 'the released entry should have left the greylist');
    }

    public function testUserMayNotWhitelistSomebodyElsesMail(): void
    {
        [$client] = $this->signedInAsPlainUser();

        self::sendApiJsonRequest($client, 'POST', '/api/greylist/toWhiteList', self::SOMEBODY_ELSES_ENTRY);
        self::assertResponseStatusCodeSame(403);
    }

    public function testUserMayDeleteMailAddressedToThemselves(): void
    {
        [$client] = $this->signedInAsPlainUser();

        self::sendApiJsonRequest($client, 'DELETE', '/api/greylist/delete', self::OWN_ENTRY);
        self::assertResponseIsSuccessful();
    }

    public function testUserMayNotDeleteSomebodyElsesMail(): void
    {
        [$client] = $this->signedInAsPlainUser();

        self::sendApiJsonRequest($client, 'DELETE', '/api/greylist/delete', self::SOMEBODY_ELSES_ENTRY);
        self::assertResponseStatusCodeSame(403);
    }

    /**
     * A bulk call must not become a way to smuggle a foreign row past the check
     * by pairing it with one of your own.
     */
    public function testBulkDeleteRefusesABatchContainingSomebodyElsesMail(): void
    {
        [$client] = $this->signedInAsPlainUser();

        self::sendApiJsonRequest($client, 'DELETE', '/api/greylist/bulk/delete', [
            'entries' => [self::OWN_ENTRY, self::SOMEBODY_ELSES_ENTRY],
        ]);
        self::assertResponseStatusCodeSame(403);
    }

    /**
     * One request empties the greylist for every recipient on the server. The
     * interface has always hidden it from users; the API used to allow it.
     */
    public function testUserMayNotDeleteTheWholeGreylistByDate(): void
    {
        [$client] = $this->signedInAsPlainUser();

        self::sendApiJsonRequest($client, 'DELETE', '/api/greylist/delete-to-date', ['date' => '2099-12-31']);
        self::assertResponseStatusCodeSame(403);

        // Straight from the repository rather than a second client: the kernel is
        // already booted, and the user's own listing would only ever show one row.
        self::assertCount(
            5,
            self::getContainer()->get(ConnectRepository::class)->findAll(),
            'the greylist must be untouched'
        );
    }

    public function testUserOnlySeesMailAddressedToThemselves(): void
    {
        [$client] = $this->signedInAsPlainUser();

        $client->request('GET', '/api/greylist');
        $result = self::getSuccessfulJsonResponse($client);

        self::assertSame(1, $result['count']);
        self::assertSame(self::OWN_ENTRY['rcpt'], $result['results'][0]['connect']['rcpt']);
    }

    // ------------------------------------------------------- tagged recipients
    //
    // Issue #80. Postfix delivers anna+tag@example.com to anna@example.com when
    // recipient_delimiter is set, but Greyface compared connect.rcpt to the
    // alias exactly, so tagged mail belonged to nobody: the recipient could
    // neither see nor release it.
    //
    // These go through the HTTP endpoints on purpose. The matching happens in a
    // DQL join using SUBSTRING_INDEX, and a unit test of the rule would still
    // pass if that join produced invalid SQL.

    public function testUserSeesMailSentToATaggedFormOfTheirAddress(): void
    {
        $user = self::createUser();
        $alias = self::createUserAlias($user, 'info@greyface.de');
        $tagged = self::createConnect(
            senderName: 'shop',
            senderDomain: 'example.com',
            source: '198.51.100',
            rcpt: 'info+newsletter@greyface.de'
        );
        $client = self::createApiClient($user);

        self::initializeDatabaseWithEntities($user, $alias, $tagged);

        $client->request('GET', '/api/greylist');
        $result = self::getSuccessfulJsonResponse($client);

        $recipients = array_column(array_column($result['results'], 'connect'), 'rcpt');
        self::assertContains('info+newsletter@greyface.de', $recipients);
        self::assertContains('info@greyface.de', $recipients, 'the untagged address must still match');
    }

    public function testUserMayWhitelistMailSentToATaggedFormOfTheirAddress(): void
    {
        $user = self::createUser();
        $alias = self::createUserAlias($user, 'info@greyface.de');
        $tagged = self::createConnect(
            senderName: 'shop',
            senderDomain: 'example.com',
            source: '198.51.100',
            rcpt: 'info+newsletter@greyface.de'
        );
        $client = self::createApiClient($user);

        self::initializeDatabaseWithEntities($user, $alias, $tagged);

        self::sendApiJsonRequest($client, 'POST', '/api/greylist/toWhiteList', [
            'name' => 'shop',
            'domain' => 'example.com',
            'source' => '198.51.100',
            'rcpt' => 'info+newsletter@greyface.de',
        ]);
        self::getSuccessfulJsonResponse($client);
    }

    /**
     * The widening has a limit: it maps a tag onto the address it is delivered
     * to, and nothing else. Owning info@ must not confer somebody else's mailbox.
     */
    public function testTagMatchingDoesNotReachAnotherMailbox(): void
    {
        $user = self::createUser();
        $alias = self::createUserAlias($user, 'info@greyface.de');
        $other = self::createConnect(
            senderName: 'shop',
            senderDomain: 'example.com',
            source: '198.51.100',
            rcpt: 'infodesk@greyface.de'
        );
        $client = self::createApiClient($user);

        self::initializeDatabaseWithEntities($user, $alias, $other);

        self::sendApiJsonRequest($client, 'DELETE', '/api/greylist/delete', [
            'name' => 'shop',
            'domain' => 'example.com',
            'source' => '198.51.100',
            'rcpt' => 'infodesk@greyface.de',
        ]);
        self::assertResponseStatusCodeSame(403);
    }

    /**
     * An administrator's listing annotates each row with the user it belongs to,
     * through the same join, so a tagged row must name its owner too.
     */
    public function testAdministratorSeesWhoOwnsATaggedRecipient(): void
    {
        $admin = self::createAdmin();
        $user = self::createUser();
        $alias = self::createUserAlias($user, 'info@greyface.de');
        $tagged = self::createConnect(
            senderName: 'shop',
            senderDomain: 'example.com',
            source: '198.51.100',
            rcpt: 'info+newsletter@greyface.de'
        );
        $client = self::createApiClient($admin);

        self::initializeDatabaseWithEntities($admin, $user, $alias, $tagged);

        $client->request('GET', '/api/greylist?max=50');
        $result = self::getSuccessfulJsonResponse($client);

        $owners = [];
        foreach ($result['results'] as $row) {
            $owners[$row['connect']['rcpt']] = $row['username'];
        }
        self::assertSame($user->getUsername(), $owners['info+newsletter@greyface.de'] ?? null);
    }
}

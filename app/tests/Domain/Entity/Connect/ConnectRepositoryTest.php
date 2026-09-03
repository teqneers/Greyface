<?php

namespace App\Tests\Domain\Entity\Connect;

use App\Domain\Entity\Connect\ConnectRepository;
use App\Test\ConnectTrait;
use App\Test\DatabaseTestTrait;
use App\Test\UserAliasTrait;
use App\Test\UserDomainTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * The greylist repository carries the application's core authorisation rule: an
 * ordinary user must only ever see mail addressed to one of their own aliases.
 * That filter had no coverage — every existing controller test authenticated as
 * an administrator.
 *
 * It also holds the only MySQL-specific DQL in the codebase (DATE()), which is
 * exactly the kind of thing a Doctrine upgrade breaks.
 */
class ConnectRepositoryTest extends KernelTestCase
{
    use DatabaseTestTrait, UserDomainTrait, UserAliasTrait, ConnectTrait;

    private function repository(): ConnectRepository
    {
        return self::getContainer()->get(ConnectRepository::class);
    }

    /**
     * @return string[] the rcpt of every returned row
     */
    private static function recipientsOf(array $result): array
    {
        return array_map(
            static fn(array $row): string => $row['connect']['rcpt'],
            $result['results']
        );
    }

    public function testListsEveryEntryForAnUnfilteredQuery(): void
    {
        self::initializeDatabaseWithEntities(
            self::createConnect('a', 'a.greyface.test', '10.0.0.1', 'mine@greyface.test'),
        );
        self::clearEntityManager();

        $result = $this->repository()->findAll();

        self::assertArrayHasKey('count', $result);
        self::assertArrayHasKey('results', $result);
        self::assertContains('mine@greyface.test', self::recipientsOf($result));
    }

    /**
     * The rule the whole application rests on.
     */
    public function testRestrictsTheListingToTheGivenUsersAliases(): void
    {
        $user = self::createUser('scoped', 'scoped@greyface.test');
        $other = self::createUser('someone-else', 'else@greyface.test');

        self::initializeDatabaseWithEntities(
            $user,
            $other,
            self::createUserAlias($user, 'mine@greyface.test'),
            self::createUserAlias($other, 'theirs@greyface.test'),
            self::createConnect('a', 'a.greyface.test', '10.0.0.1', 'mine@greyface.test'),
            self::createConnect('b', 'b.greyface.test', '10.0.0.2', 'theirs@greyface.test'),
        );
        self::clearEntityManager();

        $recipients = self::recipientsOf($this->repository()->findAll($user));

        self::assertContains('mine@greyface.test', $recipients);
        self::assertNotContains(
            'theirs@greyface.test',
            $recipients,
            'a user must never see mail addressed to somebody else'
        );
    }

    public function testShowUnassignedReturnsOnlyEntriesWithoutAnAlias(): void
    {
        $user = self::createUser('assigned', 'assigned@greyface.test');

        self::initializeDatabaseWithEntities(
            $user,
            self::createUserAlias($user, 'claimed@greyface.test'),
            self::createConnect('a', 'a.greyface.test', '10.0.0.1', 'claimed@greyface.test'),
            self::createConnect('b', 'b.greyface.test', '10.0.0.2', 'orphan@greyface.test'),
        );
        self::clearEntityManager();

        $recipients = self::recipientsOf($this->repository()->findAll('show_unassigned'));

        self::assertContains('orphan@greyface.test', $recipients);
        self::assertNotContains('claimed@greyface.test', $recipients);
    }

    public function testSearchesAcrossEveryColumn(): void
    {
        self::initializeDatabaseWithEntities(
            self::createConnect('needlename', 'a.greyface.test', '10.0.0.1', 'a@greyface.test'),
            self::createConnect('b', 'needledomain.test', '10.0.0.2', 'b@greyface.test'),
            self::createConnect('c', 'c.greyface.test', '10.0.0.3', 'needle@greyface.test'),
            self::createConnect('d', 'd.greyface.test', '10.0.0.4', 'nomatch@greyface.test'),
        );
        self::clearEntityManager();

        $recipients = self::recipientsOf($this->repository()->findAll(null, 'needle'));

        self::assertContains('a@greyface.test', $recipients);
        self::assertContains('b@greyface.test', $recipients);
        self::assertContains('needle@greyface.test', $recipients);
        self::assertNotContains('nomatch@greyface.test', $recipients);
    }

    public function testCountReflectsEveryMatchWhilePageSizeLimitsTheRows(): void
    {
        self::initializeDatabaseWithEntities(
            self::createConnect('p1', 'paged.test', '10.0.1.1', 'p1@greyface.test'),
            self::createConnect('p2', 'paged.test', '10.0.1.2', 'p2@greyface.test'),
            self::createConnect('p3', 'paged.test', '10.0.1.3', 'p3@greyface.test'),
        );
        self::clearEntityManager();

        $result = $this->repository()->findAll(null, 'paged.test', '0', 2);

        self::assertSame(3, (int)$result['count'], 'count must ignore the page size');
        self::assertCount(2, $result['results']);
    }

    public function testCountFallsBackToTheRowCountWithoutPagination(): void
    {
        self::initializeDatabaseWithEntities(
            self::createConnect('u1', 'unpaged.test', '10.0.2.1', 'u1@greyface.test'),
            self::createConnect('u2', 'unpaged.test', '10.0.2.2', 'u2@greyface.test'),
        );
        self::clearEntityManager();

        $result = $this->repository()->findAll(null, 'unpaged.test');

        self::assertSame(2, $result['count']);
        self::assertCount(2, $result['results']);
    }

    public function testSortsByAMappedColumnInEitherDirection(): void
    {
        self::initializeDatabaseWithEntities(
            self::createConnect('sortb', 'sorted.test', '10.0.3.2', 'b@greyface.test'),
            self::createConnect('sorta', 'sorted.test', '10.0.3.1', 'a@greyface.test'),
        );
        self::clearEntityManager();

        $ascending = $this->repository()->findAll(null, 'sorted.test', null, 20, 'name');
        self::assertSame(['a@greyface.test', 'b@greyface.test'], self::recipientsOf($ascending));

        $descending = $this->repository()->findAll(null, 'sorted.test', null, 20, 'name', true);
        self::assertSame(['b@greyface.test', 'a@greyface.test'], self::recipientsOf($descending));
    }

    public function testFindsAnEntryByItsFullNaturalKey(): void
    {
        self::initializeDatabaseWithEntities(
            self::createConnect('ndsr', 'ndsr.test', '10.0.4.1', 'ndsr@greyface.test'),
        );
        self::clearEntityManager();

        $found = $this->repository()->findOneByNDSR('ndsr', 'ndsr.test', '10.0.4.1', 'ndsr@greyface.test');
        self::assertCount(1, $found);

        $missing = $this->repository()->findOneByNDSR('ndsr', 'ndsr.test', '10.0.4.1', 'wrong@greyface.test');
        self::assertCount(0, $missing);
    }

    /**
     * deleteByDate() is the only place using the MySQL-only DATE() DQL function
     * registered in doctrine.yaml.
     */
    public function testDeletesEveryEntryFirstSeenUpToADate(): void
    {
        self::initializeDatabaseWithEntities(
            self::createConnect('old', 'todelete.test', '10.0.5.1', 'old@greyface.test'),
        );
        self::clearEntityManager();

        $deleted = $this->repository()->deleteByDate((new \DateTimeImmutable('tomorrow'))->format('Y-m-d'));

        self::assertGreaterThanOrEqual(1, $deleted);
        self::assertSame([], self::recipientsOf($this->repository()->findAll(null, 'todelete.test')));
    }

    public function testKeepsEntriesNewerThanTheGivenDate(): void
    {
        self::initializeDatabaseWithEntities(
            self::createConnect('keep', 'tokeep.test', '10.0.6.1', 'keep@greyface.test'),
        );
        self::clearEntityManager();

        $this->repository()->deleteByDate('2000-01-01');

        self::assertContains(
            'keep@greyface.test',
            self::recipientsOf($this->repository()->findAll(null, 'tokeep.test'))
        );
    }
}

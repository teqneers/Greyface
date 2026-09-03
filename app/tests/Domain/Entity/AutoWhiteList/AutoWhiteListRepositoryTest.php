<?php

namespace App\Tests\Domain\Entity\AutoWhiteList;

use App\Domain\Entity\AutoWhiteList\DomainAutoWhiteList\DomainAutoWhiteListRepository;
use App\Domain\Entity\AutoWhiteList\EmailAutoWhiteList\EmailAutoWhiteListRepository;
use App\Test\AutoWhiteListTrait;
use App\Test\DatabaseTestTrait;
use DateTimeImmutable;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * The auto-whitelist tables are SQLGrey's own: composite primary keys and
 * `timestamp` columns mapped as datetime_immutable. Both are places where an ORM
 * upgrade tends to change behaviour, and neither had direct coverage.
 */
class AutoWhiteListRepositoryTest extends KernelTestCase
{
    use DatabaseTestTrait, AutoWhiteListTrait;

    private function domainRepository(): DomainAutoWhiteListRepository
    {
        return self::getContainer()->get(DomainAutoWhiteListRepository::class);
    }

    private function emailRepository(): EmailAutoWhiteListRepository
    {
        return self::getContainer()->get(EmailAutoWhiteListRepository::class);
    }

    /**
     * @return string[]
     */
    private static function domainsOf(iterable $entries): array
    {
        $domains = [];
        foreach ($entries as $entry) {
            $domains[] = $entry->getDomain();
        }

        return $domains;
    }

    public function testSearchesAcrossEveryColumn(): void
    {
        self::initializeDatabaseWithEntities(
            self::createAutoWhiteListDomain('needle.awl.test', '10.1.0.1'),
            self::createAutoWhiteListDomain('other.awl.test', '10.1.0.needle'),
            self::createAutoWhiteListDomain('nomatch.awl.test', '10.1.0.3'),
        );
        self::clearEntityManager();

        $domains = self::domainsOf($this->domainRepository()->findFiltered('needle'));

        self::assertContains('needle.awl.test', $domains);
        self::assertContains('other.awl.test', $domains);
        self::assertNotContains('nomatch.awl.test', $domains);
    }

    public function testDefaultsToSortingByDomain(): void
    {
        self::initializeDatabaseWithEntities(
            self::createAutoWhiteListDomain('sort-c.awl.test', '10.2.0.3'),
            self::createAutoWhiteListDomain('sort-a.awl.test', '10.2.0.1'),
            self::createAutoWhiteListDomain('sort-b.awl.test', '10.2.0.2'),
        );
        self::clearEntityManager();

        self::assertSame(
            ['sort-a.awl.test', 'sort-b.awl.test', 'sort-c.awl.test'],
            self::domainsOf($this->domainRepository()->findFiltered('sort-'))
        );
    }

    public function testSortsByEachMappedColumn(): void
    {
        self::initializeDatabaseWithEntities(
            self::createAutoWhiteListDomain('map-a.awl.test', '10.3.0.2'),
            self::createAutoWhiteListDomain('map-b.awl.test', '10.3.0.1'),
        );
        self::clearEntityManager();

        $bySource = self::domainsOf($this->domainRepository()->findFiltered('map-', null, 20, 'source'));
        self::assertSame(['map-b.awl.test', 'map-a.awl.test'], $bySource);

        $byDomainDesc = self::domainsOf($this->domainRepository()->findFiltered('map-', null, 20, 'domain', true));
        self::assertSame(['map-b.awl.test', 'map-a.awl.test'], $byDomainDesc);
    }

    public function testSortsByTheTimestampColumns(): void
    {
        self::initializeDatabaseWithEntities(
            self::createAutoWhiteListDomain('time-late.awl.test', '10.4.0.1'),
            self::createAutoWhiteListDomain('time-early.awl.test', '10.4.0.2'),
        );
        self::clearEntityManager();

        // Only asserts the queries build and run — the seeded timestamps are
        // written by the database default, so their order is not deterministic.
        self::assertCount(2, $this->domainRepository()->findFiltered('time-', null, 20, 'firstSeen'));
        self::assertCount(2, $this->domainRepository()->findFiltered('time-', null, 20, 'lastSeen', true));
    }

    public function testReturnsAPaginatorOnlyWhenAStartIsGiven(): void
    {
        self::initializeDatabaseWithEntities(self::createAutoWhiteListDomain('pager.awl.test', '10.5.0.1'));
        self::clearEntityManager();

        self::assertIsArray($this->domainRepository()->findFiltered('pager'));
        self::assertInstanceOf(Paginator::class, $this->domainRepository()->findFiltered('pager', '0'));
    }

    public function testPagesThroughResults(): void
    {
        self::initializeDatabaseWithEntities(
            self::createAutoWhiteListDomain('page-1.awl.test', '10.6.0.1'),
            self::createAutoWhiteListDomain('page-2.awl.test', '10.6.0.2'),
            self::createAutoWhiteListDomain('page-3.awl.test', '10.6.0.3'),
            self::createAutoWhiteListDomain('page-4.awl.test', '10.6.0.4'),
        );
        self::clearEntityManager();

        $firstPage = $this->domainRepository()->findFiltered('page-', '0', 2);
        self::assertCount(4, $firstPage);
        self::assertSame(['page-1.awl.test', 'page-2.awl.test'], self::domainsOf($firstPage));

        self::assertSame(
            ['page-3.awl.test', 'page-4.awl.test'],
            self::domainsOf($this->domainRepository()->findFiltered('page-', '1', 2))
        );
    }

    public function testRoundTripsAnEntryWithExplicitTimestamps(): void
    {
        $firstSeen = new DateTimeImmutable('2024-01-02 03:04:05');
        $lastSeen = new DateTimeImmutable('2024-02-03 04:05:06');

        self::initializeDatabaseWithEntities(
            self::createAutoWhiteListDomain('stamped.awl.test', '10.7.0.1')
                ->setFirstSeen($firstSeen)
                ->setLastSeen($lastSeen),
        );
        self::clearEntityManager();

        $entries = $this->domainRepository()->findFiltered('stamped.awl.test');
        self::assertCount(1, $entries);

        $entry = $entries[0];
        self::assertSame($firstSeen->format('Y-m-d H:i:s'), $entry->getFirstSeen()->format('Y-m-d H:i:s'));
        self::assertSame($lastSeen->format('Y-m-d H:i:s'), $entry->getLastSeen()->format('Y-m-d H:i:s'));
    }

    public function testDeletesAnEntry(): void
    {
        self::initializeDatabaseWithEntities(
            self::createAutoWhiteListDomain('doomed.awl.test', '10.8.0.1'),
        );
        self::clearEntityManager();

        self::initializeDatabase(function (): void {
            $repository = $this->domainRepository();
            $repository->delete($repository->findFiltered('doomed.awl.test')[0]);
        });
        self::clearEntityManager();

        self::assertCount(0, $this->domainRepository()->findFiltered('doomed.awl.test'));
    }

    public function testEmailAutoWhiteListSearchesAndSortsIndependently(): void
    {
        self::initializeDatabaseWithEntities(
            self::createAutoWhiteListEmail('sender-b', 'email-needle.awl.test', '10.9.0.2'),
            self::createAutoWhiteListEmail('sender-a', 'email-needle.awl.test', '10.9.0.1'),
            self::createAutoWhiteListEmail('sender-c', 'email-other.awl.test', '10.9.0.3'),
        );
        self::clearEntityManager();

        $matches = $this->emailRepository()->findFiltered('email-needle');
        self::assertCount(2, $matches);

        $names = array_map(static fn(object $entry): string => $entry->getName(), $matches);
        self::assertSame(['sender-a', 'sender-b'], $names, 'defaults to ordering by sender name');
    }

    public function testEmailAutoWhiteListPagesThroughResults(): void
    {
        self::initializeDatabaseWithEntities(
            self::createAutoWhiteListEmail('page-1', 'email-page.awl.test', '10.10.0.1'),
            self::createAutoWhiteListEmail('page-2', 'email-page.awl.test', '10.10.0.2'),
            self::createAutoWhiteListEmail('page-3', 'email-page.awl.test', '10.10.0.3'),
        );
        self::clearEntityManager();

        $page = $this->emailRepository()->findFiltered('email-page', '0', 2);

        self::assertInstanceOf(Paginator::class, $page);
        self::assertCount(3, $page);
        self::assertCount(2, iterator_to_array($page));
    }
}

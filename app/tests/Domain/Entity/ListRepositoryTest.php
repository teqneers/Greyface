<?php

namespace App\Tests\Domain\Entity;

use App\Domain\Entity\OptIn\OptInDomain\OptInDomainRepository;
use App\Domain\Entity\OptIn\OptInEmail\OptInEmailRepository;
use App\Domain\Entity\OptOut\OptOutDomain\OptOutDomainRepository;
use App\Domain\Entity\OptOut\OptOutEmail\OptOutEmailRepository;
use App\Test\DatabaseTestTrait;
use App\Test\OptInOptOutTrait;
use Doctrine\ORM\Tools\Pagination\Paginator;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * The four opt-in / opt-out repositories are the same class four times over:
 * a single-column table with a LIKE search, one sortable column and the same
 * page-index arithmetic. Covering them once, parameterised, keeps the DQL under
 * test without four near-identical files.
 */
class ListRepositoryTest extends KernelTestCase
{
    use DatabaseTestTrait, OptInOptOutTrait;

    /**
     * @return iterable<string, array{class-string, string, string, string}>
     */
    public static function repositories(): iterable
    {
        yield 'opt-in domain' => [
            OptInDomainRepository::class, 'createOptInDomain', 'getDomain', 'domain',
        ];
        yield 'opt-in email' => [
            OptInEmailRepository::class, 'createOptInEmail', 'getEmail', 'email',
        ];
        yield 'opt-out domain' => [
            OptOutDomainRepository::class, 'createOptOutDomain', 'getDomain', 'domain',
        ];
        yield 'opt-out email' => [
            OptOutEmailRepository::class, 'createOptOutEmail', 'getEmail', 'email',
        ];
    }

    /**
     * The four entities use different value prefixes, so build values that sort
     * predictably and cannot collide with another case's rows.
     */
    private static function value(string $sortField, string $suffix): string
    {
        return $sortField === 'email'
            ? sprintf('%s@listrepo.test', $suffix)
            : sprintf('%s.listrepo.test', $suffix);
    }

    /**
     * @return string[]
     */
    private static function valuesOf(iterable $entities, string $getter): array
    {
        $values = [];
        foreach ($entities as $entity) {
            $values[] = $entity->$getter();
        }

        return $values;
    }

    #[DataProvider('repositories')]
    public function testFindsAnEntryById(string $repositoryClass, string $factory, string $getter, string $sortField): void
    {
        $entity = self::$factory(self::value($sortField, 'byid'));
        self::initializeDatabaseWithEntities($entity);
        self::clearEntityManager();

        $repository = self::getContainer()->get($repositoryClass);
        $found = $repository->findById(self::value($sortField, 'byid'));

        self::assertNotNull($found);
        self::assertSame(self::value($sortField, 'byid'), $found->$getter());
    }

    #[DataProvider('repositories')]
    public function testReturnsNullForAnUnknownId(string $repositoryClass): void
    {
        self::assertNull(self::getContainer()->get($repositoryClass)->findById('does-not-exist'));
    }

    #[DataProvider('repositories')]
    public function testFiltersWithALikeSearch(string $repositoryClass, string $factory, string $getter, string $sortField): void
    {
        self::initializeDatabaseWithEntities(
            self::$factory(self::value($sortField, 'needle-one')),
            self::$factory(self::value($sortField, 'needle-two')),
            self::$factory(self::value($sortField, 'unrelated')),
        );
        self::clearEntityManager();

        $repository = self::getContainer()->get($repositoryClass);
        $values = self::valuesOf($repository->findAll('needle-'), $getter);

        self::assertCount(2, $values);
        self::assertContains(self::value($sortField, 'needle-one'), $values);
        self::assertNotContains(self::value($sortField, 'unrelated'), $values);
    }

    #[DataProvider('repositories')]
    public function testDefaultsToAscendingOrder(string $repositoryClass, string $factory, string $getter, string $sortField): void
    {
        self::initializeDatabaseWithEntities(
            self::$factory(self::value($sortField, 'sort-c')),
            self::$factory(self::value($sortField, 'sort-a')),
            self::$factory(self::value($sortField, 'sort-b')),
        );
        self::clearEntityManager();

        $repository = self::getContainer()->get($repositoryClass);

        self::assertSame(
            [
                self::value($sortField, 'sort-a'),
                self::value($sortField, 'sort-b'),
                self::value($sortField, 'sort-c'),
            ],
            self::valuesOf($repository->findAll('sort-'), $getter)
        );
    }

    #[DataProvider('repositories')]
    public function testSortsDescendingOnRequest(string $repositoryClass, string $factory, string $getter, string $sortField): void
    {
        self::initializeDatabaseWithEntities(
            self::$factory(self::value($sortField, 'desc-a')),
            self::$factory(self::value($sortField, 'desc-b')),
        );
        self::clearEntityManager();

        $repository = self::getContainer()->get($repositoryClass);

        self::assertSame(
            [self::value($sortField, 'desc-b'), self::value($sortField, 'desc-a')],
            self::valuesOf($repository->findAll('desc-', null, 20, $sortField, true), $getter)
        );
    }

    #[DataProvider('repositories')]
    public function testReturnsAPaginatorOnlyWhenAStartIsGiven(string $repositoryClass, string $factory, string $getter, string $sortField): void
    {
        self::initializeDatabaseWithEntities(self::$factory(self::value($sortField, 'pager')));
        self::clearEntityManager();

        $repository = self::getContainer()->get($repositoryClass);

        self::assertIsArray($repository->findAll('pager'));
        self::assertInstanceOf(Paginator::class, $repository->findAll('pager', '0'));
    }

    /**
     * Same page-index-not-offset arithmetic as everywhere else in the codebase.
     */
    #[DataProvider('repositories')]
    public function testPagesThroughResults(string $repositoryClass, string $factory, string $getter, string $sortField): void
    {
        self::initializeDatabaseWithEntities(
            self::$factory(self::value($sortField, 'page-1')),
            self::$factory(self::value($sortField, 'page-2')),
            self::$factory(self::value($sortField, 'page-3')),
            self::$factory(self::value($sortField, 'page-4')),
        );
        self::clearEntityManager();

        $repository = self::getContainer()->get($repositoryClass);

        $firstPage = $repository->findAll('page-', '0', 2);
        self::assertCount(4, $firstPage, 'the paginator counts every match');
        self::assertSame(
            [self::value($sortField, 'page-1'), self::value($sortField, 'page-2')],
            self::valuesOf($firstPage, $getter)
        );

        self::assertSame(
            [self::value($sortField, 'page-3'), self::value($sortField, 'page-4')],
            self::valuesOf($repository->findAll('page-', '1', 2), $getter)
        );
    }

    #[DataProvider('repositories')]
    public function testSavesAndDeletesAnEntry(string $repositoryClass, string $factory, string $getter, string $sortField): void
    {
        $repository = self::getContainer()->get($repositoryClass);
        $value = self::value($sortField, 'roundtrip');

        self::initializeDatabase(static function () use ($repository, $factory, $value): void {
            $repository->save(self::$factory($value));
        });
        self::clearEntityManager();

        self::assertNotNull($repository->findById($value));

        self::initializeDatabase(static function () use ($repository, $value): void {
            $repository->delete($repository->findById($value));
        });
        self::clearEntityManager();

        self::assertNull($repository->findById($value));
    }
}

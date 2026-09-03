<?php

namespace App\Tests\Domain\Entity\User;

use App\Domain\Entity\User\User;
use App\Domain\Entity\User\UserRepository;
use App\Test\DatabaseTestTrait;
use App\Test\UserDomainTrait;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Repository tests run against a real database on purpose: DQL, the Paginator
 * and the soft-delete filters are exactly what breaks on a Doctrine major
 * upgrade, and none of it was covered.
 *
 * Migration Version20220718125037 seeds an `admin` user, so assertions here look
 * for their own rows rather than counting everything.
 */
class UserRepositoryTest extends KernelTestCase
{
    use DatabaseTestTrait, UserDomainTrait;

    private function repository(): UserRepository
    {
        return self::getContainer()->get(UserRepository::class);
    }

    /**
     * @param User[] $users
     * @return string[]
     */
    private static function usernamesOf(iterable $users): array
    {
        $names = [];
        foreach ($users as $user) {
            $names[] = $user->getUsername();
        }

        return $names;
    }

    public function testFindsAnActiveUserById(): void
    {
        $user = self::createUser('findme', 'findme@greyface.test');
        self::initializeDatabaseWithEntities($user);
        self::clearEntityManager();

        self::assertSame('findme', $this->repository()->findById($user->getId())->getUsername());
    }

    public function testHidesDeletedUsersByIdUnlessAskedFor(): void
    {
        $user = self::createUser('gone', 'gone@greyface.test')->delete();
        self::initializeDatabaseWithEntities($user);
        self::clearEntityManager();

        self::assertNull($this->repository()->findById($user->getId()));
        self::assertNotNull($this->repository()->findById($user->getId(), true));
    }

    public function testReturnsNullForAnUnknownId(): void
    {
        self::assertNull($this->repository()->findById('00000000-0000-4000-8000-000000000000'));
    }

    public function testFindsAnActiveUserByUsername(): void
    {
        $user = self::createUser('byname', 'byname@greyface.test');
        self::initializeDatabaseWithEntities($user);
        self::clearEntityManager();

        self::assertSame($user->getId(), $this->repository()->findByUsername('byname')->getId());
    }

    public function testHidesDeletedUsersByUsernameUnlessAskedFor(): void
    {
        $user = self::createUser('goneagain', 'goneagain@greyface.test')->delete();
        self::initializeDatabaseWithEntities($user);
        self::clearEntityManager();

        self::assertNull($this->repository()->findByUsername('goneagain'));
        self::assertNotNull($this->repository()->findByUsername('goneagain', true));
    }

    public function testReturnsNullForAnUnknownUsername(): void
    {
        self::assertNull($this->repository()->findByUsername('nobody-at-all'));
    }

    public function testExcludesDeletedUsersFromTheListing(): void
    {
        $active = self::createUser('zactive', 'zactive@greyface.test');
        $deleted = self::createUser('zdeleted', 'zdeleted@greyface.test')->delete();
        self::initializeDatabaseWithEntities($active, $deleted);
        self::clearEntityManager();

        $names = self::usernamesOf($this->repository()->findFiltered());
        self::assertContains('zactive', $names);
        self::assertNotContains('zdeleted', $names);

        $namesWithDeleted = self::usernamesOf($this->repository()->findFiltered(true));
        self::assertContains('zdeleted', $namesWithDeleted);
    }

    public function testSearchesAcrossUsernameAndEmail(): void
    {
        $byName = self::createUser('needle-user', 'unrelated@greyface.test');
        $byEmail = self::createUser('unrelated-user', 'needle@greyface.test');
        $other = self::createUser('nothing', 'nothing@greyface.test');
        self::initializeDatabaseWithEntities($byName, $byEmail, $other);
        self::clearEntityManager();

        $names = self::usernamesOf($this->repository()->findFiltered(false, 'needle'));

        self::assertContains('needle-user', $names);
        self::assertContains('unrelated-user', $names);
        self::assertNotContains('nothing', $names);
    }

    public function testDefaultsToSortingByUsernameAscending(): void
    {
        self::initializeDatabaseWithEntities(
            self::createUser('sort-c', 'c@greyface.test'),
            self::createUser('sort-a', 'a@greyface.test'),
            self::createUser('sort-b', 'b@greyface.test'),
        );
        self::clearEntityManager();

        $names = array_values(array_filter(
            self::usernamesOf($this->repository()->findFiltered(false, 'sort-')),
            static fn(string $name): bool => str_starts_with($name, 'sort-')
        ));

        self::assertSame(['sort-a', 'sort-b', 'sort-c'], $names);
    }

    public function testSortsByAMappedColumnInEitherDirection(): void
    {
        self::initializeDatabaseWithEntities(
            self::createUser('order-x', 'zzz@greyface.test'),
            self::createUser('order-y', 'aaa@greyface.test'),
        );
        self::clearEntityManager();

        $ascending = self::usernamesOf($this->repository()->findFiltered(false, 'order-', null, 20, 'email'));
        self::assertSame(['order-y', 'order-x'], $ascending);

        $descending = self::usernamesOf($this->repository()->findFiltered(false, 'order-', null, 20, 'email', true));
        self::assertSame(['order-x', 'order-y'], $descending);
    }

    public function testReturnsAPaginatorOnlyWhenAStartIsGiven(): void
    {
        self::initializeDatabaseWithEntities(self::createUser('page-a', 'page-a@greyface.test'));
        self::clearEntityManager();

        self::assertIsArray($this->repository()->findFiltered(false, 'page-'));
        self::assertInstanceOf(Paginator::class, $this->repository()->findFiltered(false, 'page-', '0'));
    }

    /**
     * Pins the surprising offset arithmetic in findFiltered(): `start` is treated as a
     * page index (start * max), except when it is 0, where it is used as a raw
     * offset. Documented here so the ORM upgrade cannot change it unnoticed.
     */
    public function testStartIsAPageIndexRatherThanAnOffset(): void
    {
        self::initializeDatabaseWithEntities(
            self::createUser('page-1', 'p1@greyface.test'),
            self::createUser('page-2', 'p2@greyface.test'),
            self::createUser('page-3', 'p3@greyface.test'),
            self::createUser('page-4', 'p4@greyface.test'),
        );
        self::clearEntityManager();

        $firstPage = self::usernamesOf($this->repository()->findFiltered(false, 'page-', '0', 2));
        self::assertSame(['page-1', 'page-2'], $firstPage);

        // start=1 with max=2 skips 1 * 2 = 2 rows, i.e. the second page.
        $secondPage = self::usernamesOf($this->repository()->findFiltered(false, 'page-', '1', 2));
        self::assertSame(['page-3', 'page-4'], $secondPage);
    }

    public function testPaginatorReportsTheTotalNumberOfMatches(): void
    {
        self::initializeDatabaseWithEntities(
            self::createUser('count-1', 'c1@greyface.test'),
            self::createUser('count-2', 'c2@greyface.test'),
            self::createUser('count-3', 'c3@greyface.test'),
        );
        self::clearEntityManager();

        $paginator = $this->repository()->findFiltered(false, 'count-', '0', 2);

        self::assertCount(3, $paginator);
        self::assertCount(2, iterator_to_array($paginator));
    }

    public function testCountsOnlyActiveAdministrators(): void
    {
        $before = $this->repository()->countAdministrators();

        self::initializeDatabaseWithEntities(
            self::createAdmin('extra-admin', 'extra-admin@greyface.test'),
            self::createAdmin('deleted-admin', 'deleted-admin@greyface.test')->delete(),
            self::createUser('plain-user', 'plain-user@greyface.test'),
        );
        self::clearEntityManager();

        self::assertSame($before + 1, $this->repository()->countAdministrators());
    }

    public function testDeletesAUserForGood(): void
    {
        $user = self::createUser('doomed', 'doomed@greyface.test');
        self::initializeDatabaseWithEntities($user);
        self::clearEntityManager();

        self::initializeDatabase(function () use ($user): void {
            $this->repository()->delete($this->repository()->findById($user->getId()));
        });
        self::clearEntityManager();

        self::assertNull($this->repository()->findById($user->getId(), true));
    }
}

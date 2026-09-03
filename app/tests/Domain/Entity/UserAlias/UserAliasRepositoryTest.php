<?php

namespace App\Tests\Domain\Entity\UserAlias;

use App\Domain\Entity\User\User as DomainUser;
use App\Domain\Entity\UserAlias\UserAlias;
use App\Domain\Entity\UserAlias\UserAliasRepository;
use App\Test\DatabaseTestTrait;
use App\Test\UserAliasTrait;
use App\Test\UserDomainTrait;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UserAliasRepositoryTest extends KernelTestCase
{
    use DatabaseTestTrait, UserDomainTrait, UserAliasTrait;

    private function repository(): UserAliasRepository
    {
        return self::getContainer()->get(UserAliasRepository::class);
    }

    /**
     * @return string[]
     */
    private static function aliasNamesOf(iterable $aliases): array
    {
        $names = [];
        foreach ($aliases as $alias) {
            $names[] = $alias->getAliasName();
        }

        return $names;
    }

    public function testFindsAnAliasById(): void
    {
        $user = self::createUser('owner', 'owner@greyface.test');
        $alias = self::createUserAlias($user, 'find@greyface.test');
        self::initializeDatabaseWithEntities($user, $alias);
        self::clearEntityManager();

        self::assertSame('find@greyface.test', $this->repository()->findById($alias->getId())->getAliasName());
    }

    public function testReturnsNullForAnUnknownId(): void
    {
        self::assertNull($this->repository()->findById('00000000-0000-4000-8000-000000000000'));
    }

    public function testFindsAnAliasByNameForItsOwner(): void
    {
        $owner = self::createUser('owner', 'owner@greyface.test');
        $stranger = self::createUser('stranger', 'stranger@greyface.test');
        self::initializeDatabaseWithEntities(
            $owner,
            $stranger,
            self::createUserAlias($owner, 'scoped@greyface.test'),
        );
        self::clearEntityManager();

        self::assertNotNull($this->repository()->findByAliasNameForUser($owner, 'scoped@greyface.test'));
        self::assertNull(
            $this->repository()->findByAliasNameForUser($stranger, 'scoped@greyface.test'),
            'an alias must not be findable through another user'
        );
        self::assertNull($this->repository()->findByAliasNameForUser($owner, 'nope@greyface.test'));
    }

    public function testRestrictsTheListingToOneUser(): void
    {
        $owner = self::createUser('owner', 'owner@greyface.test');
        $stranger = self::createUser('stranger', 'stranger@greyface.test');
        self::initializeDatabaseWithEntities(
            $owner,
            $stranger,
            self::createUserAlias($owner, 'mine@greyface.test'),
            self::createUserAlias($stranger, 'theirs@greyface.test'),
        );
        self::clearEntityManager();

        $names = self::aliasNamesOf($this->repository()->findAll($owner));

        self::assertSame(['mine@greyface.test'], $names);
    }

    public function testSearchesAcrossUsernameAndAliasName(): void
    {
        $needleUser = self::createUser('needleuser', 'nu@greyface.test');
        $other = self::createUser('plain', 'plain@greyface.test');
        self::initializeDatabaseWithEntities(
            $needleUser,
            $other,
            self::createUserAlias($needleUser, 'byuser@greyface.test'),
            self::createUserAlias($other, 'needle@greyface.test'),
            self::createUserAlias($other, 'unrelated@greyface.test'),
        );
        self::clearEntityManager();

        $names = self::aliasNamesOf($this->repository()->findAll(null, 'needle'));

        self::assertContains('byuser@greyface.test', $names);
        self::assertContains('needle@greyface.test', $names);
        self::assertNotContains('unrelated@greyface.test', $names);
    }

    public function testDefaultsToSortingByAliasName(): void
    {
        $user = self::createUser('sorter', 'sorter@greyface.test');
        self::initializeDatabaseWithEntities(
            $user,
            self::createUserAlias($user, 'sort-c@greyface.test'),
            self::createUserAlias($user, 'sort-a@greyface.test'),
            self::createUserAlias($user, 'sort-b@greyface.test'),
        );
        self::clearEntityManager();

        self::assertSame(
            ['sort-a@greyface.test', 'sort-b@greyface.test', 'sort-c@greyface.test'],
            self::aliasNamesOf($this->repository()->findAll($user))
        );
    }

    public function testSortsByAMappedColumnInEitherDirection(): void
    {
        $user = self::createUser('sorter', 'sorter@greyface.test');
        self::initializeDatabaseWithEntities(
            $user,
            self::createUserAlias($user, 'order-a@greyface.test'),
            self::createUserAlias($user, 'order-b@greyface.test'),
        );
        self::clearEntityManager();

        $descending = $this->repository()->findAll($user, null, null, 20, 'aliasName', true);

        self::assertSame(
            ['order-b@greyface.test', 'order-a@greyface.test'],
            self::aliasNamesOf($descending)
        );
    }

    public function testReturnsAPaginatorOnlyWhenAStartIsGiven(): void
    {
        $user = self::createUser('pager', 'pager@greyface.test');
        self::initializeDatabaseWithEntities($user, self::createUserAlias($user, 'page@greyface.test'));
        self::clearEntityManager();

        self::assertIsArray($this->repository()->findAll($user));
        self::assertInstanceOf(Paginator::class, $this->repository()->findAll($user, null, '0'));
    }

    public function testPagesThroughResults(): void
    {
        $user = self::createUser('pager', 'pager@greyface.test');
        self::initializeDatabaseWithEntities(
            $user,
            self::createUserAlias($user, 'p1@greyface.test'),
            self::createUserAlias($user, 'p2@greyface.test'),
            self::createUserAlias($user, 'p3@greyface.test'),
            self::createUserAlias($user, 'p4@greyface.test'),
        );
        self::clearEntityManager();

        $firstPage = $this->repository()->findAll($user, null, '0', 2);
        self::assertCount(4, $firstPage, 'the paginator counts every match');
        self::assertSame(['p1@greyface.test', 'p2@greyface.test'], self::aliasNamesOf($firstPage));

        $secondPage = $this->repository()->findAll($user, null, '1', 2);
        self::assertSame(['p3@greyface.test', 'p4@greyface.test'], self::aliasNamesOf($secondPage));
    }

    /**
     * The batch saver flushes and detaches every `batchSize` aliases; it is used
     * when a user submits several aliases in one request.
     *
     * Note the sharp edge it forces on callers: because it calls
     * EntityManager::clear(), every entity held across an iteration — including
     * the owning User — is detached, so a caller must re-fetch rather than reuse
     * a reference from before the flush.
     */
    public function testBatchSaverSignalsEveryTimeItFlushes(): void
    {
        $user = self::createUser('batch', 'batch@greyface.test');
        self::initializeDatabaseWithEntities($user);
        self::clearEntityManager();

        $flushes = [];
        self::initializeDatabase(function ($container, EntityManagerInterface $em) use (&$flushes): void {
            $save = $this->repository()->createBatchSaver(2);
            foreach (['b1', 'b2', 'b3', 'b4'] as $name) {
                // Re-fetched every time: the previous iteration may have cleared
                // the entity manager and detached the owner.
                $owner = $em->getRepository(DomainUser::class)->findOneBy(['username' => 'batch']);
                $flushes[] = $save(self::createUserAlias($owner, $name . '@greyface.test'));
            }
        });
        self::clearEntityManager();

        self::assertSame([false, true, false, true], $flushes);
    }

    public function testDeletesAnAlias(): void
    {
        $user = self::createUser('owner', 'owner@greyface.test');
        $alias = self::createUserAlias($user, 'doomed@greyface.test');
        self::initializeDatabaseWithEntities($user, $alias);
        self::clearEntityManager();

        self::initializeDatabase(function () use ($alias): void {
            $this->repository()->delete($this->repository()->findById($alias->getId()));
        });
        self::clearEntityManager();

        self::assertNull($this->repository()->findById($alias->getId()));
    }
}

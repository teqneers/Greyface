<?php

namespace App\Tests\Domain;

use App\Domain\AutoWhiteList\DomainAutoWhiteList\DomainAutoWhiteListFinder;
use App\Domain\Entity\AutoWhiteList\DomainAutoWhiteList\DomainAutoWhiteListRepository;
use App\Domain\Entity\OptIn\OptInDomain\OptInDomainRepository;
use App\Domain\Entity\OptIn\OptInEmail\OptInEmailRepository;
use App\Domain\Entity\OptOut\OptOutDomain\OptOutDomainRepository;
use App\Domain\Entity\OptOut\OptOutEmail\OptOutEmailRepository;
use App\Domain\Entity\User\UserRepository;
use App\Domain\Entity\UserAlias\UserAliasRepository;
use App\Domain\OptIn\OptInDomain\OptInDomainFinder;
use App\Domain\OptIn\OptInEmail\OptInEmailFinder;
use App\Domain\OptOut\OptOutDomain\OptOutDomainFinder;
use App\Domain\OptOut\OptOutEmail\OptOutEmailFinder;
use App\Domain\User\UserFinder;
use App\Domain\UserAlias\UserAliasFinder;
use App\Test\AutoWhiteListTrait;
use App\Test\OptInOptOutTrait;
use App\Test\UserAliasTrait;
use App\Test\UserDomainTrait;
use OutOfBoundsException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Every finder trait wraps its repository's findById() and converts a miss into
 * an OutOfBoundsException, which the controllers rely on to produce a 404. The
 * throwing branch had no coverage in any of the seven.
 */
class FinderTest extends TestCase
{
    use UserDomainTrait, UserAliasTrait, AutoWhiteListTrait, OptInOptOutTrait;

    /**
     * Each case exposes its trait's protected finder as a public closure so the
     * trait can be exercised without a container.
     *
     * @return iterable<string, array{class-string, callable, string, callable}>
     */
    public static function finders(): iterable
    {
        yield 'user' => [
            UserRepository::class,
            static fn(object $repository): object => new class ($repository) {
                use UserFinder;

                public function __construct(UserRepository $userRepository)
                {
                    $this->userRepository = $userRepository;
                }

                public function find(string $id): object
                {
                    return $this->getUserById($id);
                }
            },
            'No user found for id missing-id',
            static fn(): object => self::createUser(),
        ];

        yield 'user alias' => [
            UserAliasRepository::class,
            static fn(object $repository): object => new class ($repository) {
                use UserAliasFinder;

                public function __construct(UserAliasRepository $userAliasRepository)
                {
                    $this->userAliasRepository = $userAliasRepository;
                }

                public function find(string $id): object
                {
                    return $this->getUserAliasById($id);
                }
            },
            'No user alias found for id missing-id',
            static fn(): object => self::createUserAlias(),
        ];

        yield 'auto-whitelist domain' => [
            DomainAutoWhiteListRepository::class,
            static fn(object $repository): object => new class ($repository) {
                use DomainAutoWhiteListFinder;

                public function __construct(DomainAutoWhiteListRepository $domainAutoWhiteListRepository)
                {
                    $this->domainAutoWhiteListRepository = $domainAutoWhiteListRepository;
                }

                public function find(string $id): object
                {
                    return $this->getDomainAutoWhiteListById($id);
                }
            },
            'No Domain Auto WhiteList found for id missing-id',
            static fn(): object => self::createAutoWhiteListDomain(),
        ];

        yield 'opt-in domain' => [
            OptInDomainRepository::class,
            static fn(object $repository): object => new class ($repository) {
                use OptInDomainFinder;

                public function __construct(OptInDomainRepository $optInDomainRepository)
                {
                    $this->optInDomainRepository = $optInDomainRepository;
                }

                public function find(string $id): object
                {
                    return $this->getOptInDomainById($id);
                }
            },
            'No OptIn Domain found for id missing-id',
            static fn(): object => self::createOptInDomain(),
        ];

        yield 'opt-in email' => [
            OptInEmailRepository::class,
            static fn(object $repository): object => new class ($repository) {
                use OptInEmailFinder;

                public function __construct(OptInEmailRepository $optInEmailRepository)
                {
                    $this->optInEmailRepository = $optInEmailRepository;
                }

                public function find(string $id): object
                {
                    return $this->getOptInEmailById($id);
                }
            },
            'No OptIn Email found for id missing-id',
            static fn(): object => self::createOptInEmail(),
        ];

        yield 'opt-out domain' => [
            OptOutDomainRepository::class,
            static fn(object $repository): object => new class ($repository) {
                use OptOutDomainFinder;

                public function __construct(OptOutDomainRepository $optOutDomainRepository)
                {
                    $this->optOutDomainRepository = $optOutDomainRepository;
                }

                public function find(string $id): object
                {
                    return $this->getOptOutDomainById($id);
                }
            },
            'No OptOut Domain found for id missing-id',
            static fn(): object => self::createOptOutDomain(),
        ];

        yield 'opt-out email' => [
            OptOutEmailRepository::class,
            static fn(object $repository): object => new class ($repository) {
                use OptOutEmailFinder;

                public function __construct(OptOutEmailRepository $optOutEmailRepository)
                {
                    $this->optOutEmailRepository = $optOutEmailRepository;
                }

                public function find(string $id): object
                {
                    return $this->getOptOutEmailById($id);
                }
            },
            // Copy/paste in the source: the opt-out email finder reports
            // "OptOut Domain". Pinned as-is rather than silently corrected.
            'No OptOut Domain found for id missing-id',
            static fn(): object => self::createOptOutEmail(),
        ];
    }

    #[DataProvider('finders')]
    public function testReturnsTheEntityWhenItExists(
        string $repositoryClass,
        callable $factory,
        string $expectedMessage,
        callable $entityFactory,
    ): void {
        $entity = $entityFactory();
        $repository = $this->createMock($repositoryClass);
        $repository->method('findById')->willReturn($entity);

        self::assertSame($entity, $factory($repository)->find('an-id'));
    }

    #[DataProvider('finders')]
    public function testThrowsOutOfBoundsWhenTheEntityIsMissing(
        string $repositoryClass,
        callable $factory,
        string $expectedMessage,
    ): void {
        $repository = $this->createMock($repositoryClass);
        $repository->method('findById')->willReturn(null);

        $this->expectException(OutOfBoundsException::class);
        $this->expectExceptionMessage($expectedMessage);

        $factory($repository)->find('missing-id');
    }
}

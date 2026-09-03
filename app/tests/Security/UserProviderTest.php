<?php

namespace App\Tests\Security;

use App\Domain\Entity\User\User as DomainUser;
use App\Domain\Entity\User\UserRepository;
use App\Security\User;
use App\Security\UserProvider;
use App\Test\UserDomainTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\InMemoryUser;

class UserProviderTest extends TestCase
{
    use UserDomainTrait;

    private UserRepository&MockObject $userRepository;

    private UserProvider $provider;

    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepository::class);
        $this->provider = new UserProvider($this->userRepository);
    }

    public function testLoadsAUserByIdentifier(): void
    {
        $domainUser = self::createUser('admin', 'admin@greyface.test');
        $this->userRepository->method('findByUsername')->with('admin')->willReturn($domainUser);

        $user = $this->provider->loadUserByIdentifier('admin');

        self::assertInstanceOf(User::class, $user);
        self::assertSame('admin', $user->getUserIdentifier());
        self::assertSame('admin@greyface.test', $user->getEmail());
    }

    public function testLoadUserByUsernameIsAnAliasForLoadUserByIdentifier(): void
    {
        $this->userRepository->method('findByUsername')->willReturn(self::createUser('admin'));

        self::assertSame('admin', $this->provider->loadUserByUsername('admin')->getUserIdentifier());
    }

    public function testThrowsWhenTheUserDoesNotExist(): void
    {
        $this->userRepository->method('findByUsername')->willReturn(null);

        $this->expectException(UserNotFoundException::class);
        $this->expectExceptionMessage('Username "ghost" does not exist.');
        $this->provider->loadUserByIdentifier('ghost');
    }

    public function testRefreshesAUserFromTheRepository(): void
    {
        $stale = User::fromUser(self::createUser('admin', 'old@greyface.test'));
        $this->userRepository->method('findByUsername')
                             ->with('admin')
                             ->willReturn(self::createUser('admin', 'new@greyface.test'));

        self::assertSame('new@greyface.test', $this->provider->refreshUser($stale)->getEmail());
    }

    public function testRefusesToRefreshAForeignUserClass(): void
    {
        $this->expectException(UnsupportedUserException::class);
        $this->provider->refreshUser(new InMemoryUser('someone', 'password'));
    }

    public function testSupportsOnlyItsOwnUserClass(): void
    {
        self::assertTrue($this->provider->supportsClass(User::class));
        self::assertFalse($this->provider->supportsClass(DomainUser::class));
        self::assertFalse($this->provider->supportsClass(InMemoryUser::class));
    }

    public function testUpgradesAStoredPasswordHash(): void
    {
        $domainUser = self::createUser('admin');
        $this->userRepository->method('findById')->willReturn($domainUser);
        $this->userRepository->expects(self::once())->method('save')->with($domainUser);

        $this->provider->upgradePassword(User::fromUser($domainUser), 'a-new-hash');

        self::assertSame('a-new-hash', $domainUser->getPassword());
    }

    public function testIgnoresPasswordUpgradesForForeignUserClasses(): void
    {
        $this->userRepository->expects(self::never())->method('findById');
        $this->userRepository->expects(self::never())->method('save');

        $this->provider->upgradePassword(new InMemoryUser('someone', 'password'), 'a-new-hash');
    }

    public function testIgnoresPasswordUpgradesWhenTheUserHasSinceDisappeared(): void
    {
        $this->userRepository->method('findById')->willReturn(null);
        $this->userRepository->expects(self::never())->method('save');

        $this->provider->upgradePassword(User::fromUser(self::createUser()), 'a-new-hash');
    }

    /**
     * A failed rehash must never break the request it happened during — the user
     * is already authenticated by the time this runs.
     */
    public function testSwallowsFailuresWhileUpgradingAPassword(): void
    {
        $this->userRepository->method('findById')->willReturn(self::createUser());
        $this->userRepository->method('save')->willThrowException(new RuntimeException('write failed'));

        $this->provider->upgradePassword(User::fromUser(self::createUser()), 'a-new-hash');

        $this->expectNotToPerformAssertions();
    }
}

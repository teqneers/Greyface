<?php

namespace App\Tests\Security;

use App\Security\UserChecker;
use App\Test\SecurityUserTrait;
use App\Test\UserDomainTrait;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserInterface;

class UserCheckerTest extends TestCase
{
    use UserDomainTrait, SecurityUserTrait;

    private static function createArbitraryUser(): UserInterface
    {
        return new class implements UserInterface {
            public function getRoles(): array
            {
                return ['ROLE_USER'];
            }

            public function getUserIdentifier(): string
            {
                return 'user';
            }

            public function eraseCredentials(): void
            {
            }
        };
    }

    #[DoesNotPerformAssertions]
    public function testSkipsNonAppUsersOnPreAuth(): void
    {
        $checker = new UserChecker();
        $checker->checkPreAuth(self::createArbitraryUser());
    }

    #[DoesNotPerformAssertions]
    public function testAcceptsAppUsersOnPreAuth(): void
    {
        $checker = new UserChecker();
        $checker->checkPreAuth(self::createSecurityUser(self::createUser()));
    }

    #[DoesNotPerformAssertions]
    public function testAcceptsAppUsersOnPostAuth(): void
    {
        $checker = new UserChecker();
        $checker->checkPostAuth(self::createSecurityUser(self::createUser()));
    }

    public function testThrowsExceptionOnNonAppUsersOnPostAuth(): void
    {
        $checker = new UserChecker();

        $this->expectException(UnsupportedUserException::class);
        $this->expectExceptionMessage('User account is not supported.');
        $checker->checkPostAuth(self::createArbitraryUser());
    }
}

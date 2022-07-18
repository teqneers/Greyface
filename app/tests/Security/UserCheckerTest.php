<?php

namespace App\Tests\Security;

use App\Security\UserChecker;
use App\Test\SecurityUserTrait;
use App\Test\UserDomainTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Exception\DisabledException;
use Symfony\Component\Security\Core\Exception\LockedException;
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

    /**
     * @doesNotPerformAssertions
     */
    public function testSkipsNonAppUsersOnPreAuth(): void
    {
        $checker = new UserChecker();
        $checker->checkPreAuth(self::createArbitraryUser());
    }

    public function testThrowsExceptionOnNonAppUsersOnPostAuth(): void
    {
        $checker = new UserChecker();

        $this->expectException(UnsupportedUserException::class);
        $this->expectExceptionMessage('User account is not supported.');
        $checker->checkPostAuth(self::createArbitraryUser());
    }
}

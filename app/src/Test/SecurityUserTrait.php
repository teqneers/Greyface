<?php

namespace App\Test;

use App\Domain\Entity\User\User;
use App\Security\User as SecurityUser;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\TestBrowserToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Webmozart\Assert\Assert;

trait SecurityUserTrait
{
    public static function createSecurityUser(User $user): SecurityUser
    {
        return SecurityUser::fromUser($user);
    }

    public function createTokenStorageMock(User|SecurityUser|null $user): TokenStorageInterface
    {
        /** @var TokenStorageInterface|MockObject $tokenStorage */
        $tokenStorage = $this->createMock(TokenStorageInterface::class);
        if ($user) {
            $tokenStorage->method('getToken')
                         ->willReturn(self::createTokenForUser($user));
        }
        return $tokenStorage;
    }

    public static function createTokenForUser(User|SecurityUser $user): TokenInterface
    {
        if ($user instanceof User) {
            $user = self::createSecurityUser($user);
        }
        Assert::isInstanceOf($user, SecurityUser::class);
        return new TestBrowserToken($user->getRoles(), $user, 'main');
    }
}

<?php

namespace App\Security\Voter;

use App\Domain\User\UserInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter as BaseVoter;

abstract class UserVoter extends BaseVoter
{
    protected function ensureUser(TokenInterface $token): ?UserInterface
    {
        $tokenUser = $token->getUser();
        if (!$tokenUser instanceof UserInterface) {
            return null;
        }
        return $tokenUser;
    }

    protected function ensureAdmin(TokenInterface $token): ?UserInterface
    {
        $user = $this->ensureUser($token);
        if ($user !== null && $user->isAdministrator()) {
            return $user;
        }
        return null;
    }
}

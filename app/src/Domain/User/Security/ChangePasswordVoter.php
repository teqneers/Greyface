<?php

namespace App\Domain\User\Security;

use App\Security\Voter\UserVoter as BaseUserVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class ChangePasswordVoter extends BaseUserVoter
{
    public function supportsAttribute(string $attribute): bool
    {
        return $attribute === 'CHANGE_MY_PASSWORD';
    }

    public function supportsType(string $subjectType): bool
    {
        return $subjectType === 'null';
    }

    protected function supports($attribute, $subject): bool
    {
        return $attribute === 'CHANGE_MY_PASSWORD';
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        $user = $this->ensureUser($token);
        if (!$user) {
            return false;
        }
        return true;
    }
}

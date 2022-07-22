<?php

namespace App\Domain\UserAlias\Security;

use App\Domain\Entity\UserAlias\UserAlias;
use App\Security\Voter\UserVoter as BaseUserVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class UserAliasVoter extends BaseUserVoter
{

    public function supportsAttribute(string $attribute): bool
    {
        return in_array(
            $attribute,
            [
                'USER_ALIAS_LIST',
                'USER_ALIAS_CREATE',
                'USER_ALIAS_SHOW',
                'USER_ALIAS_EDIT',
                'USER_ALIAS_DELETE',
                'USER_ALIAS_UNDELETE'
            ],
            true
        );
    }

    public function supportsType(string $subjectType): bool
    {
        return $subjectType === UserAlias::class || $subjectType === 'null';
    }

    protected function supports($attribute, $subject): bool
    {
        if (in_array($attribute, ['USER_ALIAS_LIST', 'USER_ALIAS_CREATE'], true)) {
            return true;
        }

        if (!$subject instanceof UserAlias) {
            return false;
        }

        return in_array(
            $attribute,
            [
                'USER_ALIAS_SHOW',
                'USER_ALIAS_EDIT',
                'USER_ALIAS_DELETE',
                'USER_ALIAS_UNDELETE'
            ]
        );
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        $user = $this->ensureAdmin($token);
        if (!$user) {
            return false;
        }

        switch ($attribute) {
            case 'USER_ALIAS_LIST':
            case 'USER_ALIAS_CREATE':
                return true;
            case 'USER_ALIAS_SHOW':
            case 'USER_ALIAS_EDIT':
            case 'USER_ALIAS_DELETE':
                /** @var UserAlias $subject */
                return true;
            default:
                return false;
        }
    }
}

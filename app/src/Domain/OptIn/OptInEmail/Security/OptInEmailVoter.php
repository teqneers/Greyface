<?php

namespace App\Domain\OptIn\OptInEmail\Security;

use App\Domain\Entity\OptIn\OptInEmail\OptInEmail;
use App\Security\Voter\UserVoter as BaseUserVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class OptInEmailVoter extends BaseUserVoter
{

    public function supportsAttribute(string $attribute): bool
    {
        return in_array(
            $attribute,
            [
                'OPTIN_EMAIL_LIST',
                'OPTIN_EMAIL_CREATE',
                'OPTIN_EMAIL_SHOW',
                'OPTIN_EMAIL_EDIT',
                'OPTIN_EMAIL_DELETE'
            ],
            true
        );
    }

    public function supportsType(string $subjectType): bool
    {
        return $subjectType === OptInEmail::class || $subjectType === 'null';
    }

    protected function supports($attribute, $subject): bool
    {
        if (in_array($attribute,
            [
                'OPTIN_EMAIL_LIST',
                'OPTIN_EMAIL_CREATE',
                'OPTIN_EMAIL_SHOW',
                'OPTIN_EMAIL_EDIT',
                'OPTIN_EMAIL_DELETE'
            ], true)) {
            return true;
        }

        if (!$subject instanceof OptInEmail) {
            return false;
        }

        return in_array(
            $attribute,
            [

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
            case 'OPTIN_EMAIL_LIST':
            case 'OPTIN_EMAIL_CREATE':
            case 'OPTIN_EMAIL_SHOW':
            case 'OPTIN_EMAIL_EDIT':
            case 'OPTIN_EMAIL_DELETE':
                return true;
            default:
                return false;
        }
    }
}

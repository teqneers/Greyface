<?php

namespace App\Domain\OptOut\OptOutEmail\Security;

use App\Domain\Entity\OptOut\OptOutEmail\OptOutEmail;
use App\Security\Voter\UserVoter as BaseUserVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class OptOutEmailVoter extends BaseUserVoter
{

    public function supportsAttribute(string $attribute): bool
    {
        return in_array(
            $attribute,
            [
                'OPTOUT_EMAIL_LIST',
                'OPTOUT_EMAIL_CREATE',
                'OPTOUT_EMAIL_SHOW',
                'OPTOUT_EMAIL_EDIT',
                'OPTOUT_EMAIL_DELETE'
            ],
            true
        );
    }

    public function supportsType(string $subjectType): bool
    {
        return $subjectType === OptOutEmail::class || $subjectType === 'null';
    }

    protected function supports($attribute, $subject): bool
    {
        if (in_array($attribute, ['OPTOUT_EMAIL_LIST', 'OPTOUT_EMAIL_CREATE'], true)) {
            return true;
        }

        if (!$subject instanceof OptOutEmail) {
            return false;
        }

        return in_array(
            $attribute,
            [
                'OPTOUT_EMAIL_SHOW',
                'OPTOUT_EMAIL_EDIT',
                'OPTOUT_EMAIL_DELETE'
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
            case 'OPTOUT_EMAIL_LIST':
            case 'OPTOUT_EMAIL_CREATE':
                return true;
            case 'OPTOUT_EMAIL_SHOW':
            case 'OPTOUT_EMAIL_EDIT':
            case 'OPTOUT_EMAIL_DELETE':
                /** @var OptOutEmail $subject */
                return true;
            default:
                return false;
        }
    }
}

<?php

namespace App\Domain\AutoWhiteList\EmailAutoWhiteList\Security;

use App\Domain\Entity\AutoWhiteList\EmailAutoWhiteList\EmailAutoWhiteList;
use App\Security\Voter\UserVoter as BaseUserVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class EmailAutoWhiteListVoter extends BaseUserVoter
{

    public function supportsAttribute(string $attribute): bool
    {
        return in_array(
            $attribute,
            [
                'EMAIL_AUTOWHITE_LIST',
                'EMAIL_AUTOWHITE_CREATE',
                'EMAIL_AUTOWHITE_SHOW',
                'EMAIL_AUTOWHITE_EDIT',
                'EMAIL_AUTOWHITE_DELETE'
            ],
            true
        );
    }

    public function supportsType(string $subjectType): bool
    {
        return $subjectType === EmailAutoWhiteList::class || $subjectType === 'null';
    }

    protected function supports($attribute, $subject): bool
    {
        if (in_array($attribute, [
            'EMAIL_AUTOWHITE_LIST',
            'EMAIL_AUTOWHITE_CREATE',
            'EMAIL_AUTOWHITE_DELETE',
            'EMAIL_AUTOWHITE_EDIT'
        ], true)) {
            return true;
        }

        if (!$subject instanceof EmailAutoWhiteList) {
            return false;
        }

        return in_array(
            $attribute,
            [
                'EMAIL_AUTOWHITE_SHOW',
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
            case 'EMAIL_AUTOWHITE_LIST':
            case 'EMAIL_AUTOWHITE_CREATE':
            case 'EMAIL_AUTOWHITE_DELETE':
            case 'EMAIL_AUTOWHITE_EDIT':
                return true;
            case 'EMAIL_AUTOWHITE_SHOW':
                /** @var EmailAutoWhiteList $subject */
                return true;
            default:
                return false;
        }
    }
}

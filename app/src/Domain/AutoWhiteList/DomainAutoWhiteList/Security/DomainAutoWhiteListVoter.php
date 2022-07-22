<?php

namespace App\Domain\AutoWhiteList\DomainAutoWhiteList\Security;

use App\Domain\Entity\AutoWhiteList\DomainAutoWhiteList\DomainAutoWhiteList;
use App\Security\Voter\UserVoter as BaseUserVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class DomainAutoWhiteListVoter extends BaseUserVoter
{

    public function supportsAttribute(string $attribute): bool
    {
        return in_array(
            $attribute,
            [
                'DOMAIN_AUTOWHITE_LIST',
                'DOMAIN_AUTOWHITE_CREATE',
                'DOMAIN_AUTOWHITE_SHOW',
                'DOMAIN_AUTOWHITE_EDIT',
                'DOMAIN_AUTOWHITE_DELETE'
            ],
            true
        );
    }

    public function supportsType(string $subjectType): bool
    {
        return $subjectType === DomainAutoWhiteList::class || $subjectType === 'null';
    }

    protected function supports($attribute, $subject): bool
    {
        if (in_array($attribute, [
            'DOMAIN_AUTOWHITE_LIST',
            'DOMAIN_AUTOWHITE_CREATE',
            'DOMAIN_AUTOWHITE_DELETE',
            'DOMAIN_AUTOWHITE_EDIT'
        ], true)) {
            return true;
        }

        if (!$subject instanceof DomainAutoWhiteList) {
            return false;
        }

        return in_array(
            $attribute,
            [
                'DOMAIN_AUTOWHITE_SHOW',
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
            case 'DOMAIN_AUTOWHITE_LIST':
            case 'DOMAIN_AUTOWHITE_CREATE':
            case 'DOMAIN_AUTOWHITE_DELETE':
            case 'DOMAIN_AUTOWHITE_EDIT':
                return true;
            case 'DOMAIN_AUTOWHITE_SHOW':
                /** @var DomainAutoWhiteList $subject */
                return true;
            default:
                return false;
        }
    }
}

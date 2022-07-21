<?php

namespace App\Domain\OptInDomain\Security;

use App\Domain\Entity\OptInDomain\OptInDomain;
use App\Security\Voter\UserVoter as BaseUserVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class OptInDomainVoter extends BaseUserVoter
{

    public function supportsAttribute(string $attribute): bool
    {
        return in_array(
            $attribute,
            [
                'OPTIN_DOMAIN_LIST',
                'OPTIN_DOMAIN_CREATE',
                'OPTIN_DOMAIN_SHOW',
                'OPTIN_DOMAIN_EDIT',
                'OPTIN_DOMAIN_DELETE'
            ],
            true
        );
    }

    public function supportsType(string $subjectType): bool
    {
        return $subjectType === OptInDomain::class || $subjectType === 'null';
    }

    protected function supports($attribute, $subject): bool
    {
        if (in_array($attribute, ['OPTIN_DOMAIN_LIST', 'OPTIN_DOMAIN_CREATE'], true)) {
            return true;
        }

        if (!$subject instanceof OptInDomain) {
            return false;
        }

        return in_array(
            $attribute,
            [
                'OPTIN_DOMAIN_SHOW',
                'OPTIN_DOMAIN_EDIT',
                'OPTIN_DOMAIN_DELETE'
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
            case 'OPTIN_DOMAIN_LIST':
            case 'OPTIN_DOMAIN_CREATE':
                return true;
            case 'OPTIN_DOMAIN_SHOW':
            case 'OPTIN_DOMAIN_EDIT':
            case 'OPTIN_DOMAIN_DELETE':
                /** @var OptInDomain $subject */
                return true;
            default:
                return false;
        }
    }
}

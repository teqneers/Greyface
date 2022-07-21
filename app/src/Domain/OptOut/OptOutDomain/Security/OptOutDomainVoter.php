<?php

namespace App\Domain\OptOut\OptOutDomain\Security;

use App\Domain\Entity\OptOut\OptOutDomain\OptOutDomain;
use App\Security\Voter\UserVoter as BaseUserVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class OptOutDomainVoter extends BaseUserVoter
{

    public function supportsAttribute(string $attribute): bool
    {
        return in_array(
            $attribute,
            [
                'OPTOUT_DOMAIN_LIST',
                'OPTOUT_DOMAIN_CREATE',
                'OPTOUT_DOMAIN_SHOW',
                'OPTOUT_DOMAIN_EDIT',
                'OPTOUT_DOMAIN_DELETE'
            ],
            true
        );
    }

    public function supportsType(string $subjectType): bool
    {
        return $subjectType === OptOutDomain::class || $subjectType === 'null';
    }

    protected function supports($attribute, $subject): bool
    {
        if (in_array($attribute, ['OPTOUT_DOMAIN_LIST', 'OPTOUT_DOMAIN_CREATE'], true)) {
            return true;
        }

        if (!$subject instanceof OptOutDomain) {
            return false;
        }

        return in_array(
            $attribute,
            [
                'OPTOUT_DOMAIN_SHOW',
                'OPTOUT_DOMAIN_EDIT',
                'OPTOUT_DOMAIN_DELETE'
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
            case 'OPTOUT_DOMAIN_LIST':
            case 'OPTOUT_DOMAIN_CREATE':
                return true;
            case 'OPTOUT_DOMAIN_SHOW':
            case 'OPTOUT_DOMAIN_EDIT':
            case 'OPTOUT_DOMAIN_DELETE':
                /** @var OptOutDomain $subject */
                return true;
            default:
                return false;
        }
    }
}

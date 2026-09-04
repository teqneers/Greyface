<?php

namespace App\Domain\Dashboard\Security;

use App\Security\Voter\UserVoter as BaseUserVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/** The dashboard aggregates every list, so it is administrators only. */
class DashboardVoter extends BaseUserVoter
{
    public function supportsAttribute(string $attribute): bool
    {
        return $attribute === 'DASHBOARD_VIEW';
    }

    public function supportsType(string $subjectType): bool
    {
        return $subjectType === 'null';
    }

    protected function supports($attribute, $subject): bool
    {
        return $attribute === 'DASHBOARD_VIEW';
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        return $this->ensureAdmin($token) !== null;
    }
}

<?php

namespace App\Domain\Connect\Security;

use App\Domain\Entity\Connect\Connect;
use App\Security\Voter\UserVoter as BaseUserVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class ConnectVoter extends BaseUserVoter
{

    public function supportsAttribute(string $attribute): bool
    {
        return in_array(
            $attribute,
            [
                'CONNECT_LIST',
                'CONNECT_CREATE',
                'CONNECT_SHOW',
                'CONNECT_EDIT',
                'CONNECT_DELETE'
            ],
            true
        );
    }

    public function supportsType(string $subjectType): bool
    {
        return $subjectType === Connect::class || $subjectType === 'null';
    }

    protected function supports($attribute, $subject): bool
    {
        if (in_array($attribute, [
            'CONNECT_LIST',
            'CONNECT_CREATE',
            'CONNECT_DELETE',
            'CONNECT_EDIT'
        ], true)) {
            return true;
        }

        if (!$subject instanceof Connect) {
            return false;
        }

        return in_array(
            $attribute,
            [
                'CONNECT_SHOW',
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
            case 'CONNECT_LIST':
            case 'CONNECT_CREATE':
            case 'CONNECT_DELETE':
            case 'CONNECT_EDIT':
                return true;
            case 'CONNECT_SHOW':
                /** @var Connect $subject */
                return true;
            default:
                return false;
        }
    }
}

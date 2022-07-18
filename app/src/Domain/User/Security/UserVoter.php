<?php

namespace App\Domain\User\Security;

use App\Domain\Entity\User\User;
use App\Domain\Entity\User\UserRepository;
use App\Security\Voter\UserVoter as BaseUserVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class UserVoter extends BaseUserVoter
{
    public function __construct(
        private readonly UserRepository $userRepository
    ) {
    }

    public function supportsAttribute(string $attribute): bool
    {
        return in_array(
            $attribute,
            [
                'USER_LIST',
                'USER_CREATE',
                'USER_SHOW',
                'USER_EDIT',
                'USER_DELETE',
                'USER_UNDELETE'
            ],
            true
        );
    }

    public function supportsType(string $subjectType): bool
    {
        return $subjectType === User::class || $subjectType === 'null';
    }

    protected function supports($attribute, $subject): bool
    {
        if (in_array($attribute, ['USER_LIST', 'USER_CREATE'], true)) {
            return true;
        }

        if (!$subject instanceof User) {
            return false;
        }

        return in_array(
            $attribute,
            [
                'USER_SHOW',
                'USER_EDIT',
                'USER_DELETE',
                'USER_UNDELETE'
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
            case 'USER_LIST':
            case 'USER_CREATE':
                return true;
            case 'USER_SHOW':
                /** @var User $subject */
                return true;
            case 'USER_EDIT':
                /** @var User $subject */
                return !$subject->isDeleted();
            case 'USER_DELETE':
                /** @var User $subject */
                if ($subject->isDeleted()) {
                    return false;
                }
                if ($subject->equals($user)) {
                    return false;
                }
                if ($subject->isAdministrator()) {
                    return $this->userRepository->countAdministrators() > 1;
                }
                return true;
            case 'USER_UNDELETE':
                /** @var User $subject */
                return $subject->isDeleted();
            default:
                return false;
        }
    }
}

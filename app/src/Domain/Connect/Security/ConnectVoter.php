<?php

namespace App\Domain\Connect\Security;

use App\Domain\Entity\Connect\Connect;
use App\Domain\Entity\UserAlias\UserAliasRepository;
use App\Domain\User\UserInterface;
use App\Security\Voter\UserVoter as BaseUserVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Who may act on a greylist row.
 *
 * Administrators may act on every row. Everyone else may act only on mail
 * addressed to an address they own, which is the same rule
 * ConnectRepository::findFiltered() applies when deciding what they can see.
 * Before this existed the voter returned true for every attribute, so the
 * listing was scoped but every write endpoint was open: any account could
 * delete another user's entry, or empty the greylist outright.
 */
class ConnectVoter extends BaseUserVoter
{
    /**
     * Alias names per user id, so a bulk action over fifty rows asks the
     * database once rather than once per row.
     *
     * @var array<string, string[]>
     */
    private array $aliasNames = [];

    public function __construct(
        private readonly UserAliasRepository $userAliasRepository
    ) {
    }

    public function supportsAttribute(string $attribute): bool
    {
        return in_array(
            $attribute,
            [
                'CONNECT_LIST',
                'CONNECT_SHOW',
                'CONNECT_DELETE',
                'CONNECT_WHITELIST',
                'CONNECT_DELETE_BY_DATE'
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
        // Neither of these is about one row: the listing is filtered per user
        // by the repository, and deleting by date is all-or-nothing.
        if (in_array($attribute, ['CONNECT_LIST', 'CONNECT_DELETE_BY_DATE'], true)) {
            return true;
        }

        if (!$subject instanceof Connect) {
            return false;
        }

        return in_array($attribute, ['CONNECT_SHOW', 'CONNECT_DELETE', 'CONNECT_WHITELIST'], true);
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        $user = $this->ensureUser($token);
        if (!$user) {
            return false;
        }

        switch ($attribute) {
            case 'CONNECT_LIST':
                // Everyone may open the greylist; the repository decides what is in it.
                return true;

            case 'CONNECT_DELETE_BY_DATE':
                // One request empties the greylist for the whole server, which is
                // not something a single recipient gets to do. The interface has
                // always hidden this; now the API refuses it too.
                return $user->isAdministrator();

            case 'CONNECT_SHOW':
            case 'CONNECT_DELETE':
            case 'CONNECT_WHITELIST':
                /** @var Connect $subject */
                return $user->isAdministrator() || $this->ownsRecipient($user, $subject);

            default:
                return false;
        }
    }

    /**
     * Case-insensitively, because the listing compares these same two columns in
     * the database under a _ci collation. Matching case-sensitively here would
     * let a user see a row they were then forbidden to touch.
     */
    private function ownsRecipient(UserInterface $user, Connect $connect): bool
    {
        $id = $user->getId();
        if (!isset($this->aliasNames[$id])) {
            $this->aliasNames[$id] = array_map(
                'mb_strtolower',
                $this->userAliasRepository->findAliasNamesForUserId($id)
            );
        }

        return in_array(mb_strtolower($connect->getRcpt()), $this->aliasNames[$id], true);
    }
}

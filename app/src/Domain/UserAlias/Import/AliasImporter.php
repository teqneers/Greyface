<?php

namespace App\Domain\UserAlias\Import;

use App\Domain\Entity\User\User;
use App\Domain\Entity\User\UserRepository;
use App\Domain\Entity\UserAlias\UserAlias;
use App\Domain\Entity\UserAlias\UserAliasRepository;
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\Uuid;

/**
 * Applies a parsed list of address/username pairs.
 *
 * Three rules worth knowing before reading the code, because each is a decision
 * rather than a detail:
 *
 * **It never creates accounts.** A username the file names but the database does
 * not have is a problem to report, not an invitation. An import that invented
 * accounts would turn a typo in a mail map into a login.
 *
 * **The file wins for the addresses it names.** An address already assigned to
 * somebody else is moved, not rejected, because that is what keeping Greyface in
 * step with the mail system means. It is counted separately and shown in the dry
 * run, so nobody finds out afterwards.
 *
 * **Pruning is scoped to the accounts the file mentions.** Removing every alias
 * absent from the file would mean a partial list silently stripped everyone it
 * did not cover. A file listing anna and bob can therefore be used to sync anna
 * and bob without touching anyone else, and a complete file still syncs
 * completely.
 */
final readonly class AliasImporter
{
    public function __construct(
        private UserRepository         $users,
        private UserAliasRepository    $aliases,
        private EntityManagerInterface $entityManager
    ) {
    }

    public function import(AliasImportSource $source, bool $prune = false, bool $dryRun = false): AliasImportResult
    {
        $result = new AliasImportResult($source->problems);

        /** @var array<string, User|null> $accounts */
        $accounts = [];
        /** @var array<string, string[]> $wanted username => addresses named by the file */
        $wanted = [];
        /** @var array<string, true> $named every address the file names, whoever it names it for */
        $named = [];

        foreach ($source->pairs as $pair) {
            $address = strtolower($pair->address);

            if (!$this->looksLikeAnAddress($address)) {
                $result->addProblem(new AliasImportProblem($pair->line, $pair->address, 'notAnAddress'));
                continue;
            }

            $accounts[$pair->username] ??= $this->users->findByUsername($pair->username);
            $owner = $accounts[$pair->username];
            if ($owner === null) {
                $result->addProblem(new AliasImportProblem($pair->line, $pair->username, 'unknownUser'));
                continue;
            }

            $wanted[$pair->username][] = $address;
            $named[$address] = true;

            $existing = $this->aliases->findOneByAliasName($address);
            if ($existing === null) {
                $result->created++;
                if (!$dryRun) {
                    $this->entityManager->persist(new UserAlias((string)Uuid::uuid4(), $owner, $address));
                }
                continue;
            }

            if ($existing->getUser()->getId() === $owner->getId()) {
                $result->unchanged++;
                continue;
            }

            $result->moved++;
            $result->addMove($address, $existing->getUser()->getUsername(), $owner->getUsername());
            if (!$dryRun) {
                $existing->setUser($owner);
            }
        }

        if ($prune) {
            $this->prune($wanted, $accounts, $named, $result, $dryRun);
        }

        // The parser's problems are found first and the importer's second, so
        // unsorted they interleave out of order. Somebody working through a long
        // file reads it top to bottom.
        $result->sortProblemsByLine();

        // One unit of work for the whole file. The repository's save() flushes on
        // every call, which for a three-hundred-line import is three hundred
        // round trips, and its delete() does not flush at all — it relies on the
        // command bus's transaction middleware, which a console import never
        // goes through.
        if (!$dryRun && $result->changesAnything()) {
            $this->entityManager->flush();
        }

        return $result;
    }

    /**
     * @param array<string, string[]>  $wanted   which accounts the file covers
     * @param array<string, User|null> $accounts
     * @param array<string, true>      $named    every address the file mentions
     */
    private function prune(array $wanted, array $accounts, array $named, AliasImportResult $result, bool $dryRun): void
    {
        foreach ($wanted as $username => $addresses) {
            $owner = $accounts[$username] ?? null;
            if ($owner === null) {
                continue;
            }

            foreach ($this->aliases->findAliasNamesForUserId($owner->getId()) as $held) {
                // Against every address the file names, not just the ones it
                // names for this account. An address being moved to somebody
                // else is still listed, and reading only this account's share
                // made prune delete the alias the same run had just reassigned:
                // the move is not flushed yet, so the query still shows it here.
                if (isset($named[strtolower($held)])) {
                    continue;
                }

                $alias = $this->aliases->findByAliasNameForUser($owner, $held);
                if ($alias === null) {
                    continue;
                }

                $result->removed++;
                $result->addRemoval($held, $username);
                if (!$dryRun) {
                    $this->entityManager->remove($alias);
                }
            }
        }
    }

    /**
     * Deliberately not the entity's Assert::email(). That throws, and one bad
     * line among three hundred should be reported rather than abort the import.
     */
    private function looksLikeAnAddress(string $address): bool
    {
        return strlen($address) <= 128
            && filter_var($address, FILTER_VALIDATE_EMAIL) !== false;
    }
}

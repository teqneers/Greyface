<?php

namespace App\Tests\Domain\UserAlias\Import;

use App\Domain\Entity\User\UserRepository;
use App\Domain\Entity\UserAlias\UserAliasRepository;
use App\Domain\UserAlias\Import\AliasImporter;
use App\Domain\UserAlias\Import\AliasImportParser;
use App\Domain\UserAlias\Import\AliasImportResult;
use App\Test\DatabaseTestTrait;
use App\Test\UserAliasTrait;
use App\Test\UserDomainTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * The importer's three decisions, each of which could reasonably have gone the
 * other way: it never creates accounts, the file wins for addresses it names,
 * and pruning is scoped to the accounts the file mentions.
 */
class AliasImporterTest extends KernelTestCase
{
    use DatabaseTestTrait, UserDomainTrait, UserAliasTrait;

    private function import(string $text, bool $prune = false, bool $dryRun = false): AliasImportResult
    {
        $importer = new AliasImporter(
            self::getContainer()->get(UserRepository::class),
            self::getContainer()->get(UserAliasRepository::class),
            self::getContainer()->get('doctrine')->getManager()
        );

        return $importer->import((new AliasImportParser())->parse($text), $prune, $dryRun);
    }

    /**
     * @return string[]
     */
    private function aliasesOf(string $username): array
    {
        $user = self::getContainer()->get(UserRepository::class)->findByUsername($username);
        self::assertNotNull($user);

        $names = self::getContainer()->get(UserAliasRepository::class)->findAliasNamesForUserId($user->getId());
        sort($names);

        return $names;
    }

    public function testCreatesAliasesForKnownAccounts(): void
    {
        self::bootKernel();
        $anna = self::createUser(username: 'anna');
        self::initializeDatabaseWithEntities($anna);

        $result = $this->import("anna@example.com,anna\nsales@example.com,anna");

        self::assertSame(2, $result->created);
        self::assertSame([], $result->problems);
        self::assertSame(['anna@example.com', 'sales@example.com'], $this->aliasesOf('anna'));
    }

    /**
     * A typo in a mail map must not become a login.
     */
    public function testNeverCreatesAnAccountItDoesNotRecognise(): void
    {
        self::bootKernel();
        $anna = self::createUser(username: 'anna');
        self::initializeDatabaseWithEntities($anna);

        $result = $this->import("anna@example.com,anna\nbob@example.com,bpb");

        self::assertSame(1, $result->created);
        self::assertCount(1, $result->problems);
        self::assertSame('unknownUser', $result->problems[0]->reason);
        self::assertSame('bpb', $result->problems[0]->text);
    }

    public function testReportsSomethingThatIsNotAnAddress(): void
    {
        self::bootKernel();
        $anna = self::createUser(username: 'anna');
        self::initializeDatabaseWithEntities($anna);

        $result = $this->import('not-an-address,anna');

        self::assertSame(0, $result->created);
        self::assertSame('notAnAddress', $result->problems[0]->reason);
    }

    public function testLeavesAnAliasThatIsAlreadyRight(): void
    {
        self::bootKernel();
        $anna = self::createUser(username: 'anna');
        $alias = self::createUserAlias($anna, 'anna@example.com');
        self::initializeDatabaseWithEntities($anna, $alias);

        $result = $this->import('anna@example.com,anna');

        self::assertSame(0, $result->created);
        self::assertSame(1, $result->unchanged);
    }

    /**
     * Keeping Greyface in step with the mail system means the file decides who
     * owns the addresses it names. Reporting a conflict instead would leave the
     * two permanently out of step with no way to fix it from the file.
     */
    public function testMovesAnAddressThatBelongsToSomebodyElse(): void
    {
        self::bootKernel();
        $anna = self::createUser(username: 'anna');
        $bob = self::createUser(username: 'bob');
        $alias = self::createUserAlias($anna, 'shared@example.com');
        self::initializeDatabaseWithEntities($anna, $bob, $alias);

        $result = $this->import('shared@example.com,bob');

        self::assertSame(1, $result->moved);
        self::assertSame([['address' => 'shared@example.com', 'from' => 'anna', 'to' => 'bob']], $result->moves);
        self::assertSame([], $this->aliasesOf('anna'));
        self::assertSame(['shared@example.com'], $this->aliasesOf('bob'));
    }

    public function testPruneRemovesAddressesTheFileNoLongerLists(): void
    {
        self::bootKernel();
        $anna = self::createUser(username: 'anna');
        $keep = self::createUserAlias($anna, 'anna@example.com');
        $gone = self::createUserAlias($anna, 'old@example.com');
        self::initializeDatabaseWithEntities($anna, $keep, $gone);

        $result = $this->import('anna@example.com,anna', prune: true);

        self::assertSame(1, $result->removed);
        self::assertSame(['anna@example.com'], $this->aliasesOf('anna'));
    }

    /**
     * The reason pruning is scoped: a partial list is a normal thing to have,
     * and it must not quietly strip everyone it does not mention.
     */
    public function testPruneLeavesAccountsTheFileDoesNotMention(): void
    {
        self::bootKernel();
        $anna = self::createUser(username: 'anna');
        $bob = self::createUser(username: 'bob');
        $annas = self::createUserAlias($anna, 'anna@example.com');
        $bobs = self::createUserAlias($bob, 'bob@example.com');
        self::initializeDatabaseWithEntities($anna, $bob, $annas, $bobs);

        $result = $this->import('anna@example.com,anna', prune: true);

        self::assertSame(0, $result->removed);
        self::assertSame(['bob@example.com'], $this->aliasesOf('bob'));
    }

    /**
     * The dry run has to be produced by the code that would act, or it is a
     * different program's opinion of what would happen.
     */
    public function testADryRunReportsExactlyWhatItWouldDoAndChangesNothing(): void
    {
        self::bootKernel();
        $anna = self::createUser(username: 'anna');
        $bob = self::createUser(username: 'bob');
        $moving = self::createUserAlias($anna, 'shared@example.com');
        $doomed = self::createUserAlias($bob, 'old@example.com');
        self::initializeDatabaseWithEntities($anna, $bob, $moving, $doomed);

        $preview = $this->import("shared@example.com,bob\nnew@example.com,bob", prune: true, dryRun: true);

        self::assertSame(1, $preview->created);
        self::assertSame(1, $preview->moved);
        self::assertSame(1, $preview->removed);
        self::assertSame(['shared@example.com'], $this->aliasesOf('anna'), 'nothing may have moved');
        self::assertSame(['old@example.com'], $this->aliasesOf('bob'), 'nothing may have been added or removed');

        $applied = $this->import("shared@example.com,bob\nnew@example.com,bob", prune: true);

        self::assertSame($preview->created, $applied->created);
        self::assertSame($preview->moved, $applied->moved);
        self::assertSame($preview->removed, $applied->removed);
        self::assertSame(['new@example.com', 'shared@example.com'], $this->aliasesOf('bob'));
    }

    /**
     * A prune once deleted the very alias the same run had reassigned. The move
     * is not flushed when prune runs, so the query still shows the address under
     * its old owner, and checking only that account's share of the file made it
     * look abandoned. The address was moved and then removed, and the account it
     * moved to was left with nothing.
     */
    public function testPruneDoesNotRemoveAnAddressTheSameRunReassigned(): void
    {
        self::bootKernel();
        $anna = self::createUser(username: 'anna');
        $bob = self::createUser(username: 'bob');
        $hers = self::createUserAlias($anna, 'anna@example.com');
        $moving = self::createUserAlias($anna, 'sales@example.com');
        self::initializeDatabaseWithEntities($anna, $bob, $hers, $moving);

        $result = $this->import("anna@example.com,anna\nsales@example.com,bob", prune: true);

        self::assertSame(1, $result->moved);
        self::assertSame(0, $result->removed, 'the reassigned address is still listed by the file');
        self::assertSame(['anna@example.com'], $this->aliasesOf('anna'));
        self::assertSame(['sales@example.com'], $this->aliasesOf('bob'));
    }

    public function testAddressesAreStoredInLowerCase(): void
    {
        self::bootKernel();
        $anna = self::createUser(username: 'anna');
        self::initializeDatabaseWithEntities($anna);

        $this->import('Anna@Example.COM,anna');

        self::assertSame(['anna@example.com'], $this->aliasesOf('anna'));
    }
}

<?php

namespace App\Tests\Command;

use App\Command\ImportAliasesCommand;
use App\Domain\Entity\User\UserRepository;
use App\Domain\Entity\UserAlias\UserAliasRepository;
use App\Domain\UserAlias\Import\AliasImporter;
use App\Domain\UserAlias\Import\AliasImportParser;
use App\Test\DatabaseTestTrait;
use App\Test\UserAliasTrait;
use App\Test\UserDomainTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * The command is a thin front for the importer, which is tested on its own, so
 * this covers what only the command does: reading the file, choosing the mode,
 * and printing something an operator can act on.
 */
class ImportAliasesCommandTest extends KernelTestCase
{
    use DatabaseTestTrait, UserDomainTrait, UserAliasTrait;

    private string $file;

    protected function tearDown(): void
    {
        if (isset($this->file) && file_exists($this->file)) {
            unlink($this->file);
        }

        parent::tearDown();
    }

    private function tester(): CommandTester
    {
        self::bootKernel();

        $command = new ImportAliasesCommand(
            new AliasImportParser(),
            new AliasImporter(
                self::getContainer()->get(UserRepository::class),
                self::getContainer()->get(UserAliasRepository::class),
                self::getContainer()->get('doctrine')->getManager()
            )
        );

        $application = new Application();
        $application->add($command);

        return new CommandTester($application->find('greyface:alias:import'));
    }

    private function fileContaining(string $text): string
    {
        $this->file = tempnam(sys_get_temp_dir(), 'aliases');
        file_put_contents($this->file, $text);

        return $this->file;
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

    public function testImportsAFile(): void
    {
        $tester = $this->tester();
        $anna = self::createUser(username: 'anna');
        self::initializeDatabaseWithEntities($anna);

        $tester->execute(['file' => $this->fileContaining("anna@example.com,anna\nsales@example.com,anna")]);

        self::assertSame(Command::SUCCESS, $tester->getStatusCode());
        self::assertStringContainsString('2 added', $tester->getDisplay());
        self::assertSame(['anna@example.com', 'sales@example.com'], $this->aliasesOf('anna'));
    }

    public function testADryRunSaysSoAndWritesNothing(): void
    {
        $tester = $this->tester();
        $anna = self::createUser(username: 'anna');
        self::initializeDatabaseWithEntities($anna);

        $tester->execute([
            'file' => $this->fileContaining('anna@example.com,anna'),
            '--dry-run' => true,
        ]);

        self::assertStringContainsString('Nothing was written', $tester->getDisplay());
        self::assertSame([], $this->aliasesOf('anna'));
    }

    public function testTheUserOptionTakesAPlainAddressList(): void
    {
        $tester = $this->tester();
        $anna = self::createUser(username: 'anna');
        self::initializeDatabaseWithEntities($anna);

        $tester->execute([
            'file' => $this->fileContaining("anna@example.com\nsales@example.com"),
            '--user' => 'anna',
        ]);

        self::assertSame(['anna@example.com', 'sales@example.com'], $this->aliasesOf('anna'));
    }

    /**
     * A run from cron must apply the lines it understood. Failing the whole run
     * over one bad line would look, in a mail log, exactly like doing nothing.
     */
    public function testUnreadableLinesAreReportedButDoNotFailTheRun(): void
    {
        $tester = $this->tester();
        $anna = self::createUser(username: 'anna');
        self::initializeDatabaseWithEntities($anna);

        $tester->execute(['file' => $this->fileContaining("anna@example.com,anna\nnonsense\nx@example.com,ghost")]);

        self::assertSame(Command::SUCCESS, $tester->getStatusCode());
        self::assertStringContainsString('no such account', $tester->getDisplay());
        self::assertSame(['anna@example.com'], $this->aliasesOf('anna'));
    }

    public function testReportsAFileItCannotRead(): void
    {
        $tester = $this->tester();
        $tester->execute(['file' => '/nowhere/aliases.csv']);

        self::assertSame(Command::FAILURE, $tester->getStatusCode());
        self::assertStringContainsString('Cannot read', $tester->getDisplay());
    }
}

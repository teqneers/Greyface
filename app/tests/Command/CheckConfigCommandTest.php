<?php

namespace App\Tests\Command;

use App\Command\CheckConfigCommand;
use App\Command\LoadFixturesCommand;
use DAMA\DoctrineTestBundle\PHPUnit\SkipDatabaseRollback;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;

/**
 * The gate the container runs before it serves anything, and the last step of a
 * manual install. Its whole value is refusing the unsafe cases, so those are what
 * this covers.
 *
 * The command is built by hand rather than fetched from the container: it takes
 * the environment and the secret as constructor arguments, and the test
 * environment would otherwise pin both to values that make every case fail.
 */
class CheckConfigCommandTest extends KernelTestCase
{
    private const PLACEHOLDER_SECRET = 'ff7cb5c00e05226de5813f3fe4efc70a';

    private function tester(string $environment = 'prod', string $secret = 'a-real-secret'): CommandTester
    {
        self::bootKernel();

        $command = new CheckConfigCommand(
            self::getContainer()->get(Connection::class),
            $environment,
            $secret
        );

        $application = new Application();
        $application->add($command);

        return new CommandTester($application->find('greyface:check-config'));
    }

    public function testAcceptsASafeConfiguration(): void
    {
        $tester = $this->tester();
        $tester->execute([]);

        self::assertSame(Command::SUCCESS, $tester->getStatusCode());
        self::assertStringContainsString('Configuration looks good', $tester->getDisplay());
    }

    public function testRefusesAnEnvironmentOtherThanProd(): void
    {
        $tester = $this->tester(environment: 'dev');
        $tester->execute([]);

        self::assertSame(Command::FAILURE, $tester->getStatusCode());
        self::assertStringContainsString('APP_ENV', $tester->getDisplay());
    }

    public function testRefusesTheSecretPublishedInTheRepository(): void
    {
        $tester = $this->tester(secret: self::PLACEHOLDER_SECRET);
        $tester->execute([]);

        self::assertSame(Command::FAILURE, $tester->getStatusCode());
        self::assertStringContainsString('APP_SECRET', $tester->getDisplay());
    }

    public function testRefusesAnEmptySecret(): void
    {
        $tester = $this->tester(secret: '');
        $tester->execute([]);

        self::assertSame(Command::FAILURE, $tester->getStatusCode());
        self::assertStringContainsString('APP_SECRET', $tester->getDisplay());
    }

    public function testReportsEverythingWrongAtOnce(): void
    {
        // An operator fixing one problem at a time and restarting each time is a
        // miserable way to install anything.
        $tester = $this->tester(environment: 'dev', secret: self::PLACEHOLDER_SECRET);
        $tester->execute([]);

        $display = $tester->getDisplay();
        self::assertStringContainsString('APP_ENV', $display);
        self::assertStringContainsString('APP_SECRET', $display);
    }

    public function testSkipsTheDatabaseWhenAsked(): void
    {
        $tester = $this->tester();
        $tester->execute(['--skip-database' => true]);

        self::assertSame(Command::SUCCESS, $tester->getStatusCode());
    }

    // Dropping a table is DDL, which implicitly commits and would destroy the
    // surrounding transaction, so this one opts out and restores the table itself.
    #[SkipDatabaseRollback]
    public function testRefusesADatabaseWithoutSqlgreyTables(): void
    {
        self::bootKernel();
        $connection = self::getContainer()->get(Connection::class);

        // Greyface is an interface to SQLGrey and does not create its tables, so
        // a database without them is a misconfiguration, not something to fix up.
        $connection->executeStatement('DROP TABLE IF EXISTS connect');

        $command = new CheckConfigCommand($connection, 'prod', 'a-real-secret');
        $application = new Application();
        $application->add($command);
        $tester = new CommandTester($application->find('greyface:check-config'));
        $tester->execute([]);

        self::assertSame(Command::FAILURE, $tester->getStatusCode());
        self::assertStringContainsString('SQLGrey', $tester->getDisplay());
        self::assertStringContainsString('connect', $tester->getDisplay());

        $this->restoreSqlgreyTables();
    }

    /**
     * The fixtures are the only thing that recreates SQLGrey's tables, and they
     * put the five sample greylist rows back too, which later tests count.
     */
    private function restoreSqlgreyTables(): void
    {
        $command = new LoadFixturesCommand(
            self::getContainer()->get(Connection::class),
            self::getContainer()->get(PasswordHasherFactoryInterface::class),
            'test'
        );

        $application = new Application();
        $application->add($command);

        (new CommandTester($application->find('greyface:fixtures:load')))->execute([]);
    }
}

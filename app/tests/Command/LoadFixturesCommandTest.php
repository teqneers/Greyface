<?php

namespace App\Tests\Command;

use App\Command\LoadFixturesCommand;
use DAMA\DoctrineTestBundle\PHPUnit\SkipDatabaseRollback;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;

/**
 * The fixtures stand in for a SQLGrey installation so Greyface can be developed
 * without one. They create tables Greyface does not own and insert fake greylist
 * entries, so the environment guard is the most important thing here: run against
 * a real installation they would corrupt a live mail filter.
 *
 * The rollback every other test relies on is skipped here: this command issues
 * CREATE TABLE, and DDL implicitly commits in MariaDB, which would tear the
 * surrounding transaction down mid-test. Nothing is undone for us, so tearDown
 * puts the fixture state back for the tests that run afterwards.
 */
#[SkipDatabaseRollback]
class LoadFixturesCommandTest extends KernelTestCase
{
    private Connection $connection;

    protected function tearDown(): void
    {
        $this->tester()->execute([]);

        parent::tearDown();
    }

    private function tester(string $environment = 'test'): CommandTester
    {
        self::bootKernel();
        $this->connection = self::getContainer()->get(Connection::class);

        $command = new LoadFixturesCommand(
            $this->connection,
            self::getContainer()->get(PasswordHasherFactoryInterface::class),
            $environment
        );

        $application = new Application();
        $application->add($command);

        return new CommandTester($application->find('greyface:fixtures:load'));
    }

    public function testRefusesToRunInProduction(): void
    {
        $tester = $this->tester(environment: 'prod');
        $tester->execute([]);

        self::assertSame(Command::FAILURE, $tester->getStatusCode());
        self::assertStringContainsString('Refusing to load fixtures', $tester->getDisplay());
        self::assertStringContainsString('corrupt a real', $tester->getDisplay());
    }

    public function testCreatesSqlgreysTables(): void
    {
        $tester = $this->tester();
        $this->connection->executeStatement('DROP TABLE IF EXISTS optout_email');

        $tester->execute(['--schema-only' => true]);

        self::assertSame(Command::SUCCESS, $tester->getStatusCode());
        self::assertContains('optout_email', $this->connection->createSchemaManager()->listTableNames());
    }

    public function testSchemaOnlyInsertsNothing(): void
    {
        $tester = $this->tester();
        $this->connection->executeStatement('DELETE FROM connect');

        $tester->execute(['--schema-only' => true]);

        self::assertSame(Command::SUCCESS, $tester->getStatusCode());
        self::assertSame(0, (int)$this->connection->fetchOne('SELECT COUNT(*) FROM connect'));
    }

    public function testInsertsTheSampleGreylistEntries(): void
    {
        $tester = $this->tester();
        $this->connection->executeStatement('DELETE FROM connect');

        $tester->execute([]);

        self::assertSame(Command::SUCCESS, $tester->getStatusCode());
        // The controller tests count these five; changing them changes those.
        self::assertSame(5, (int)$this->connection->fetchOne('SELECT COUNT(*) FROM connect'));
    }

    public function testLeavesExistingGreylistEntriesAlone(): void
    {
        $tester = $this->tester();
        $before = (int)$this->connection->fetchOne('SELECT COUNT(*) FROM connect');
        self::assertGreaterThan(0, $before);

        $tester->execute([]);

        self::assertSame($before, (int)$this->connection->fetchOne('SELECT COUNT(*) FROM connect'));
    }

    public function testCreatesADevelopmentAdministratorOnlyWhenThereAreNoUsers(): void
    {
        $tester = $this->tester();

        // With the fixtures' account already present it must not add another.
        $tester->execute([]);
        self::assertStringContainsString('users left alone', $tester->getDisplay());

        $this->connection->executeStatement('DELETE FROM tq_aliases');
        $this->connection->executeStatement('DELETE FROM tq_users');

        $tester->execute([]);
        self::assertSame(Command::SUCCESS, $tester->getStatusCode());

        $row = $this->connection->fetchAssociative('SELECT username, email, password FROM tq_users');
        self::assertNotFalse($row);
        self::assertSame('admin', $row['username']);
        self::assertSame('root@localhost', $row['email']);
        // Hashed here, unlike the public hash the old migration carried.
        self::assertStringStartsWith('$2y$', $row['password']);
    }

    public function testUsesSqlgreysOwnCollationRatherThanForcingOne(): void
    {
        // The bug this whole arrangement exists to prevent: Greyface forcing
        // utf8mb4_unicode_ci while SQLGrey takes the database default meant the
        // greylist join failed on every real installation and passed here.
        $this->tester();

        $collations = $this->connection->fetchAllKeyValue(
            'SELECT CONCAT(TABLE_NAME, ".", COLUMN_NAME), COLLATION_NAME
               FROM information_schema.COLUMNS
              WHERE TABLE_SCHEMA = DATABASE()
                AND ((TABLE_NAME = "connect" AND COLUMN_NAME = "rcpt")
                  OR (TABLE_NAME = "tq_aliases" AND COLUMN_NAME = "alias_name"))'
        );

        self::assertSame(
            $collations['connect.rcpt'],
            $collations['tq_aliases.alias_name'],
            'the greylist joins these two columns, and MariaDB will not compare different collations'
        );
    }
}

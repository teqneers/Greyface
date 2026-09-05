<?php

namespace App\Tests\Command;

use App\Command\CreateUserCommand;
use App\Domain\Entity\User\User;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * How a fresh installation gets its first administrator. The container runs this
 * on every start with --if-none, so both the creating and the doing-nothing paths
 * matter, and neither may ever invent a default account.
 */
class CreateUserCommandTest extends KernelTestCase
{
    private Connection $connection;

    private function tester(): CommandTester
    {
        self::bootKernel();
        $this->connection = self::getContainer()->get(Connection::class);

        $command = new CreateUserCommand(
            $this->connection,
            self::getContainer()->get(MessageBusInterface::class)
        );

        $application = new Application();
        $application->add($command);

        return new CommandTester($application->find('greyface:user:create'));
    }

    public function testCreatesAnAdministratorFromOptions(): void
    {
        $tester = $this->tester();
        $tester->execute([
            '--username' => 'newadmin',
            '--password' => 'a-long-enough-passphrase',
        ]);

        self::assertSame(Command::SUCCESS, $tester->getStatusCode());

        $row = $this->connection->fetchAssociative(
            'SELECT username, email, role, password FROM tq_users WHERE username = ?',
            ['newadmin']
        );
        self::assertNotFalse($row);
        self::assertSame(User::ROLE_ADMIN, $row['role']);
        // The address is contact information, not a routing decision, so it is
        // derived when not given — but it must survive strict e-mail validation.
        self::assertSame('newadmin@greyface.local', $row['email']);
        self::assertStringStartsWith('$2y$', $row['password'], 'the password must be hashed');
    }

    public function testHonoursAnExplicitEmailAndRole(): void
    {
        $tester = $this->tester();
        $tester->execute([
            '--username' => 'plainuser',
            '--password' => 'a-long-enough-passphrase',
            '--email' => 'plain@greyface.test',
            '--role' => User::ROLE_USER,
        ]);

        self::assertSame(Command::SUCCESS, $tester->getStatusCode());

        $row = $this->connection->fetchAssociative(
            'SELECT email, role FROM tq_users WHERE username = ?',
            ['plainuser']
        );
        self::assertSame(['email' => 'plain@greyface.test', 'role' => User::ROLE_USER], $row);
    }

    public function testDoesNothingWithIfNoneWhenAUserExists(): void
    {
        $tester = $this->tester();
        $before = (int)$this->connection->fetchOne('SELECT COUNT(*) FROM tq_users');
        self::assertGreaterThan(0, $before, 'the fixtures should have left an account behind');

        $tester->execute([
            '--if-none' => true,
            '--username' => 'shouldnotappear',
            '--password' => 'a-long-enough-passphrase',
        ]);

        self::assertSame(Command::SUCCESS, $tester->getStatusCode());
        self::assertSame(
            $before,
            (int)$this->connection->fetchOne('SELECT COUNT(*) FROM tq_users')
        );
    }

    public function testRefusesToInventCredentials(): void
    {
        $tester = $this->tester();
        $tester->execute([]);

        self::assertSame(Command::FAILURE, $tester->getStatusCode());
        self::assertStringContainsString('never invents', $tester->getDisplay());
    }

    public function testReportsWhatValidationObjectedTo(): void
    {
        $tester = $this->tester();
        // The bus validates before handling; the operator should see the field,
        // not the middleware's generic "failed validation".
        $tester->execute([
            '--username' => 'bademail',
            '--password' => 'a-long-enough-passphrase',
            '--email' => 'not-an-email-address',
        ]);

        self::assertSame(Command::FAILURE, $tester->getStatusCode());
        self::assertStringContainsString('email', $tester->getDisplay());
        self::assertSame(
            0,
            (int)$this->connection->fetchOne('SELECT COUNT(*) FROM tq_users WHERE username = ?', ['bademail'])
        );
    }

    public function testRefusesADuplicateUsername(): void
    {
        $tester = $this->tester();
        $existing = $this->connection->fetchOne('SELECT username FROM tq_users LIMIT 1');

        $tester->execute([
            '--username' => $existing,
            '--password' => 'a-long-enough-passphrase',
        ]);

        self::assertSame(Command::FAILURE, $tester->getStatusCode());
    }
}

<?php

declare(strict_types=1);

namespace App\Command;

use App\Domain\Entity\User\User;
use DateTimeImmutable;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;

/**
 * Creates SQLGrey's tables and a little sample data, so Greyface can be developed
 * and tested without a SQLGrey installation.
 *
 * This used to live in migration Version20220721081217, which meant every
 * production install created tables it does not own and inserted fake senders
 * into a live greylist. Fixtures are the right home for it: they run only where
 * they are asked for, and never in production.
 */
#[AsCommand(
    name: 'greyface:fixtures:load',
    description: "Creates SQLGrey's tables and sample data for development and testing",
)]
class LoadFixturesCommand extends Command
{
    /**
     * SQLGrey's own schema, taken from what SQLGrey 1.8.0 actually creates rather
     * than from the invented copy Greyface's old migration carried. That copy
     * differed in ways that mattered: it forced utf8mb4_unicode_ci where SQLGrey
     * takes the database default, which is how the greylist join broke against a
     * real installation while every test here passed.
     *
     * No CHARACTER SET or COLLATE clause, deliberately — SQLGrey does not specify
     * one either, so both follow the database default and agree by construction.
     */
    private const SQLGREY_TABLES = [
        'CREATE TABLE IF NOT EXISTS config ('
        . 'parameter varchar(255) NOT NULL,'
        . 'value varchar(255) DEFAULT NULL,'
        . 'PRIMARY KEY (parameter)'
        . ') ENGINE=InnoDB',

        'CREATE TABLE IF NOT EXISTS connect ('
        . 'sender_name varchar(64) NOT NULL,'
        . 'sender_domain varchar(255) NOT NULL,'
        . 'src varchar(39) NOT NULL,'
        . 'rcpt varchar(255) NOT NULL,'
        . 'first_seen timestamp NOT NULL,'
        . 'KEY connect_idx (src, sender_domain, sender_name),'
        . 'KEY connect_fseen (first_seen)'
        . ') ENGINE=InnoDB',

        'CREATE TABLE IF NOT EXISTS domain_awl ('
        . 'sender_domain varchar(255) NOT NULL,'
        . 'src varchar(39) NOT NULL,'
        . 'first_seen timestamp NOT NULL,'
        . 'last_seen timestamp NOT NULL,'
        . 'PRIMARY KEY (src, sender_domain),'
        . 'KEY domain_awl_lseen (last_seen)'
        . ') ENGINE=InnoDB',

        'CREATE TABLE IF NOT EXISTS from_awl ('
        . 'sender_name varchar(64) NOT NULL,'
        . 'sender_domain varchar(255) NOT NULL,'
        . 'src varchar(39) NOT NULL,'
        . 'first_seen timestamp NOT NULL,'
        . 'last_seen timestamp NOT NULL,'
        . 'PRIMARY KEY (src, sender_domain, sender_name),'
        . 'KEY from_awl_lseen (last_seen)'
        . ') ENGINE=InnoDB',

        'CREATE TABLE IF NOT EXISTS optin_domain (domain varchar(255) NOT NULL, PRIMARY KEY (domain)) ENGINE=InnoDB',
        'CREATE TABLE IF NOT EXISTS optin_email (email varchar(255) NOT NULL, PRIMARY KEY (email)) ENGINE=InnoDB',
        'CREATE TABLE IF NOT EXISTS optout_domain (domain varchar(255) NOT NULL, PRIMARY KEY (domain)) ENGINE=InnoDB',
        'CREATE TABLE IF NOT EXISTS optout_email (email varchar(255) NOT NULL, PRIMARY KEY (email)) ENGINE=InnoDB',
    ];

    /**
     * Five greylisted senders. The controller tests assert against these exact
     * rows, so changing them changes the expected counts in tests/Controller.
     */
    private const SAMPLE_CONNECTS = [
        ['greyface', 'recruit-greyface.de', '15.215.255', 'jobs@greyface.de', '2022-01-11 10:52:29'],
        ['mailyz', 'greyface.org', '89.163.12', 'dummy@greyface.de', '2022-02-11 11:53:56'],
        ['info', 'greyface.com', '125.253.92', 'info@greyface.de', '2022-03-12 03:19:22'],
        ['helpdesk', 'greyface.ca', '111.127.0', 'helpdesk@greyface.de', '2022-07-11 22:41:41'],
        ['ov8r45r83w5', 'peelregion.ca', '17.4.122.244', 'contact@greyface.de', '2022-07-11 17:05:52'],
    ];

    public function __construct(
        private readonly Connection $connection,
        private readonly PasswordHasherFactoryInterface $passwordHasherFactory,
        private readonly string $environment,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption(
            'schema-only',
            null,
            InputOption::VALUE_NONE,
            "Create SQLGrey's tables but insert nothing. Run this before Greyface's "
            . 'migrations, the way a real installation has SQLGrey in place first.'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // The whole point of this command is data that must never reach a real
        // SQLGrey database, so refuse outright rather than relying on nobody
        // typing it on the wrong machine.
        if (!in_array($this->environment, ['dev', 'test'], true)) {
            $io->error(sprintf(
                'Refusing to load fixtures in the "%s" environment. These create SQLGrey tables '
                . 'and fake greylist entries, which would corrupt a real installation.',
                $this->environment
            ));

            return Command::FAILURE;
        }

        foreach (self::SQLGREY_TABLES as $sql) {
            $this->connection->executeStatement($sql);
        }

        if ($input->getOption('schema-only')) {
            $io->success("SQLGrey's tables are ready.");

            return Command::SUCCESS;
        }

        $existing = (int)$this->connection->fetchOne('SELECT COUNT(*) FROM connect');
        if ($existing === 0) {
            foreach (self::SAMPLE_CONNECTS as [$name, $domain, $source, $rcpt, $firstSeen]) {
                $this->connection->insert('connect', [
                    'sender_name'   => $name,
                    'sender_domain' => $domain,
                    'src'           => $source,
                    'rcpt'          => $rcpt,
                    'first_seen'    => $firstSeen,
                ]);
            }
        }

        $admin = $this->createDevelopmentAdmin();

        $io->success(sprintf(
            'SQLGrey tables ready, %d sample greylist entries, %s.',
            count(self::SAMPLE_CONNECTS),
            $admin ? 'admin user "admin" created with password "admin"' : 'users left alone'
        ));

        return Command::SUCCESS;
    }

    /**
     * A throw-away administrator, so a developer can log in and the controller
     * tests have an account to authenticate against. Production creates its first
     * administrator from the environment instead; see greyface:user:create.
     *
     * Inserted directly rather than through the command bus: the address the tests
     * expect, root@localhost, is realistic for a mail host but has no TLD, and the
     * CreateUser command validates e-mail in strict mode and rejects it. The
     * password is hashed properly here, unlike the public hash this replaces.
     */
    private function createDevelopmentAdmin(): bool
    {
        $users = (int)$this->connection->fetchOne('SELECT COUNT(*) FROM tq_users');
        if ($users > 0) {
            return false;
        }

        $now = (new DateTimeImmutable('now'))->format('Y-m-d H:i:s');

        $this->connection->insert('tq_users', [
            'id'         => Uuid::uuid4()->toString(),
            'username'   => 'admin',
            'email'      => 'root@localhost',
            'role'       => User::ROLE_ADMIN,
            'password'   => $this->passwordHasherFactory->getPasswordHasher(User::class)->hash('admin'),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return true;
    }
}

<?php

declare(strict_types=1);

namespace App\Command;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception as DbalException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Refuses to let an unsafe configuration start.
 *
 * This is a command rather than a shell check in the container entrypoint so that
 * both installation paths get it: the entrypoint runs it before serving, and the
 * operator documentation makes it the last step of a manual install. People
 * unpacking the archive are the ones most likely to leave the repository's
 * placeholder secret in place, so they are exactly who this protects.
 */
#[AsCommand(
    name: 'greyface:check-config',
    description: 'Verifies that this installation is safely configured for production',
)]
class CheckConfigCommand extends Command
{
    /**
     * The value committed in .env. It signs remember-me cookies, so anyone who can
     * read this repository could forge a session on an installation still using it.
     */
    private const PLACEHOLDER_SECRET = 'ff7cb5c00e05226de5813f3fe4efc70a';

    /**
     * SQLGrey's tables that Greyface maps entities onto. Greyface no longer
     * creates these — they belong to SQLGrey — so their absence means this
     * database is not a SQLGrey database, and every screen would fail with a
     * "table doesn't exist" error at the first request.
     */
    private const SQLGREY_TABLES = [
        'connect',
        'domain_awl',
        'from_awl',
        'optin_domain',
        'optin_email',
        'optout_domain',
        'optout_email',
    ];

    public function __construct(
        private readonly Connection $connection,
        private readonly string $environment,
        private readonly string $secret,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption(
            'skip-database',
            null,
            InputOption::VALUE_NONE,
            'Check the configuration without attempting to connect'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io       = new SymfonyStyle($input, $output);
        $problems = [];

        if ($this->environment !== 'prod') {
            $problems[] = sprintf(
                'APP_ENV is "%s". Set APP_ENV=prod: any other value exposes the debug toolbar '
                . 'and detailed error pages.',
                $this->environment
            );
        }

        if ($this->secret === '' || $this->secret === self::PLACEHOLDER_SECRET) {
            $problems[] = 'APP_SECRET is unset or still the placeholder from the repository. '
                . 'Generate one, for example with `openssl rand -hex 32`, and set it. It signs '
                . 'remember-me cookies, so a known value lets anyone forge a login.';
        }

        $databaseUrl = (string)($_SERVER['DATABASE_URL'] ?? '');
        if ($databaseUrl === '') {
            $problems[] = 'DATABASE_URL is unset. Point it at the database SQLGrey uses.';
        } elseif (!$input->getOption('skip-database')) {
            try {
                $this->connection->executeQuery('SELECT 1');
            } catch (DbalException $exception) {
                $problems[] = sprintf('Cannot reach the database: %s', $exception->getMessage());
            }

            $missing = $this->missingSqlgreyTables();
            if ($missing !== []) {
                $problems[] = sprintf(
                    "SQLGrey's tables are missing from this database: %s. Greyface is an interface "
                    . 'to SQLGrey and does not create them. Install SQLGrey and let it run once '
                    . 'against this database first, then point DATABASE_URL at it.',
                    implode(', ', $missing)
                );
            }
        }

        if ($problems !== []) {
            $io->error('This installation is not safe to start:');
            $io->listing($problems);

            return Command::FAILURE;
        }

        $io->success('Configuration looks good.');

        return Command::SUCCESS;
    }

    /**
     * @return list<string>
     */
    private function missingSqlgreyTables(): array
    {
        try {
            $present = $this->connection->createSchemaManager()->listTableNames();
        } catch (DbalException) {
            // Unreachable database is already reported above.
            return [];
        }

        return array_values(array_diff(self::SQLGREY_TABLES, $present));
    }
}

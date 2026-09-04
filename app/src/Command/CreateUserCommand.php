<?php

declare(strict_types=1);

namespace App\Command;

use App\Domain\Entity\User\User;
use App\Domain\User\Command\CreateUser;
use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\Exception\ValidationFailedException;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Creates a user, and in particular the first administrator of a fresh install.
 *
 * The container entrypoint runs this on every start with --if-none, which does
 * nothing once an account exists. Greyface deliberately ships no default
 * credentials: the image is public, so anything baked in would be public too.
 */
#[AsCommand(
    name: 'greyface:user:create',
    description: 'Creates a user, reading the credentials from arguments or the environment',
)]
class CreateUserCommand extends Command
{
    public function __construct(
        private readonly Connection $connection,
        private readonly MessageBusInterface $commandBus,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('username', null, InputOption::VALUE_REQUIRED, 'Defaults to $GREYFACE_ADMIN_USER')
            ->addOption('password', null, InputOption::VALUE_REQUIRED, 'Defaults to $GREYFACE_ADMIN_PASSWORD')
            ->addOption('email', null, InputOption::VALUE_REQUIRED, 'Defaults to $GREYFACE_ADMIN_EMAIL, else <username>@greyface.local')
            ->addOption('role', null, InputOption::VALUE_REQUIRED, 'admin or user', User::ROLE_ADMIN)
            ->addOption(
                'if-none',
                null,
                InputOption::VALUE_NONE,
                'Do nothing when any user already exists. Used by the container entrypoint.'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $username = $input->getOption('username') ?: ($_SERVER['GREYFACE_ADMIN_USER'] ?? '');
        $password = $input->getOption('password') ?: ($_SERVER['GREYFACE_ADMIN_PASSWORD'] ?? '');
        $email    = $input->getOption('email') ?: ($_SERVER['GREYFACE_ADMIN_EMAIL'] ?? '');
        $role     = (string)$input->getOption('role');

        if ($input->getOption('if-none') && $this->anyUserExists()) {
            $io->writeln('A user already exists; leaving the database alone.');

            return Command::SUCCESS;
        }

        if ($username === '' || $password === '') {
            $io->error(
                'No credentials given. Pass --username and --password, or set GREYFACE_ADMIN_USER '
                . 'and GREYFACE_ADMIN_PASSWORD. Greyface never invents a default account.'
            );

            return Command::FAILURE;
        }

        $createUser           = CreateUser::create();
        $createUser->username = (string)$username;
        // The address is contact information on the account, not a routing
        // decision, so a first run does not have to supply one. It cannot be
        // <username>@localhost: the command validates e-mail in strict mode,
        // which requires a TLD.
        $createUser->email    = $email !== '' ? (string)$email : $username . '@greyface.local';
        $createUser->role     = $role;
        $createUser->password = (string)$password;

        try {
            // The command bus validates and wraps this in a transaction; see
            // config/packages/messenger.yaml.
            $this->commandBus->dispatch($createUser);
        } catch (ValidationFailedException $failure) {
            // The bus validates before handling; surface what it objected to
            // rather than the middleware's generic "failed validation".
            foreach ($failure->getViolations() as $violation) {
                $io->error(sprintf('%s: %s', $violation->getPropertyPath(), $violation->getMessage()));
            }

            return Command::FAILURE;
        }

        $io->success(sprintf('Created %s "%s".', $role, $username));

        return Command::SUCCESS;
    }

    private function anyUserExists(): bool
    {
        return (int)$this->connection->fetchOne('SELECT COUNT(*) FROM tq_users') > 0;
    }
}

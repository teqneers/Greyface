<?php

namespace App\Command\User;

use App\Domain\Entity\User\User;
use App\Domain\User\Command\CreateUser;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use App\Command\Command;
use App\Command\CommandDispatchingCommand;

#[AsCommand(
    name: 'app:user:create',
    description: 'Creates a new user.'
)]
class CreateCommand extends Command
{
    use CommandDispatchingCommand;

    public function __construct(
        private readonly MessageBusInterface $commandBus
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setHelp('This command allows you to create a new user.')
            ->addOption('username', 'un', InputOption::VALUE_REQUIRED, 'Username')
            ->addOption('email', 'fn', InputOption::VALUE_REQUIRED, 'Email')
            ->addOption('admin', null, InputOption::VALUE_NONE, 'Administrator')
            ->addOption('user', null, InputOption::VALUE_NONE, 'User')
            ->addOption('password', 'p', InputOption::VALUE_OPTIONAL, 'Password');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io->title('Create a new user');

        /** @var string|null $username */
        $username = $input->getOption('username');
        if (!$username) {
            $username = $this->io->ask('Username');
        }

        /** @var string|null $email */
        $email = $input->getOption('email');
        if (!$email) {
            $email = $this->io->ask('Email', 'email@greyface.com');
        }

        if ($input->getOption('admin')) {
            $role = User::ROLE_ADMIN;
        } elseif ($input->getOption('user')) {
            $role = User::ROLE_USER;
        } else {
            $role = $this->io->choice('Role', User::AVAILABLE_ROLES, User::ROLE_USER);
        }

        /** @var string|null $password */
        $password = $input->getOption('password');
        if ($password === null) {
            $password = $this->io->askHidden('Password');
            if ($password === null) {
                $this->io->error('A password is required to create a user.');
                return self::FAILURE;
            }
        }

        $this->io->section('User Details');
        $this->io->table(
            [],
            [
                ['Username', $username],
                ['Email', $email],
                ['Role', $role],
                ['Password', $password],
            ]
        );

        if (!$this->io->confirm('Create new user?')) {
            $this->io->note('Aborted.');
            return 130;
        }

        $createUser = CreateUser::create();
        $createUser->username = $username;
        $createUser->email = $email;
        $createUser->role = $role;
        $createUser->password = $password;
        if (($errors = $this->dispatchCommand($createUser, $this->commandBus)) !== null) {
            $this->io->error($errors);
            return self::FAILURE;
        }

        $this->io->success('User ' . $username . ' created.');
        return self::SUCCESS;
    }
}

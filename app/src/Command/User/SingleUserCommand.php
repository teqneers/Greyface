<?php

namespace App\Command\User;

use App\Domain\Entity\User\User;
use App\Domain\Entity\User\UserRepository;
use DateTimeInterface;
use RuntimeException;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Command\Command;
use Webmozart\Assert\Assert;

abstract class SingleUserCommand extends Command
{
    private ?User $user = null;

    public function __construct(
        private readonly UserRepository $userRepository
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('username', InputArgument::REQUIRED, 'Username');
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);

        /** @var string $username */
        $username = $input->getArgument('username');
        $this->user = $this->userRepository->findByUsername($username);
        if (!$this->user) {
            $this->io->error('User ' . $username . ' not found.');
            throw new RuntimeException('User ' . $username . ' not found.');
        }
    }

    final protected function displayUserDetails(): void
    {
        $user = $this->getUser();

        $this->io->section('Details for user [' . $user->getUsername() . ']');
        $this->io->table(
            [],
            [
                ['ID', $user->getId()],
                ['Username', $user->getUsername()],
                ['Email', $user->getEmail()],
                new TableSeparator(),
                ['Role', $user->getRole()],
                ['Administrator', $user->isAdministrator() ? 'yes' : 'no'],
                new TableSeparator(),
                ['Deleted', $user->isDeleted() ? 'yes' : 'no'],
                new TableSeparator(),
                ['Updated', $user->getUpdatedAt()->format(DateTimeInterface::RFC822)],
                ['Created', $user->getCreatedAt()->format(DateTimeInterface::RFC822)],
            ]
        );
    }

    final protected function getUser(): User
    {
        Assert::notNull($this->user);
        return $this->user;
    }
}

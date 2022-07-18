<?php

namespace App\Command\User;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:user:show',
    description: 'Shows details for a user.'
)]
class ShowCommand extends SingleUserCommand
{
    protected function configure(): void
    {
        parent::configure();
        $this
            ->setHelp('This command allows you to show details for a user.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io->title('Show user details');
        $this->displayUserDetails();
        return self::SUCCESS;
    }
}

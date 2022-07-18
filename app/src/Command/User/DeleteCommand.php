<?php

namespace App\Command\User;

use App\Domain\Entity\User\UserRepository;
use App\Domain\User\Command\DeleteUser;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use App\Command\CommandDispatchingCommand;

#[AsCommand(
    name: 'app:user:delete',
    description: 'Deletes a user.'
)]
class DeleteCommand extends SingleUserCommand
{
    use CommandDispatchingCommand;

    public function __construct(
        private readonly MessageBusInterface $commandBus,
        UserRepository $userRepository
    ) {
        parent::__construct($userRepository);
    }

    protected function configure(): void
    {
        parent::configure();
        $this
            ->setHelp('This command allows you to delete a user.')
            ->addOption(
                'fully-delete',
                null,
                InputOption::VALUE_NONE,
                'Fully delete user instead of marking user as deleted'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io->title('Delete user');
        $this->displayUserDetails();
        $user = $this->getUser();

        /** @var bool $fullyDelete */
        $fullyDelete = $input->getOption('fully-delete');

        $confirmMessage = $fullyDelete
            ? 'Fully delete user ' . $user->getUsername() . '?'
            : '(Soft-) Delete user ' . $user->getUsername() . '?';
        if (!$this->io->confirm($confirmMessage)) {
            $this->io->note('Aborted.');
            return 130;
        }

        $enableUser = $fullyDelete ? DeleteUser::delete($user) : DeleteUser::softDelete($user);
        if (($errors = $this->dispatchCommand($enableUser, $this->commandBus)) !== null) {
            $this->io->error($errors);
            return self::FAILURE;
        }

        $successMessage = $fullyDelete
            ? 'User ' . $user->getUsername() . ' fully deleted.'
            : 'User ' . $user->getUsername() . ' marked as deleted.';
        $this->io->success($successMessage);
        return self::SUCCESS;
    }
}

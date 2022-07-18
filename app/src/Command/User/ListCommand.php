<?php

namespace App\Command\User;

use App\Domain\Entity\User\User;
use App\Domain\Entity\User\UserRepository;
use DateTimeInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Command\Command;

#[AsCommand(
    name: 'app:user:list',
    description: 'Lists all users.'
)]
class ListCommand extends Command
{
    public function __construct(
        private readonly UserRepository $userRepository
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setHelp('This command allows you to list all users.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io->title('User list');
        $this->io->table(
            [
                'ID',
                'Username',
                'Email',
                'Role',
                'Deleted',
                'Created',
            ],
            array_map(
                static function (User $user): array {
                    return [
                        $user->getId(),
                        $user->getUsername(),
                        $user->getEmail(),
                        $user->getRole(),
                        $user->isDeleted() ? 'yes' : 'no',
                        $user->getCreatedAt()->format(DateTimeInterface::RFC822),
                    ];
                },
                $this->userRepository->findAll()
            )
        );
        return self::SUCCESS;
    }
}

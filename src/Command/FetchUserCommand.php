<?php

namespace App\Command;

use App\Message\UpdateUser;
use App\Repository\UserRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(
    name: 'app:fetch-user',
    description: 'Add a short description for your command',
)]
class FetchUserCommand extends Command
{
    public function __construct(
        private UserRepository $userRepository,
        private MessageBusInterface $bus,
        string $name = null
    ) {
        parent::__construct($name);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $latest = $this->userRepository->getHighestGithubId();

        $fetchTo = $latest + 5000;

        for ($i = $latest + 1; $i <= $fetchTo; ++$i) {
            $this->bus->dispatch(
                new UpdateUser($i)
            );
        }

        $io->success('Users with github ids from ' . ($latest + 1) . ' to ' . $fetchTo . ' have been added to the queue');

        return Command::SUCCESS;
    }
}

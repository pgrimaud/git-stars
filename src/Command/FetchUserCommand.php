<?php

namespace App\Command;

use App\Message\UpdateUser;
use App\Repository\UserRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(
    name: 'app:fetch-user',
    description: 'Fetch 5000 new users from github',
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

    protected function configure(): void
    {
        $this
            ->addArgument(
                'githubId',
                InputArgument::REQUIRED,
                'Start from github ID'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $githubId = intval($input->getArgument('githubId'));

        if ($githubId == 0) {
            $io->error('Please enter a valid number above 0');

            return Command::FAILURE;
        }

        $fetchTo = $githubId + 5000;

        for ($i = $githubId; $i < $fetchTo; ++$i) {
            $this->bus->dispatch(
                new UpdateUser($i)
            );
        }

        $io->success('Users with github ids from ' . $githubId . ' to ' . $fetchTo . ' have been added to the queue');

        return Command::SUCCESS;
    }
}

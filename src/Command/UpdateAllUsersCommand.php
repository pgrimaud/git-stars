<?php

declare(strict_types=1);

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
    name: 'app:update:all-users',
    description: 'Update users whose last update happened before specified amount of days',
)]
class UpdateAllUsersCommand extends Command
{
    public function __construct(
        private UserRepository $userRepository,
        private MessageBusInterface $bus,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument(
                'days',
                InputArgument::REQUIRED,
                'Enter number of days. All users last modified before this date will be updated'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io   = new SymfonyStyle($input, $output);
        $days = intval($input->getArgument('days'));

        if ($days == 0) {
            $io->error('Please enter a valid number above 0');

            return Command::FAILURE;
        }

        $date = new \DateTime('- ' . $days . ' days');

        $users = $this->userRepository->getOldestNonUpdatedUsers(5000, $date);
        if (!$users) {
            $io->error('No user has last been updated over ' . $days . ' day(s) ago');

            return Command::FAILURE;
        }

        foreach ($users as $user) {
            $this->bus->dispatch(
                new UpdateUser($user->getGithubId())
            );
        }

        $io->success('Users last updated ' . $days . ' day(s) ago have been updated');

        return Command::SUCCESS;
    }
}

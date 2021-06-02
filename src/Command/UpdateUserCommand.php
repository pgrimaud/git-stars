<?php

namespace App\Command;

use App\Client\GitHub\GitHubClient;
use App\Service\UserService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:update-user',
    description: 'Update a user\'s star count',
)]
class UpdateUserCommand extends Command
{
    public function __construct(
        private UserService $userService,
        private GitHubClient $gitHubClient,
        string $name = null
    ) {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this->addArgument('id', InputArgument::REQUIRED, 'GitHub Id');
        $this->addOption('username', 'u', InputOption::VALUE_NONE, 'Enable search by username rather than github id');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if ($input->getOption('username')) {
            $user     = $this->gitHubClient->getUserByUsername(strval($input->getArgument('id')));
            $githubId = $user['id'];
        } else {
            $githubId = intval($input->getArgument('id'));
        }

        $user = $this->userService->update($githubId);

        if ($user) {
            $io->success('User ' . $user->getUsername() . ' was added/updated on the database!');
        } else {
            $io->warning('User with Github id ' . $githubId . ' was not found!');
        }

        return Command::SUCCESS;
    }
}

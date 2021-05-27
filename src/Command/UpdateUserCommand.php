<?php

namespace App\Command;

use App\Client\GitHub\GitHubClient;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:update-user',
    description: 'Update a user\'s star count',
)]
class UpdateUserCommand extends Command
{
    public function __construct(
        string $name = null,
        private EntityManagerInterface $manager,
        private UserRepository $userRepository,
        private GitHubClient $gitHubClient
    ) {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this->addArgument('id', InputArgument::REQUIRED, 'GitHub Id');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io       = new SymfonyStyle($input, $output);
        $githubId = $input->getArgument('id');

        $githubUser = $this->gitHubClient->getUserById(intval($githubId));

        $user = $this->userRepository->findOneBy(['githubId' => $githubUser['id']]);

        if (!$user instanceof User) {
            $user = new User();
            $user->setGithubId($githubUser['id']);
            $user->setAccessToken('');
            $user->setUsername($githubUser['login']);

            $this->manager->persist($user);
            $this->manager->flush();
        } elseif ($user->getUsername() !== $githubUser['login']) {
            // Update username if it changed since last update
            // @TODO Create a 301 Redirection
            $user->setUsername($githubUser['login']);

            $this->manager->persist($user);
            $this->manager->flush();
        }

        $repositories = $this->gitHubClient->getRepositoriesByUsername($user->getUsername());

        $stars = [];

        foreach ($repositories as $repo) {
            if (null !== $repo['language']) {
                if (!isset($stars[$repo['language']])) {
                    $stars[$repo['language']] = $repo['stargazers_count'];
                } else {
                    $stars[$repo['language']] += $repo['stargazers_count'];
                }
            }
        }

        dd($stars);

        $io->success('You have a new command! Now make it your own! Pass --help to see your options.');

        return Command::SUCCESS;
    }
}

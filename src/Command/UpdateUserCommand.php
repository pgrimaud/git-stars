<?php

namespace App\Command;

use App\Client\GitHub\GitHubClient;
use App\Entity\Language;
use App\Entity\User;
use App\Entity\UserLanguage;
use App\Repository\LanguageRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\String\Slugger\SluggerInterface;

#[AsCommand(
    name: 'app:update-user',
    description: 'Update a user\'s star count',
)]
class UpdateUserCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $manager,
        private UserRepository $userRepository,
        private LanguageRepository $languageRepository,
        private GitHubClient $gitHubClient,
        private SluggerInterface $slugger,
        string $name = null
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

        $repositories = $this->gitHubClient->getAllRepositoriesByUsername($user->getUsername());

        $stars = [];

        foreach ($repositories as $repo) {
            if ($repo['language'] !== null) {
                if (!isset($stars[$repo['language']])) {
                    $stars[$repo['language']] = $repo['stargazers_count'];
                } else {
                    $stars[$repo['language']] += $repo['stargazers_count'];
                }
            }
        }

//        dd($user->getUserLanguages());

        foreach ($stars as $key => $star) {
            $githubLanguage = (string) $key;

            $exist = false;
            foreach ($user->getUserLanguages() as $lang) {
                $langName = $lang->getLanguage()->getName();
                if ($githubLanguage === $langName) {
                    $lang->setStars($star);

                    $this->manager->persist($lang);
                    $this->manager->flush();

                    $exist = true;
                    break;
                }
            }

            if (!$exist) {
                $language = $this->languageRepository->findOneBy(['name' => $githubLanguage]);

                if (!$language instanceof Language) {
                    $language = new Language();
                    $language->setName($githubLanguage);
                    $language->setSlug($this->slugger->slug($githubLanguage)->lower());
                    $language->setColor(Language::DEFAULT_COLOR);
                }

                $userLanguage = new UserLanguage();
                $userLanguage->setUser($user);
                $userLanguage->setStars($star);
                $userLanguage->setLanguage($language);

                $this->manager->persist($userLanguage);
                $this->manager->flush();
            }
        }

        $io->success('User ' . $user->getUsername() . ' was added/updated on the database!');

        return Command::SUCCESS;
    }
}

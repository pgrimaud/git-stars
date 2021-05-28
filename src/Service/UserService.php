<?php

declare(strict_types=1);

namespace App\Service;

use App\Client\GitHub\GitHubClient;
use App\Entity\Language;
use App\Entity\User;
use App\Entity\UserLanguage;
use App\Repository\LanguageRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

class UserService
{
    public function __construct(
        private EntityManagerInterface $manager,
        private UserRepository $userRepository,
        private LanguageRepository $languageRepository,
        private GitHubClient $gitHubClient,
        private SluggerInterface $slugger,
    ) {
    }

    public function update(int $githubId): ?User
    {
        try {
            $githubUser = $this->gitHubClient->getUserById($githubId);
        } catch (\Exception $e) {
            return null;
        }

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
            if ($repo['language'] !== null && $repo['stargazers_count'] > 0) {
                if (!isset($stars[$repo['language']])) {
                    $stars[$repo['language']] = $repo['stargazers_count'];
                } else {
                    $stars[$repo['language']] += $repo['stargazers_count'];
                }
            }
        }

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
                    $language->setSlug($this->slugger->slug($githubLanguage)->lower()->toString());
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

        return $user;
    }
}

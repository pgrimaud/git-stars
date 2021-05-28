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

        $apiLanguages = [];

        foreach ($repositories as $repo) {
            if ($repo['language'] !== null && $repo['stargazers_count'] > 0) {
                if (!isset($apiLanguages[$repo['language']])) {
                    $apiLanguages[$repo['language']] = [
                        'repos' => 1,
                        'stars' => $repo['stargazers_count'],
                    ];
                } else {
                    $apiLanguages[$repo['language']]['stars'] += $repo['stargazers_count'];
                    ++$apiLanguages[$repo['language']]['repos'];
                }
            }
        }

        foreach ($apiLanguages as $key => $apiLanguage) {
            $githubLanguage = (string) $key;

            $exist = false;
            foreach ($user->getUserLanguages() as $lang) {
                $langName = $lang->getLanguage()->getName();
                if ($githubLanguage === $langName) {
                    $lang->setStars($apiLanguage['stars']);
                    $lang->setRepositories($apiLanguage['repos']);

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

                $newUserLanguage = new UserLanguage();
                $newUserLanguage->setStars($apiLanguage['stars']);
                $newUserLanguage->setRepositories($apiLanguage['repos']);
                $newUserLanguage->setLanguage($language);
                $newUserLanguage->setUser($user);

                $this->manager->persist($newUserLanguage);
                $this->manager->flush();
            }
        }

        return $user;
    }
}

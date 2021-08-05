<?php

declare(strict_types=1);

namespace App\Service;

use App\Client\GitHub\GitHubClient;
use App\Entity\Ghost;
use App\Entity\Language;
use App\Entity\User;
use App\Entity\UserLanguage;
use App\Message\GetLocation;
use App\Message\ManualUpdateUser;
use App\Repository\LanguageRepository;
use App\Repository\UserRepository;
use App\Utils\LanguageHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

class UserService
{
    public function __construct(
        private EntityManagerInterface $manager,
        private UserRepository $userRepository,
        private LanguageRepository $languageRepository,
        private GitHubClient $gitHubClient,
        private SluggerInterface $slugger,
        private MessageBusInterface $messageBus
    ) {
    }

    public function partialFetchUser(string $username, string $accessToken): ?User
    {
        $this->gitHubClient->auth($accessToken);

        try {
            $githubUser = $this->gitHubClient->getUserByUsername($username);

            $user = new User();
            $user->setGithubId($githubUser['id']);
            $user->setAccessToken('');
            $user->setUsername($githubUser['login']);
            $user->setName((string) $githubUser['name']);
            $user->setOrganization($githubUser['type'] !== 'User');
            $user->setStatus(User::STATUS_RUNNING);

            $this->manager->persist($user);
            $this->manager->flush();

            $this->messageBus->dispatch(
                new ManualUpdateUser($githubUser['id'], $accessToken)
            );

            return $user;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function update(int $githubId, ?string $accessToken = null): ?User
    {
        if ($accessToken) {
            $this->gitHubClient->auth($accessToken);
        } else {
            $this->gitHubClient->randomAuth();
        }

        try {
            $githubUser = $this->gitHubClient->getUserById($githubId);
        } catch (\Exception $e) {
            if ($e->getCode() === 404) {
                $newGhost = new Ghost();

                $newGhost->setGithubId($githubId);

                $this->manager->persist($newGhost);
                $this->manager->flush();
            }

            return null;
        }

        $user = $this->userRepository->findOneBy(['githubId' => $githubUser['id']]);

        if (!$user instanceof User) {
            $user = new User();
            $user->setGithubId($githubUser['id']);
            $user->setAccessToken('');
            $user->setUsername($githubUser['login']);
        } elseif ($user->getUsername() !== $githubUser['login']) {
            // Update username if it changed since last update
            // @TODO Create a 301 Redirection

            $user->setUsername($githubUser['login']);
        }

        // check if location changed
        if ($user->getLocation() !== $githubUser['location']) {
            $this->messageBus->dispatch(
                new GetLocation($user->getGithubId(), (string) $githubUser['location'])
            );
        }

        $user->setLocation($githubUser['location']);
        $user->setName((string) $githubUser['name']);
        $user->setOrganization($githubUser['type'] !== 'User');
        $user->setTwitterHandle($githubUser['twitter_username']);

        $this->manager->persist($user);
        $this->manager->flush();

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
            } elseif ($repo['language'] === null && $repo['stargazers_count'] > 0) {
                if (!isset($apiLanguages['No language'])) {
                    $apiLanguages['No language'] = [
                        'repos' => 1,
                        'stars' => $repo['stargazers_count'],
                    ];
                } else {
                    $apiLanguages['No language']['stars'] += $repo['stargazers_count'];
                    ++$apiLanguages['No language']['repos'];
                }
            }
        }

        foreach ($apiLanguages as $key => $apiLanguage) {
            $githubLanguage = (string) $key;

            $exist = false;
            foreach ($user->getUserLanguages() as $lang) {
                $langName = $lang->getLanguage()->getName();
                if (strtolower($githubLanguage) === strtolower($langName)) {
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
                    $language->setColor(LanguageHelper::createColor());
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

        $user->setStatus(User::STATUS_IDLE);
        $this->manager->persist($user);
        $this->manager->flush();

        return $user;
    }
}

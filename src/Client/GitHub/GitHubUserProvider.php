<?php

declare(strict_types=1);

namespace App\Client\GitHub;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthUserProvider;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserInterface;

class GitHubUserProvider extends OAuthUserProvider
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function loadUserByUsername($username, array $data = []): User
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy([
            'username' => $username,
            'githubId' => $data['id'],
        ]);
        $user->setData($data);

        return $user;
    }

    public function loadUserByOAuthUserResponse(UserResponseInterface $response): User
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy([
            'githubId' => $response->getData()['id'],
        ]);

        if (!$user instanceof User) {
            $user = new User();
            $user->setUsername($response->getData()['login']);
            $user->setGithubId($response->getData()['id']);
        }

        $user->setAccessToken($response->getAccessToken());
        $user->setData($response->getData());

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $this->loadUserByUsername($response->getNickname(), $response->getData());
    }

    public function refreshUser(UserInterface $user): User
    {
        if (!$this->supportsClass(get_class($user))) {
            throw new UnsupportedUserException(sprintf('Unsupported user class "%s"', get_class($user)));
        }

        return $this->loadUserByUsername($user->getUsername(), $user->getData());
    }

    public function supportsClass($class): bool
    {
        return 'App\\Entity\\User' === $class;
    }
}

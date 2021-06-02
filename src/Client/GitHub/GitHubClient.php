<?php

declare(strict_types=1);

namespace App\Client\GitHub;

use Github\Client;
use Github\ResultPager;

class GitHubClient
{
    private Client $client;

    public function __construct(string $generalAccessToken)
    {
        $this->client = new Client();
        $this->auth($generalAccessToken);
    }

    public function getUserById(int $githubId): array
    {
        return $this->client->api('user')->showById($githubId);
    }

    public function getUserByUsername(string $username): array
    {
        return $this->client->api('user')->show($username);
    }

    public function getAllRepositoriesByUsername(string $username): array
    {
        $userApi   = $this->client->api('user');
        $paginator = new ResultPager($this->client);

        return $paginator->fetchAll($userApi, 'repositories', [$username]);
    }

    public function checkApiKey(): array
    {
        return $this->client->api('rate_limit')->getResources();
    }

    public function auth(string $accessToken)
    {
        $this->client->authenticate($accessToken, null, Client::AUTH_ACCESS_TOKEN);
    }
}

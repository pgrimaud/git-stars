<?php

declare(strict_types=1);

namespace App\Client\GitHub;

use Github\Client;
use Github\ResultPager;

class GitHubClient
{
    private Client $client;
    private array $tokens;

    public function __construct(string $generalAccessTokens)
    {
        $this->client = new Client();
        $this->tokens = explode(',', $generalAccessTokens);
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

    public function checkApiKey(?string $token): array
    {
        if ($token) {
            $this->auth($token);
        }

        return $this->client->api('rate_limit')->getResources();
    }

    public function auth(string $accessToken)
    {
        $this->client->authenticate($accessToken, null, Client::AUTH_ACCESS_TOKEN);
    }

    public function randomAuth()
    {
        $rand = rand(0, count($this->tokens) - 1);
        $this->auth($this->tokens[$rand]);
    }
}

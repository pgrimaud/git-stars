<?php

declare(strict_types=1);

namespace App\Client\GitHub;

use Github\Client;

class GitHubClient
{
    private Client $client;

    public function __construct(string $generalAccessToken)
    {
        $this->client = new Client();
        $this->client->authenticate($generalAccessToken, null, Client::AUTH_ACCESS_TOKEN);
    }

    public function getUserById(int $githubId): array
    {
        return $this->client->api('user')->showById($githubId);
    }

    public function getRepositoriesByUsername(string $username): array
    {
        return $this->client->api('user')->repositories($username);
    }
}

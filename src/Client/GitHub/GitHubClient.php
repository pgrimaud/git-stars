<?php

declare(strict_types=1);

namespace App\Client\GitHub;

use Github\Client;
use Symfony\Component\Security\Core\User\UserInterface;

class GitHubClient
{
    public static function get(UserInterface $user, ?Client $client = null): Client
    {
        $client ??= new Client();
        $client->authenticate($user->getAccessToken(), null, Client::AUTH_ACCESS_TOKEN);

        return $client;
    }
}

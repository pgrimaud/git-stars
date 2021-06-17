<?php

declare(strict_types=1);

namespace App\Client\Twitter;

use DG\Twitter\Twitter;

class TwitterClient
{
    private Twitter $twitterClient;

    public function __construct(string $apiKey, string $apiSecretKey, string $accessToken, string $accessTokenSecret)
    {
        $this->twitterClient = new Twitter($apiKey, $apiSecretKey, $accessToken, $accessTokenSecret);
    }

    public function sendTweet(string $message): void
    {
        $this->twitterClient->send($message);
    }
}

<?php

declare(strict_types=1);

namespace App\Client\Geocode;

use GuzzleHttp\Client;

class GeocodeClient
{
    public const API_ENDPOINT = 'https://geocode.xyz';

    public function __construct(private string $apiKey, private Client $client)
    {
    }

    public function findLocation(string $location): array
    {
        $request = $this->client->request('POST', self::API_ENDPOINT, [
            'form_params' => [
                'locate' => $location,
                'geoit'  => 'json',
                'auth'   => $this->apiKey,
            ],
        ]);

        return json_decode((string) $request->getBody(), true);
    }
}

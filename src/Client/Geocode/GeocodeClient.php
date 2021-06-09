<?php

declare(strict_types=1);

namespace App\Client\Geocode;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;

class GeocodeClient
{
    public const API_ENDPOINT = 'https://photon.komoot.io/api/?limit=1&lang=en&q=';

    private Client $client;

    public function __construct()
    {
        $this->client = new Client();
    }

    public function findLocation(string $location, int $timeout = 2): array
    {
        try {
            $request = $this->makeCurlCall($location);
            $result  = json_decode((string) $request->getBody(), true);
        } catch (RequestException $e) {
            sleep($timeout);

            $timeout += 2;
            $result = $this->findLocation($location, $timeout);
        }

        return $result;
    }

    private function makeCurlCall(string $location): ResponseInterface
    {
        return $this->client->request('GET', self::API_ENDPOINT . $location);
    }
}

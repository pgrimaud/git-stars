<?php

declare(strict_types=1);

namespace App\Service;

use App\Client\Geocode\GeocodeClient;
use Doctrine\ORM\EntityManagerInterface;

class GeocodeUser
{
    public function __construct(
        private EntityManagerInterface $manager,
        private GeocodeClient $geocodeClient
    ) {
    }

    public function update(string $githubId, string $location): void
    {
        $result = $this->geocodeClient->findLocation($location);
        dd($result);
    }
}

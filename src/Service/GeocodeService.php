<?php

declare(strict_types=1);

namespace App\Service;

use App\Client\Geocode\GeocodeClient;
use App\Entity\City;
use App\Entity\Country;
use App\Entity\Location;
use App\Entity\User;
use App\Repository\CityRepository;
use App\Repository\CountryRepository;
use App\Repository\LocationRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use League\ISO3166\ISO3166;
use Symfony\Component\String\Slugger\SluggerInterface;

class GeocodeService
{
    public function __construct(
        private EntityManagerInterface $manager,
        private GeocodeClient $geocodeClient,
        private LocationRepository $locationRepository,
        private CityRepository $cityRepository,
        private CountryRepository $countryRepository,
        private SluggerInterface $slugger,
        private UserRepository $userRepository
    )
    {
    }

    public function update(int $githubId, string $location): void
    {
        $existingLocation = $this->locationRepository->findOneBy([
            'name' => trim($location),
        ]);

        if (!$existingLocation instanceof Location) {
            $result = $this->geocodeClient->findLocation($location);

            if (!isset($result['standard'])) {
                $suggestion = $result['suggestion']['region'];
                $iso        = new ISO3166();
                $apiCountry = $iso->alpha2($suggestion)['name'];
                $apiCity    = null;
            } else {
                $apiCountry = $result['standard']['countryname'];
                $apiCity    = $result['standard']['city'];
            }

            $existingLocation = new Location();

            $country = $this->countryRepository->findOneBy([
                'name' => $apiCountry,
            ]);

            if (!$country instanceof Country && $apiCountry) {
                $country = new Country();
                $country->setName($apiCountry);
                $country->setSlug($this->slugger->slug($apiCountry)->lower()->toString());

                $this->manager->persist($country);
                $this->manager->flush();
            }

            $existingLocation->setCountry($country);

            $city = $this->cityRepository->findOneBy([
                'name' => $apiCity,
            ]);

            if (!$city instanceof City && $apiCity !== null) {
                $city = new City();
                $city->setName($apiCity);
                $city->setSlug($this->slugger->slug($apiCity)->lower()->toString());

                $this->manager->persist($city);
                $this->manager->flush();
            }

            $existingLocation->setName($location);
            $existingLocation->setCity($city);

            $this->manager->persist($existingLocation);
            $this->manager->flush();
        }

        $user = $this->userRepository->findOneBy([
            'githubId' => $githubId,
        ]);

        if ($user instanceof User) {
            $user->setCountry($existingLocation->getCountry());
            $user->setCity($existingLocation->getCity());

            $this->manager->persist($user);
            $this->manager->flush();
        }
    }
}

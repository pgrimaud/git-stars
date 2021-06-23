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
    ) {
    }

    public function update(int $githubId, string $location): void
    {
        $existingLocation = $this->locationRepository->findOneBy([
            'name' => trim($location),
        ]);

        if (!$existingLocation instanceof Location) {
            $existingLocation = new Location();
            $existingLocation->setName($location);

            $location = $this->fixLocation($location);

//          Return if location doesn't exist
            if ($location === null) {
                return;
            }

            $result = $this->geocodeClient->findLocation(urlencode($location));

            $apiCountry = null;
            $apiCity    = null;

            if (isset($result['features'][0]['properties']['countrycode'])) {
                $realResult = $result['features'][0]['properties'];

                $countrycode = $realResult['countrycode'];

                $iso        = new ISO3166();
                $apiCountry = $iso->alpha2($countrycode)['name'];

                if (in_array($realResult['osm_value'], ['village', 'city'])) {
                    $apiCity = $realResult['name'];
                } elseif (in_array($realResult['osm_value'], ['country', 'state', 'farmland', 'county'])) {
                    $apiCity = null;
                } elseif (in_array($realResult['osm_type'], ['N', 'W', 'R'])) {
                    if (isset($realResult['city'])) {
                        $apiCity = $realResult['city'];
                    } else {
                        $apiCity = $realResult['name'];
                    }
                } else {
                    throw new \Exception('Invalid geocode result');
                }
            } else {
                $countrycode = null;
            }

            $existingLocation->setCountry($this->findCountry($apiCountry, $countrycode));
            $existingLocation->setCity($this->findCity($apiCity));

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

    private function findCity(?string $apiCity): ?City
    {
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

        return $city;
    }

    private function findCountry(?string $apiCountry, ?string $countrycode): ?Country
    {
        $country = $this->countryRepository->findOneBy([
            'isoCode' => $countrycode,
        ]);

        if (!$country instanceof Country && $apiCountry && $countrycode) {
            $country = new Country();
            $country->setName($apiCountry);
            $country->setSlug($this->slugger->slug($apiCountry)->lower()->toString());
            $country->setIsoCode(strtolower($countrycode));

            $this->manager->persist($country);
            $this->manager->flush();
        }

        return $country;
    }

    private function fixLocation(string $location): ?string
    {
        return match (strtolower($location)) {
            'us' => 'United States of America',
            'the internet', 'everywhere' => null,
            default => $location
        };
    }
}

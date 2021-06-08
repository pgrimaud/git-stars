<?php

namespace App\Repository;

use App\Entity\City;
use App\Entity\Country;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method City|null find($id, $lockMode = null, $lockVersion = null)
 * @method City|null findOneBy(array $criteria, array $orderBy = null)
 * @method City[]    findAll()
 * @method City[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CityRepository extends AbstractBaseRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, City::class);
    }

    public function findCitiesByCountry(Country $country): array
    {
        $cacheKey = $this->getCacheAdapter()->getItem($country->getSlug() . '-all-cities');

        if ($cacheKey->isHit()) {
            $cities = $cacheKey->get();
        } else {
            $cities = $this->createQueryBuilder('c')
                ->select('c')
                ->join('c.locations', 'loc')
                ->andWhere('loc.country = :country')
                ->setParameter('country', $country)
                ->groupBy('c.id')
                ->orderBy('c.slug')
                ->getQuery()
                ->getResult();

            $cacheKey->set($cities);
            $cacheKey->expiresAfter(600);

            $this->getCacheAdapter()->save($cacheKey);
        }

        return $cities;
    }
}

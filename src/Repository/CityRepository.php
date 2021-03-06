<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\City;
use App\Entity\Country;
use App\Entity\Language;
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

    public function findCitiesByCountry(Country $country, ?int $userTypeFilter): array
    {
        $cacheKeyName = $country->getSlug() . '-all-cities' . ($userTypeFilter !== null ? '-' . $userTypeFilter : '');
        $cacheKey     = $this->getCacheAdapter()->getItem($cacheKeyName);

        if ($cacheKey->isHit()) {
            $cities = $cacheKey->get();
        } else {
            $qb = $this->createQueryBuilder('c')
                ->select('c')
                ->join('c.locations', 'loc')
                ->join('c.users', 'u')
                ->join('u.userLanguages', 'ul');

            if ($userTypeFilter !== null) {
                $qb->andWhere('u.organization = :isOrga')
                    ->setParameter('isOrga', $userTypeFilter);
            }

            $qb->andWhere('ul.stars > 0')
                ->andWhere('loc.country = :country')
                ->setParameter('country', $country)
                ->andWhere()
                ->groupBy('c.id')
                ->orderBy('c.slug');

            $cities = $qb->getQuery()->getResult();

            $cacheKey->set($cities);
            $cacheKey->expiresAfter(3600 * 24);

            $this->getCacheAdapter()->save($cacheKey);
        }

        return $cities;
    }

    public function findAllCitiesByLanguage(Language $language, ?Country $country, ?int $userTypeFilter): array
    {
        $cacheKey = $this->getCacheAdapter()->getItem($language->getSlug() . '-all-cities' . ($userTypeFilter ? '-' . $userTypeFilter : ''));

        if ($cacheKey->isHit()) {
            $cities = $cacheKey->get();
        } else {
            $qb = $this->createQueryBuilder('c')
                ->select('c')
                ->join('c.users', 'u')
                ->join('u.userLanguages', 'ul')
                ->join('c.locations', 'loc')
                ->andWhere('loc.country = :country')
                ->setParameter('country', $country)
                ->andWhere('ul.language = :language')
                ->setParameter('language', $language);

            if ($userTypeFilter) {
                $qb->andWhere('u.organization = :isOrga')
                    ->setParameter('isOrga', $userTypeFilter);
            }

            $qb->andWhere('ul.stars > 0')
                ->groupBy('c.id')
                ->orderBy('c.slug')
                ->getQuery()
                ->getResult();

            $cities = $qb->getQuery()->getResult();

            $cacheKey->set($cities);
            $cacheKey->expiresAfter(3600 * 24);

            $this->getCacheAdapter()->save($cacheKey);
        }

        return $cities;
    }
}

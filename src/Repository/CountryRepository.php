<?php

namespace App\Repository;

use App\Entity\Country;
use App\Entity\Language;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Country|null find($id, $lockMode = null, $lockVersion = null)
 * @method Country|null findOneBy(array $criteria, array $orderBy = null)
 * @method Country[]    findAll()
 * @method Country[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CountryRepository extends AbstractBaseRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Country::class);
    }

    public function findAllCountries(): array
    {
        $cacheKey = $this->getCacheAdapter()->getItem('all-countries');

        if ($cacheKey->isHit()) {
            $countries = $cacheKey->get();
        } else {
            $countries = $this->findBy([], [
                'name' => 'ASC',
            ]);
            $cacheKey->set($countries);
            $cacheKey->expiresAfter(600);

            $this->getCacheAdapter()->save($cacheKey);
        }

        return $countries;
    }

    public function findAllCountriesByLanguage(Language $language): array
    {
        $cacheKey = $this->getCacheAdapter()->getItem($language->getSlug() . '-all-countries');

        if ($cacheKey->isHit()) {
            $countries = $cacheKey->get();
        } else {
            $countries = $this->createQueryBuilder('c')
                ->select('c')
                ->join('c.users', 'u')
                ->join('u.userLanguages', 'ul')
                ->andWhere('ul.language = :language')
                ->setParameter('language', $language)
                ->andWhere('ul.stars > 0')
                ->groupBy('c.id')
                ->orderBy('c.slug')
                ->getQuery()
                ->getResult();

            $cacheKey->set($countries);
            $cacheKey->expiresAfter(1);

            $this->getCacheAdapter()->save($cacheKey);
        }

        return $countries;
    }
}

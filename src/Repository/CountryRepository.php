<?php

namespace App\Repository;

use App\Entity\Country;
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
            $cacheKey->expiresAfter(120);

            $this->getCacheAdapter()->save($cacheKey);
        }

        return $countries;
    }
}

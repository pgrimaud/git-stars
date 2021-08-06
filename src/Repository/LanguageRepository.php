<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Language;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Language|null find($id, $lockMode = null, $lockVersion = null)
 * @method Language|null findOneBy(array $criteria, array $orderBy = null)
 * @method Language[]    findAll()
 * @method Language[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LanguageRepository extends AbstractBaseRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Language::class);
    }

    public function getTopLanguages(int $limit): array
    {
        $cacheKey = $this->getCacheAdapter()->getItem('top-languages');

        if ($cacheKey->isHit()) {
            $topLanguages = $cacheKey->get();
        } else {
            $topLanguages = $this->createQueryBuilder('l')
                ->select('l', 'sum(ul.stars) as stars')
                ->join('l.userLanguages', 'ul')
                ->orderBy('stars', 'DESC')
                ->groupBy('l.id')
                ->setMaxResults($limit)
                ->getQuery()
                ->getResult();

            $cacheKey->set($topLanguages);
            $cacheKey->expiresAfter(3600 * 24);

            $this->getCacheAdapter()->save($cacheKey);
        }

        return $topLanguages;
    }
}

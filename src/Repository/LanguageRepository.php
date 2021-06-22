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

    public function totalLanguages(): int
    {
        return (int) $this->createQueryBuilder('l')
            ->select('count(distinct(l.id)) as total')
            ->join('l.userLanguages', 'ul')
            ->andWhere('ul.stars > 0')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findAllByStars(int $start): array
    {
        return $this->createQueryBuilder('l')
            ->select('l', 'SUM(ul.stars) as stars')
            ->join('l.userLanguages', 'ul')
            ->groupBy('l.id')
            ->orderBy('sum(ul.stars)', 'DESC')
            ->setFirstResult($start)
            ->setMaxResults(25)
            ->getQuery()
            ->getResult();
    }

    public function getArrayOfNames(): array
    {
        return array_column($this->createQueryBuilder('l', 'l.name')
            ->select('l.name')
            ->join('l.userLanguages', 'ul')
            ->groupBy('l.id')
            ->orderBy('sum(ul.stars)', 'DESC')
            ->getQuery()
            ->getArrayResult(), 'name');
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
            $cacheKey->expiresAfter(3600);

            $this->getCacheAdapter()->save($cacheKey);
        }

        return $topLanguages;
    }
}

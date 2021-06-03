<?php

namespace App\Repository;

use App\Entity\Ghost;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Ghost|null find($id, $lockMode = null, $lockVersion = null)
 * @method Ghost|null findOneBy(array $criteria, array $orderBy = null)
 * @method Ghost[]    findAll()
 * @method Ghost[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GhostRepository extends AbstractBaseRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ghost::class);
    }

    // /**
    //  * @return Ghost[] Returns an array of Ghost objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('g.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Ghost
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}

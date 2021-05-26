<?php

namespace App\Repository;

use App\Entity\UserLanguage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method UserLanguage|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserLanguage|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserLanguage[]    findAll()
 * @method UserLanguage[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserLanguageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserLanguage::class);
    }

    // /**
    //  * @return UserLanguage[] Returns an array of UserLanguage objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?UserLanguage
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}

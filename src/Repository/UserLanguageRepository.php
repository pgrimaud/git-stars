<?php

namespace App\Repository;

use App\Entity\Language;
use App\Entity\User;
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

    public function findUserByLanguage(Language $language, int $start)
    {
        return $this->createQueryBuilder('ul')
            ->select('ul.stars', 'u.username')
            ->join('ul.user', 'u')
            ->andWhere('ul.language = :language')
            ->setParameter('language', $language)
            ->orderBy('ul.stars', 'DESC')
            ->setFirstResult($start)
            ->setMaxResults(25)
            ->getQuery()
            ->getResult()
            ;
    }

    public function findLanguageByUsers(User $user)
    {
        return $this->createQueryBuilder('ul')
            ->select('ul.stars', 'l.name', 'l.color')
            ->join('ul.language', 'l')
            ->andWhere('ul.user = :user')
            ->setParameter('user', $user)
            ->orderBy('ul.stars', 'DESC')
            ->getQuery()
            ->getResult()
            ;
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

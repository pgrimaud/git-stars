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

    public function findUserByLanguage(Language $language, int $start): array
    {
        return $this->createQueryBuilder('ul')
            ->select('ul.stars', 'u.username', 'u.githubId', 'u.name', 'u.organization')
            ->join('ul.user', 'u')
            ->andWhere('ul.language = :language')
            ->setParameter('language', $language)
            ->orderBy('ul.stars', 'DESC')
            ->setFirstResult($start)
            ->setMaxResults(25)
            ->getQuery()
            ->getResult();
    }

    public function totalLanguagePages(Language $language): int
    {
        return $this->createQueryBuilder('ul')
            ->select('count(ul) as total')
            ->andWhere('ul.language = :language')
            ->setParameter('language', $language)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findLanguageByUsers(User $user): array
    {
        return $this->createQueryBuilder('ul')
            ->select('ul.stars', 'l.name', 'l.color')
            ->join('ul.language', 'l')
            ->andWhere('ul.user = :user')
            ->setParameter('user', $user)
            ->orderBy('ul.stars', 'DESC')
            ->getQuery()
            ->getResult();
    }
}

<?php

namespace App\Repository;

use App\Entity\City;
use App\Entity\Country;
use App\Entity\Language;
use App\Entity\User;
use App\Entity\UserLanguage;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method UserLanguage|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserLanguage|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserLanguage[]    findAll()
 * @method UserLanguage[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserLanguageRepository extends AbstractBaseRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserLanguage::class);
    }

    public function findUserByLanguage(Language $language, ?Country $country, ?City $city, int $start): array
    {
        $query = $this->createQueryBuilder('ul')
            ->select('ul.stars', 'u.username', 'u.githubId', 'u.name', 'u.organization')
            ->join('ul.user', 'u')
            ->andWhere('ul.language = :language')
            ->setParameter('language', $language)
            ->orderBy('ul.stars', 'DESC');

        if ($country) {
            $query->andWhere('u.country = :country')
                ->setParameter('country', $country);
        }

        if ($city) {
            $query->andWhere('u.city = :city')
                ->setParameter('city', $city);
        }

        return $query->setFirstResult($start)
            ->setMaxResults(25)
            ->getQuery()
            ->getResult();
    }

    public function totalLanguagePages(Language $language, ?Country $country, ?City $city): int
    {
        $query = $this->createQueryBuilder('ul')
            ->select('count(ul) as total')
            ->join('ul.user', 'u')
            ->andWhere('ul.language = :language')
            ->setParameter('language', $language);

        if ($country) {
            $query->andWhere('u.country = :country')
                ->setParameter('country', $country);
        }

        if ($city) {
            $query->andWhere('u.city = :city')
                ->setParameter('city', $city);
        }

        return $query->getQuery()
            ->getSingleScalarResult();
    }

    public function findLanguageByUser(User $user): array
    {
        return $this->createQueryBuilder('ul')
            ->select('ul.stars', 'l.name', 'l.color', 'l.slug')
            ->join('ul.language', 'l')
            ->andWhere('ul.user = :user')
            ->setParameter('user', $user)
            ->orderBy('ul.stars', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function getUserRank(?Language $language): array
    {
        return $this->createQueryBuilder('ul')
            ->select('ul', 'rank() OVER(ORDER BY ul.stars) as rank')
            ->andWhere('ul.language = :language')
            ->setParameter('language', $language)
            ->groupBy('ul.user')
            ->orderBy('ul.stars', 'DESC')
            ->getQuery()
            ->getResult();
    }
}

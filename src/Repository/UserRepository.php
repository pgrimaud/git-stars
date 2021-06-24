<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\City;
use App\Entity\Country;
use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends AbstractBaseRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $this->_em->persist($user);
        $this->_em->flush();
    }

    public function getOldestNonUpdatedUsers(int $limit, \DateTime $dateTime): array
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.updated < :dateTime')
            ->setParameter('dateTime', $dateTime)
            ->orderBy('u.updated', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function checkUserType(?Country $country, ?City $city, bool $isOrga): ?array
    {
        $query = $this->createQueryBuilder('u')
            ->select('u.id')
            ->andWhere('u.organization = :isOrga')
            ->setParameter('isOrga', $isOrga);

        if ($country) {
            $query->andWhere('u.country = :country')
                ->setParameter('country', $country);
        }

        if ($city) {
            $query->andWhere('u.city = :city')
                ->setParameter('city', $city);
        }

        return $query->orderBy('u.id')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getTodayTop(): array
    {
        $cacheKey = $this->getCacheAdapter()->getItem('top-users-today');

        if ($cacheKey->isHit()) {
            $topToday = $cacheKey->get();
        } else {
            $topToday = $this->createQueryBuilder('u')
                ->select('u', 'sum(ul.stars) as stars', 'ci', 'co')
                ->join('u.userLanguages', 'ul')
                ->leftJoin('u.city', 'ci')
                ->leftJoin('u.country', 'co')
                ->andWhere('u.twitterHandle IS NOT NULL')
                ->andWhere('u.organization = 0')
                ->setMaxResults(4)
                ->having('stars >= 250')
                ->orderBy('RAND()')
                ->groupBy('u.id')
                ->getQuery()
                ->getResult();

            $cacheKey->set($topToday);
            $cacheKey->expiresAt(new \DateTime('23:59:59'));

            $this->getCacheAdapter()->save($cacheKey);
        }

        return $topToday;
    }

    public function findAllId(): array
    {
        return array_column(
            $this->createQueryBuilder('u')
                ->select('u.githubId as id')
                ->getQuery()
                ->getArrayResult(),
            'id'
        );
    }

    public function countUsers(): int
    {
        return (int) $this->createQueryBuilder('u')
                 ->select('COUNT(u.id)')
                 ->getQuery()
                 ->getSingleScalarResult();
    }
}

<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
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

    public function getHighestGithubId(): int
    {
        $user = $this->createQueryBuilder('u')
            ->orderBy('u.githubId', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        return !$user ? 0 : $user->getGitHubId();
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

    public function totalPages(): int
    {
        return $this->createQueryBuilder('u')
            ->select('count(distinct(u.id)) as total')
            ->join('u.userLanguages', 'ul')
            ->andWhere('ul.stars > 0')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findSomeUsers(int $start): array
    {
        return $this->createQueryBuilder('u')
            ->select('u', 'ul.stars')
            ->join('u.userLanguages', 'ul')
            ->groupBy('u.id')
            ->orderBy('ul.stars', 'DESC')
            ->setFirstResult($start)
            ->setMaxResults(25)
            ->getQuery()
            ->getResult();
    }
}

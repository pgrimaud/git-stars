<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\City;
use App\Entity\Country;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class RankingService
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    public function getRankingLanguage(User $user): array
    {
        $statement = $this->em->getConnection()->executeQuery(
            'SELECT * 
             FROM ranking_user_language r
             LEFT JOIN language l on r.language_id = l.id 
             WHERE user_id=' . $user->getId() . '
             ORDER BY r.stars DESC'
        );

        return $statement->fetchAllAssociative();
    }

    public function getRankingGlobal(User $user): array
    {
        $statement = $this->em->getConnection()->executeQuery(
            'SELECT * 
             FROM ranking_global
             WHERE user_id=' . $user->getId()
        );

        return (array) $statement->fetchAssociative();
    }

    public function getTopLanguage(User $user): array
    {
        $statement = $this->em->getConnection()->executeQuery(
            'SELECT rl.stars, rl.language_id, l.color, l.name, l.slug 
             FROM ranking_user_language rl
             LEFT JOIN language l on rl.language_id = l.id
             WHERE rl.user_id=' . $user->getId() . '
             ORDER BY stars DESC
             LIMIT 1'
        );

        return (array) $statement->fetchAssociative();
    }

    public function getTopUsers(int $isOrga): array
    {
        $statement = $this->em->getConnection()->executeQuery(
            'SELECT u.*, rg.stars 
             FROM ranking_global rg
             LEFT JOIN user u on rg.user_id = u.id
             WHERE rg.is_orga = ' . $isOrga . '
             LIMIT 3'
        );

        return (array) $statement->fetchAllAssociative();
    }

    public function findSomeUsers(?Country $country, ?City $city, ?int $userTypeFilter, int $start = 0): array
    {
        $query = 'SELECT *, u.github_id as githubId, u.twitter_handle as twitterHandle
                    FROM ranking_global rl INNER JOIN user u on u.id = rl.user_id '
                    . ($userTypeFilter ? 'AND u.organization = ' . $userTypeFilter . ' ' : '')
                    . ($country ? 'AND rl.country_id = ' . $country->getId() . ' ' : '')
                    . ($city ? 'AND rl.city_id = ' . $city->getId() . ' ' : '')
                    . 'ORDER BY rl.id ASC 
                    LIMIT ' . $start . ', 25';

        $statement = $this->em->getConnection()->executeQuery($query);

        return (array) $statement->fetchAllAssociative();
    }

    public function findAllLanguagesByStars(int $start = 0): array
    {
        $query = 'SELECT * 
                    FROM ranking_language rl INNER JOIN language l on rl.language_id = l.id
                    WHERE rl.id >= ' . ($start + 1) . ' ' . 'ORDER BY rl.id ASC LIMIT 25';

        $statement = $this->em->getConnection()->executeQuery($query);

        return (array) $statement->fetchAllAssociative();
    }

    public function getLanguageNames(): array
    {
        $query = 'SELECT l.name
                    FROM ranking_language rl INNER JOIN language l on rl.language_id = l.id
                    ORDER BY rl.id ASC';

        $statement = $this->em->getConnection()->executeQuery($query);

        return (array) $statement->fetchAllAssociative();
    }

    public function getTotalLanguagePages(): int
    {
        $query = 'SELECT count(id)
                    FROM ranking_language rl
                    ORDER BY rl.id ASC';

        $statement = $this->em->getConnection()->executeQuery($query);

        return (int) $statement->fetchOne();
    }

    public function getTotalUserPages(?Country $country, ?City $city, ?int $userTypeFilter): int
    {
        $query = 'SELECT count(id)
                    FROM ranking_global rg
                    WHERE rg.stars >= 1' . ' '
                    . ($userTypeFilter ? 'AND rg.is_orga = ' . $userTypeFilter . ' ' : '')
                    . ($country ? 'AND rg.country_id = ' . $country->getId() . ' ' : '')
                    . ($city ? 'AND rg.city_id = ' . $city->getId() . ' ' : '')
                    . 'ORDER BY rg.id ASC';

        $statement = $this->em->getConnection()->executeQuery($query);

        return (int) $statement->fetchOne();
    }
}

<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class RankingService
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    public function getRanking(User $user): array
    {
        $statement = $this->em->getConnection()->executeQuery(
            'SELECT * FROM ranking r
             LEFT JOIN language l on r.language_id = l.id 
             WHERE user_id=' . $user->getId() . '
             ORDER BY r.stars DESC'
        );

        return $statement->fetchAllAssociative();
    }
}

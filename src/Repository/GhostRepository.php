<?php

declare(strict_types=1);

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
}

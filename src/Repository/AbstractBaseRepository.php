<?php

declare(strict_types=1);

namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Component\Cache\Adapter\AdapterInterface;

abstract class AbstractBaseRepository extends ServiceEntityRepository
{
    private AdapterInterface $cacheAdapter;

    public function setAdapter(AdapterInterface $adapter): void
    {
        $this->cacheAdapter = $adapter;
    }

    public function getCacheAdapter(): AdapterInterface
    {
        return $this->cacheAdapter;
    }
}

<?php

declare(strict_types=1);

namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Component\Cache\Adapter\TraceableAdapter;

abstract class AbstractBaseRepository extends ServiceEntityRepository
{
    private TraceableAdapter $cacheAdapter;

    public function setAdapter(TraceableAdapter $adapter): void
    {
        $this->cacheAdapter = $adapter;
    }

    public function getCacheAdapter(): TraceableAdapter
    {
        return $this->cacheAdapter;
    }
}

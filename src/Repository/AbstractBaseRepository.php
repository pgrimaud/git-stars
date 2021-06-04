<?php

declare(strict_types=1);

namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Sentry\SentryBundle\Tracing\Cache\TraceableCacheAdapter;
use Symfony\Component\Cache\Adapter\TraceableAdapter;

abstract class AbstractBaseRepository extends ServiceEntityRepository
{
    private TraceableCacheAdapter | TraceableAdapter $cacheAdapter;

    public function setAdapter(TraceableCacheAdapter | TraceableAdapter $adapter): void
    {
        $this->cacheAdapter = $adapter;
    }

    public function getCacheAdapter(): TraceableCacheAdapter | TraceableAdapter
    {
        return $this->cacheAdapter;
    }
}

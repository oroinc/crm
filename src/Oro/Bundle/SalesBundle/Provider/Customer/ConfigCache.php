<?php

namespace Oro\Bundle\SalesBundle\Provider\Customer;

use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

/**
 * The cache for customer configuration.
 */
class ConfigCache
{
    private CacheItemPoolInterface $cache;
    private ?CacheItemInterface $cacheItem = null;

    public function __construct(CacheItemPoolInterface $cache)
    {
        $this->cache = $cache;
    }

    /**
     * Deletes all entries from the cache.
     */
    public function clear(): void
    {
        $this->cache->clear();
    }

    /**
     * Fetches customer classes from the cache.
     */
    public function getClasses(string $key): ?array
    {
        $this->cacheItem = $this->cache->getItem($key);
        return $this->cacheItem->isHit() ? $this->cacheItem->get() : null;
    }

    /**
     * Puts customer classes to the cache
     */
    public function setClasses(string $key, array $classes): void
    {
        $this->cacheItem ??= $this->cache->getItem($key);
        $this->cacheItem->set($classes);
        $this->cache->save($this->cacheItem);
    }
}

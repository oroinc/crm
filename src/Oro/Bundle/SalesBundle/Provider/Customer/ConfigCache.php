<?php

namespace Oro\Bundle\SalesBundle\Provider\Customer;

use Doctrine\Common\Cache\CacheProvider;

/**
 * The cache for customer configuration.
 */
class ConfigCache
{
    /** @var CacheProvider */
    private $cache;

    public function __construct(CacheProvider $cache)
    {
        $this->cache = $cache;
    }

    /**
     * Deletes all entries from the cache.
     */
    public function clear(): void
    {
        $this->cache->deleteAll();
    }

    /**
     * Fetches customer classes from the cache.
     *
     * @param string $key
     *
     * @return string[]|null
     */
    public function getClasses(string $key): ?array
    {
        $classes = $this->cache->fetch($key);
        if (false === $classes) {
            return null;
        }

        return $classes;
    }

    /**
     * Puts customer classes to the cache.
     *
     * @param string   $key
     * @param string[] $classes
     */
    public function setClasses(string $key, array $classes): void
    {
        $this->cache->save($key, $classes);
    }
}

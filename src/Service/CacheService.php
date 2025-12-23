<?php

namespace App\Service;

use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * Cache service for managing application-wide caching operations.
 *
 * Provides a centralized interface for caching with standardized TTL management.
 */
readonly class CacheService
{
    public function __construct(
        private CacheInterface $cache
    ) {
    }

    /**
     * Get data from cache or execute callback to fetch fresh data.
     *
     * @template T
     * @param string $key Cache key
     * @param callable(): T $callback Callback to fetch fresh data if the cache misses
     * @param int $ttl Time-to-live in seconds
     * @return mixed The cached or freshly fetched data
     * @throws InvalidArgumentException
     */
    public function remember(string $key, callable $callback, int $ttl): mixed
    {
        return $this->cache->get($key, function (ItemInterface $item) use ($callback, $ttl) {
            $item->expiresAfter($ttl);

            return $callback();
        });
    }

    /**
     * Invalidate (delete) a cache entry.
     *
     * @param string $key Cache key to invalidate
     * @return bool True if the item was successfully removed, false otherwise
     * @throws InvalidArgumentException
     */
    public function forget(string $key): bool
    {
        return $this->cache->delete($key);
    }
}

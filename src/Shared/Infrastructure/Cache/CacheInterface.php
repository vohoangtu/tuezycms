<?php

declare(strict_types=1);

namespace Shared\Infrastructure\Cache;

/**
 * Cache Interface
 * PSR-6 compatible cache interface
 */
interface CacheInterface
{
    /**
     * Get value from cache
     */
    public function get(string $key, mixed $default = null): mixed;

    /**
     * Set value in cache
     */
    public function set(string $key, mixed $value, int $ttl = 3600): bool;

    /**
     * Check if key exists in cache
     */
    public function has(string $key): bool;

    /**
     * Delete value from cache
     */
    public function delete(string $key): bool;

    /**
     * Clear all cache
     */
    public function clear(): bool;

    /**
     * Get multiple values
     */
    public function getMultiple(array $keys, mixed $default = null): array;

    /**
     * Set multiple values
     */
    public function setMultiple(array $values, int $ttl = 3600): bool;

    /**
     * Delete multiple values
     */
    public function deleteMultiple(array $keys): bool;
}

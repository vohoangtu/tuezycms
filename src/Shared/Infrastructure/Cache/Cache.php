<?php

declare(strict_types=1);

namespace Shared\Infrastructure\Cache;

use Shared\Infrastructure\Config\AppConfig;

/**
 * Cache Facade
 * Static interface for cache operations
 */
class Cache
{
    private static ?CacheInterface $instance = null;

    /**
     * Get cache instance
     */
    public static function getInstance(): CacheInterface
    {
        if (self::$instance === null) {
            $config = AppConfig::getInstance();
            $driver = $config->get('cache.driver', 'file');

            self::$instance = match($driver) {
                'file' => new FileCache(),
                default => new FileCache()
            };
        }

        return self::$instance;
    }

    /**
     * Set cache instance (for testing)
     */
    public static function setInstance(CacheInterface $cache): void
    {
        self::$instance = $cache;
    }

    /**
     * Get value from cache
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return self::getInstance()->get($key, $default);
    }

    /**
     * Set value in cache
     */
    public static function set(string $key, mixed $value, int $ttl = 3600): bool
    {
        return self::getInstance()->set($key, $value, $ttl);
    }

    /**
     * Check if key exists
     */
    public static function has(string $key): bool
    {
        return self::getInstance()->has($key);
    }

    /**
     * Delete value from cache
     */
    public static function delete(string $key): bool
    {
        return self::getInstance()->delete($key);
    }

    /**
     * Clear all cache
     */
    public static function clear(): bool
    {
        return self::getInstance()->clear();
    }

    /**
     * Get multiple values
     */
    public static function getMultiple(array $keys, mixed $default = null): array
    {
        return self::getInstance()->getMultiple($keys, $default);
    }

    /**
     * Set multiple values
     */
    public static function setMultiple(array $values, int $ttl = 3600): bool
    {
        return self::getInstance()->setMultiple($values, $ttl);
    }

    /**
     * Delete multiple values
     */
    public static function deleteMultiple(array $keys): bool
    {
        return self::getInstance()->deleteMultiple($keys);
    }

    /**
     * Remember value in cache
     * Get from cache or execute callback and store result
     */
    public static function remember(string $key, int $ttl, callable $callback): mixed
    {
        $value = self::get($key);
        
        if ($value !== null) {
            return $value;
        }

        $value = $callback();
        self::set($key, $value, $ttl);
        
        return $value;
    }

    /**
     * Remember value forever (1 year TTL)
     */
    public static function rememberForever(string $key, callable $callback): mixed
    {
        return self::remember($key, 31536000, $callback); // 1 year
    }

    /**
     * Forget value from cache (alias for delete)
     */
    public static function forget(string $key): bool
    {
        return self::delete($key);
    }

    /**
     * Flush all cache (alias for clear)
     */
    public static function flush(): bool
    {
        return self::clear();
    }
}

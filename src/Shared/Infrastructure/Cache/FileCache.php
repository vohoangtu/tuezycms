<?php

declare(strict_types=1);

namespace Shared\Infrastructure\Cache;

/**
 * File Cache Driver
 * Stores cache data in files
 */
class FileCache implements CacheInterface
{
    private string $cachePath;

    public function __construct(?string $cachePath = null)
    {
        $this->cachePath = $cachePath ?? dirname(__DIR__, 4) . '/storage/cache';
        
        if (!is_dir($this->cachePath)) {
            mkdir($this->cachePath, 0755, true);
        }
    }

    /**
     * Get value from cache
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $file = $this->getFilePath($key);
        
        if (!file_exists($file)) {
            return $default;
        }

        $data = unserialize(file_get_contents($file));
        
        // Check if expired
        if ($data['expires_at'] < time()) {
            $this->delete($key);
            return $default;
        }

        return $data['value'];
    }

    /**
     * Set value in cache
     */
    public function set(string $key, mixed $value, int $ttl = 3600): bool
    {
        $file = $this->getFilePath($key);
        
        $data = [
            'value' => $value,
            'expires_at' => time() + $ttl,
            'created_at' => time()
        ];

        return file_put_contents($file, serialize($data)) !== false;
    }

    /**
     * Check if key exists in cache
     */
    public function has(string $key): bool
    {
        $file = $this->getFilePath($key);
        
        if (!file_exists($file)) {
            return false;
        }

        $data = unserialize(file_get_contents($file));
        
        // Check if expired
        if ($data['expires_at'] < time()) {
            $this->delete($key);
            return false;
        }

        return true;
    }

    /**
     * Delete value from cache
     */
    public function delete(string $key): bool
    {
        $file = $this->getFilePath($key);
        
        if (file_exists($file)) {
            return unlink($file);
        }

        return true;
    }

    /**
     * Clear all cache
     */
    public function clear(): bool
    {
        $files = glob($this->cachePath . '/*.cache');
        
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }

        return true;
    }

    /**
     * Get multiple values
     */
    public function getMultiple(array $keys, mixed $default = null): array
    {
        $result = [];
        
        foreach ($keys as $key) {
            $result[$key] = $this->get($key, $default);
        }

        return $result;
    }

    /**
     * Set multiple values
     */
    public function setMultiple(array $values, int $ttl = 3600): bool
    {
        foreach ($values as $key => $value) {
            if (!$this->set($key, $value, $ttl)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Delete multiple values
     */
    public function deleteMultiple(array $keys): bool
    {
        foreach ($keys as $key) {
            if (!$this->delete($key)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get file path for cache key
     */
    private function getFilePath(string $key): string
    {
        $hash = md5($key);
        return $this->cachePath . '/' . $hash . '.cache';
    }

    /**
     * Clean expired cache files
     */
    public function cleanExpired(): int
    {
        $files = glob($this->cachePath . '/*.cache');
        $cleaned = 0;

        foreach ($files as $file) {
            if (!is_file($file)) {
                continue;
            }

            $data = unserialize(file_get_contents($file));
            
            if ($data['expires_at'] < time()) {
                unlink($file);
                $cleaned++;
            }
        }

        return $cleaned;
    }
}

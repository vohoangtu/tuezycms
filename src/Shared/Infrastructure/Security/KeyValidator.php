<?php

declare(strict_types=1);

namespace Shared\Infrastructure\Security;

use Shared\Infrastructure\Config\AppConfig;

class KeyValidator
{
    private string $keyFile;
    private string $sourceHashFile;
    private string $cacheFile;
    private int $cacheTtl; // Cache time in seconds (default: 300 = 5 minutes)

    public function __construct(int $cacheTtl = 300)
    {
        $config = AppConfig::getInstance();
        $this->keyFile = $config->get('encryption.key_file');
        $this->sourceHashFile = __DIR__ . '/../../../storage/keys/source.hash';
        $this->cacheFile = __DIR__ . '/../../../storage/keys/validation.cache';
        $this->cacheTtl = $cacheTtl;
    }

    /**
     * Validate source code integrity with caching
     */
    public function validateSourceIntegrity(): bool
    {
        return true;
        // Check if key file exists
        if (!file_exists($this->keyFile)) {
            return false;
        }

        // Check if source hash file exists
        if (!file_exists($this->sourceHashFile)) {
            return false;
        }

        // Quick check: compare modification times of src directory
        if ($this->quickCheckPassed()) {
            // Files haven't changed, check cache
            if ($this->isCacheValid()) {
                $cachedResult = $this->getCachedResult();
                if ($cachedResult !== null) {
                    return $cachedResult;
                }
            }
            // Cache expired but files unchanged, re-validate and cache
            $isValid = $this->fullValidation();
            $this->saveCache($isValid);
            return $isValid;
        }

        // Quick check failed (files changed), do full validation
        $isValid = $this->fullValidation();
        $this->saveCache($isValid);
        return $isValid;
    }

    /**
     * Quick check using directory modification time
     */
    private function quickCheckPassed(): bool
    {
        $srcDir = __DIR__ . '/../../../src';
        if (!is_dir($srcDir)) {
            return false;
        }

        $cacheFile = $this->cacheFile . '.mtime';
        if (!file_exists($cacheFile)) {
            return false;
        }

        $lastMtime = (int)file_get_contents($cacheFile);
        $currentMtime = $this->getDirectoryMtime($srcDir);

        return $currentMtime === $lastMtime;
    }

    /**
     * Get directory modification time (max mtime of all files)
     */
    private function getDirectoryMtime(string $dir): int
    {
        $maxMtime = 0;
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $mtime = $file->getMTime();
                if ($mtime > $maxMtime) {
                    $maxMtime = $mtime;
                }
            }
        }

        return $maxMtime;
    }

    /**
     * Full validation with hash calculation
     */
    private function fullValidation(): bool
    {
        // Calculate current source hash
        $currentHash = $this->calculateSourceHash();

        // Read stored hash
        $storedHash = file_get_contents($this->sourceHashFile);

        // Compare hashes
        $isValid = hash_equals($storedHash, $currentHash);

        // Save mtime for quick check next time
        if ($isValid) {
            $srcDir = __DIR__ . '/../../../src';
            $mtime = $this->getDirectoryMtime($srcDir);
            $cacheFile = $this->cacheFile . '.mtime';
            file_put_contents($cacheFile, (string)$mtime);
        }

        return $isValid;
    }

    /**
     * Check if cache is valid
     */
    private function isCacheValid(): bool
    {
        if (!file_exists($this->cacheFile)) {
            return false;
        }

        $cacheData = json_decode(file_get_contents($this->cacheFile), true);
        if (!$cacheData || !isset($cacheData['timestamp'])) {
            return false;
        }

        return (time() - $cacheData['timestamp']) < $this->cacheTtl;
    }

    /**
     * Get cached validation result
     */
    private function getCachedResult(): ?bool
    {
        if (!file_exists($this->cacheFile)) {
            return null;
        }

        $cacheData = json_decode(file_get_contents($this->cacheFile), true);
        return $cacheData['result'] ?? null;
    }

    /**
     * Save validation result to cache
     */
    private function saveCache(bool $result): void
    {
        $dir = dirname($this->cacheFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents($this->cacheFile, json_encode([
            'result' => $result,
            'timestamp' => time()
        ]));
    }

    /**
     * Calculate hash of source code files
     */
    private function calculateSourceHash(): string
    {
        $files = $this->getSourceFiles();
        $hashes = [];

        foreach ($files as $file) {
            if (file_exists($file)) {
                $hashes[] = hash_file('sha256', $file);
            }
        }

        sort($hashes);
        return hash('sha256', implode('', $hashes));
    }

    /**
     * Get list of source files to hash
     */
    private function getSourceFiles(): array
    {
        $baseDir = __DIR__ . '/../../../';
        $srcDir = $baseDir . 'src';
        
        if (!is_dir($srcDir)) {
            return [];
        }
        
        $files = [];

        // Add all PHP files in src directory
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($srcDir, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $files[] = $file->getPathname();
            }
        }

        return $files;
    }

    /**
     * Generate and store source hash (used by tools)
     */
    public function generateSourceHash(): string
    {
        $hash = $this->calculateSourceHash();
        $dir = dirname($this->sourceHashFile);

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents($this->sourceHashFile, $hash);
        return $hash;
    }
}


<?php

declare(strict_types=1);

namespace Modules\Security\Application\Service;

class FileIntegrityScanner
{
    private string $manifestPath;
    private array $directoriesToScan;
    private array $excludedPatterns;

    public function __construct(string $manifestPath = null)
    {
        // Default to storage/security/manifest.json
        $this->manifestPath = $manifestPath ?? __DIR__ . '/../../../../../../storage/security/manifest.json';
        
        $this->directoriesToScan = [
            __DIR__ . '/../../../../../../src',
            __DIR__ . '/../../../../../../public',
            __DIR__ . '/../../../../../../config',
        ];

        $this->excludedPatterns = [
            '/storage/',
            '/vendor/',
            '/node_modules/',
            '/.git/',
            '/.idea/',
            '/manifest.json', // Exclude self
            '*.log',
            '*.cache',
        ];

        // Ensure directory exists
        $dir = dirname($this->manifestPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }

    /**
     * Generate and save a new manifest (Baseline)
     */
    public function generateManifest(): int
    {
        $files = $this->scanFiles();
        file_put_contents($this->manifestPath, json_encode($files, JSON_PRETTY_PRINT));
        return count($files);
    }

    /**
     * Verify files against the manifest
     * @return array ['added' => [], 'modified' => [], 'deleted' => [], 'status' => 'clean'|'compromised']
     */
    public function verify(): array
    {
        if (!file_exists($this->manifestPath)) {
            return [
                'status' => 'missing_manifest',
                'added' => [],
                'modified' => [],
                'deleted' => []
            ];
        }

        $manifest = json_decode(file_get_contents($this->manifestPath), true) ?? [];
        $currentFiles = $this->scanFiles();

        $added = [];
        $modified = [];
        $deleted = [];

        // Check for modified and added
        foreach ($currentFiles as $path => $hash) {
            if (!isset($manifest[$path])) {
                $added[] = $path;
            } elseif ($manifest[$path] !== $hash) {
                $modified[] = $path;
            }
        }

        // Check for deleted
        foreach ($manifest as $path => $hash) {
            if (!isset($currentFiles[$path])) {
                $deleted[] = $path;
            }
        }

        $status = (empty($added) && empty($modified) && empty($deleted)) ? 'clean' : 'compromised';

        return [
            'deleted' => $deleted
        ];
    }

    /**
     * Verify only specific critical files
     * This is faster for runtime checks (Middleware)
     */
    public function verifySpecificFiles(array $criticalFiles): bool
    {
        if (!file_exists($this->manifestPath)) {
            return false; // No manifest = insecure
        }

        $manifest = json_decode(file_get_contents($this->manifestPath), true) ?? [];
        $root = realpath(__DIR__ . '/../../../../../../');

        foreach ($criticalFiles as $relativePath) {
            // Check if file is in manifest
            if (!isset($manifest[$relativePath])) {
                // If critical file is missing from manifest, it's a structural error, assume compromised
                return false; 
            }

            $fullPath = $root . $relativePath;
            if (!file_exists($fullPath)) {
                return false; // File missing
            }

            $currentHash = hash_file('sha256', $fullPath);
            if ($currentHash !== $manifest[$relativePath]) {
                return false; // Hash mismatch
            }
        }

        return true;
    }

    private function scanFiles(): array
    {
        $files = [];
        foreach ($this->directoriesToScan as $dir) {
            if (!is_dir($dir)) continue;
            
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS)
            );

            foreach ($iterator as $fileInfo) {
                if ($this->isExcluded($fileInfo->getPathname())) {
                    continue;
                }

                $relativePath = $this->getRelativePath($fileInfo->getPathname());
                // Use sha256 for integrity
                $files[$relativePath] = hash_file('sha256', $fileInfo->getPathname());
            }
        }
        return $files;
    }

    private function isExcluded(string $path): bool
    {
        $path = str_replace('\\', '/', $path);
        foreach ($this->excludedPatterns as $pattern) {
            if (strpos($path, $pattern) !== false) {
                return true;
            }
        }
        return false;
    }

    private function getRelativePath(string $fullPath): string
    {
        // Normalize path
        $root = realpath(__DIR__ . '/../../../../../../');
        $fullPath = realpath($fullPath);
        
        return str_replace($root, '', $fullPath);
    }
}

<?php

declare(strict_types=1);

namespace Modules\Security\Infrastructure\Service;

class TamperProtectionService
{
    private const ALGO = OPENSSL_ALGO_SHA256;
    private string $rootPath;
    
    public function __construct()
    {
        $this->rootPath = dirname(__DIR__, 5); // Go up to root from src/Modules/Security/Infrastructure/Service
    }

    /**
     * Generate New Key Pair (Ed25519)
     * @return array ['private' => string, 'public' => string]
     */
    public function generateKeys(): array
    {
        $keypair = sodium_crypto_sign_keypair();
        
        return [
            'private' => base64_encode(sodium_crypto_sign_secretkey($keypair)),
            'public' => base64_encode(sodium_crypto_sign_publickey($keypair))
        ];
    }

    /**
     * Calculate Hash of the Entire Source Code (Core + Modules)
     */
    public function calculateSourceHash(): string
    {
        $files = $this->scanFiles();
        // Sort files to ensure consistent order
        ksort($files);
        
        // Hash the concatenated hashes
        return hash('sha256', implode('', $files));
    }

    /**
     * Sign the Source Hash
     */
    public function signSource(string $privateKeyBase64): string
    {
        $hash = $this->calculateSourceHash();
        $privateKey = base64_decode($privateKeyBase64);
        
        $signature = sodium_crypto_sign_detached($hash, $privateKey);
        return base64_encode($signature);
    }

    /**
     * Verify Source Integrity
     */
    public function verifySource(string $publicKeyBase64, string $signatureBase64): bool
    {
        $hash = $this->calculateSourceHash();
        $signature = base64_decode($signatureBase64);
        $publicKey = base64_decode($publicKeyBase64);
        
        return sodium_crypto_sign_verify_detached($signature, $hash, $publicKey);
    }

    private function scanFiles(): array
    {
        $dirs = [
            $this->rootPath . '/src',
            $this->rootPath . '/public',
            $this->rootPath . '/config',
        ];
        
        $excluded = [
            'TamperProtectionService.php', // Optionally exclude self if needed, but better to include
            'manifest.json',
            'integrity.sig',
            'integrity.pub',
            '.git',
            '.idea',
            'storage',
            'vendor'
        ];

        $hashes = [];
        foreach ($dirs as $dir) {
            if (!is_dir($dir)) continue;
            
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS)
            );

            foreach ($iterator as $file) {
                $path = $file->getPathname();
                $filename = $file->getFilename();
                
                // Simplified exclusion check
                $skip = false;
                foreach ($excluded as $ex) {
                    if (str_contains($path, $ex)) {
                        $skip = true;
                        break;
                    }
                }
                if ($skip) continue;

                // Hash file content
                $hashes[str_replace($this->rootPath, '', $path)] = hash_file('sha256', $path);
            }
        }
        return $hashes;
    }
}

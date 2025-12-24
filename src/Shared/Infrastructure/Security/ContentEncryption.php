<?php

declare(strict_types=1);

namespace Shared\Infrastructure\Security;

use Shared\Infrastructure\Config\AppConfig;

class ContentEncryption
{
    private string $keyFile;
    private ?string $encryptionKey = null;
    private bool $keyLoaded = false;

    public function __construct()
    {
        $config = AppConfig::getInstance();
        $this->keyFile = $config->get('encryption.key_file');
        // Don't load key in constructor - load it lazily when needed
    }

    /**
     * Load encryption key from file (lazy loading)
     */
    private function loadKey(): void
    {
        if ($this->keyLoaded) {
            return;
        }

        if (!file_exists($this->keyFile)) {
            throw new \RuntimeException('Encryption key file not found. Please generate key using tools page.');
        }

        $key = file_get_contents($this->keyFile);
        if ($key === false || strlen($key) < 32) {
            throw new \RuntimeException('Invalid encryption key file.');
        }

        $this->encryptionKey = trim($key);
        $this->keyLoaded = true;
    }

    /**
     * Encrypt content
     */
    public function encrypt(string $content): string
    {
        $this->loadKey();
        
        if ($this->encryptionKey === null) {
            throw new \RuntimeException('Encryption key not loaded.');
        }

        $ivLength = openssl_cipher_iv_length('AES-256-GCM');
        $iv = openssl_random_pseudo_bytes($ivLength);
        
        $encrypted = openssl_encrypt(
            $content,
            'AES-256-GCM',
            $this->encryptionKey,
            OPENSSL_RAW_DATA,
            $iv,
            $tag
        );

        if ($encrypted === false) {
            throw new \RuntimeException('Encryption failed.');
        }

        // Combine IV, tag, and encrypted data
        return base64_encode($iv . $tag . $encrypted);
    }

    /**
     * Decrypt content
     */
    public function decrypt(string $encryptedContent): string
    {
        $this->loadKey();
        
        if ($this->encryptionKey === null) {
            throw new \RuntimeException('Encryption key not loaded.');
        }

        $data = base64_decode($encryptedContent, true);
        if ($data === false) {
            throw new \RuntimeException('Invalid encrypted data format.');
        }

        $ivLength = openssl_cipher_iv_length('AES-256-GCM');
        $tagLength = 16; // GCM tag is always 16 bytes

        if (strlen($data) < $ivLength + $tagLength) {
            throw new \RuntimeException('Encrypted data too short.');
        }

        $iv = substr($data, 0, $ivLength);
        $tag = substr($data, $ivLength, $tagLength);
        $encrypted = substr($data, $ivLength + $tagLength);

        $decrypted = openssl_decrypt(
            $encrypted,
            'AES-256-GCM',
            $this->encryptionKey,
            OPENSSL_RAW_DATA,
            $iv,
            $tag
        );

        if ($decrypted === false) {
            throw new \RuntimeException('Decryption failed.');
        }

        return $decrypted;
    }

    /**
     * Generate a new encryption key
     */
    public static function generateKey(): string
    {
        return base64_encode(random_bytes(32));
    }

    /**
     * Save encryption key to file
     */
    public function saveKey(string $key): void
    {
        $dir = dirname($this->keyFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents($this->keyFile, $key, LOCK_EX);
        chmod($this->keyFile, 0600);
        
        // Update the loaded key if it was already loaded
        $this->encryptionKey = trim($key);
        $this->keyLoaded = true;
    }
    
    /**
     * Get the key file path (for tools page)
     */
    public function getKeyFile(): string
    {
        return $this->keyFile;
    }
}


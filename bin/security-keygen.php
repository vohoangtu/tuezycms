<?php
// bin/security-keygen.php

require __DIR__ . '/../vendor/autoload.php';

use Modules\Security\Infrastructure\Service\TamperProtectionService;

$service = new TamperProtectionService();
$keys = $service->generateKeys();

// Ensure storage dir exists
$storageKeys = __DIR__ . '/../storage/security/keys';
if (!is_dir($storageKeys)) {
    mkdir($storageKeys, 0700, true);
}

// Save Keys
file_put_contents($storageKeys . '/private.pem', $keys['private']);
file_put_contents($storageKeys . '/public.pem', $keys['public']);

echo "Keys generated successfully!\n";
echo "Private Key: {$storageKeys}/private.pem (KEEP SAFE!)\n";
echo "Public Key: {$storageKeys}/public.pem (Deploy this)\n";

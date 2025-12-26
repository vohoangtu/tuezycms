<?php
// bin/security-sign.php

require __DIR__ . '/../vendor/autoload.php';

use Modules\Security\Infrastructure\Service\TamperProtectionService;

$service = new TamperProtectionService();

// Load Private Key
$keyPath = __DIR__ . '/../storage/security/keys/private.pem';
if (!file_exists($keyPath)) {
    die("Error: Private key not found at $keyPath. Run keygen first.\n");
}

$privateKey = file_get_contents($keyPath);

echo "Calculating hash and signing source code...\n";
$signature = $service->signSource($privateKey);

// Save Signature to Root (for deployment)
file_put_contents(__DIR__ . '/../integrity.sig', $signature);

// Copy Public Key to Root (for deployment verification)
copy(__DIR__ . '/../storage/security/keys/public.pem', __DIR__ . '/../integrity.pub');

echo "Source Code Signed!\n";
echo "Signature: integrity.sig\n";
echo "Public Key: integrity.pub\n";

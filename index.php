<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use Shared\Infrastructure\Security\KeyValidator;
use Shared\Infrastructure\Config\AppConfig;

// Validate source code integrity
$keyValidator = new KeyValidator();
if (!$keyValidator->validateSourceIntegrity()) {
    http_response_code(403);
    die('Source code integrity check failed. System disabled.');
}

// Load configuration
$config = AppConfig::getInstance();

// Route to appropriate handler
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// Remove query string
$path = parse_url($requestUri, PHP_URL_PATH);

// Route to admin or public
if (str_starts_with($path, '/admin')) {
    require __DIR__ . '/public/admin/index.php';
} elseif (str_starts_with($path, '/tools')) {
    require __DIR__ . '/public/tools/index.php';
} else {
    require __DIR__ . '/public/index.php';
}


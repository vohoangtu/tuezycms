<?php

declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

use Shared\Infrastructure\Storage\LocalFileStorage;

$storage = new LocalFileStorage();
$requestPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$requestPath = str_replace('/uploads', '', $requestPath);
$requestPath = ltrim($requestPath, '/');

// Security: prevent path traversal
if (strpos($requestPath, '..') !== false || strpos($requestPath, '/') === 0) {
    http_response_code(403);
    die('Forbidden');
}

$filePath = $storage->getPath($requestPath);

// Check if file exists
if (!file_exists($filePath) || !is_file($filePath)) {
    http_response_code(404);
    die('File not found');
}

// Get file info
$mimeType = mime_content_type($filePath);
$fileSize = filesize($filePath);

// Set headers
header('Content-Type: ' . $mimeType);
header('Content-Length: ' . $fileSize);
header('Cache-Control: public, max-age=31536000');
header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 31536000) . ' GMT');

// Security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');

// Output file
readfile($filePath);
exit;


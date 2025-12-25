<?php

require_once __DIR__ . '/vendor/autoload.php';

use Shared\Infrastructure\Cache\Cache;

echo "Clearing cache...\n";
if (Cache::flush()) {
    echo "Cache cleared successfully.\n";
} else {
    echo "Failed to clear cache.\n";
}

<?php

// Test the Route class parameter extraction
require_once '../vendor/autoload.php';

use Core\Routing\Route;

// Create atest route with {id:\\d+}
$route = new Route('GET', '/api/roles/{id:\\d+}', function() {});

// Test if it matches
$path = '/api/roles/5';

echo "Testing Route parameter extraction:\n";
echo "Route: " . $route->getPath() . "\n";
echo "Path: {$path}\n\n";

if ($route->matches($path)) {
    echo "✓ Route matches!\n";
    $params = $route->extractParameters($path);
    echo "Parameters extracted: " . print_r($params, true) . "\n";
} else {
    echo "✗ Route does not match\n";
}

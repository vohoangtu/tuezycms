<?php

/**
 * Test Cache System
 * Run: php test-cache.php
 */

require_once __DIR__ . '/vendor/autoload.php';

use Shared\Infrastructure\Cache\Cache;

echo "=== Testing Cache System ===\n\n";

try {
    // Test 1: Set and Get
    echo "Test 1: Set and Get\n";
    Cache::set('test_key', 'test_value', 60);
    $value = Cache::get('test_key');
    echo "Value: " . ($value === 'test_value' ? '✓ PASS' : '✗ FAIL') . "\n\n";

    // Test 2: Has
    echo "Test 2: Has\n";
    $has = Cache::has('test_key');
    echo "Has key: " . ($has ? '✓ PASS' : '✗ FAIL') . "\n\n";

    // Test 3: Get with default
    echo "Test 3: Get with default\n";
    $value = Cache::get('non_existent_key', 'default_value');
    echo "Default value: " . ($value === 'default_value' ? '✓ PASS' : '✗ FAIL') . "\n\n";

    // Test 4: Delete
    echo "Test 4: Delete\n";
    Cache::delete('test_key');
    $has = Cache::has('test_key');
    echo "Deleted: " . (!$has ? '✓ PASS' : '✗ FAIL') . "\n\n";

    // Test 5: Set Multiple
    echo "Test 5: Set Multiple\n";
    Cache::setMultiple([
        'key1' => 'value1',
        'key2' => 'value2',
        'key3' => 'value3'
    ], 60);
    $values = Cache::getMultiple(['key1', 'key2', 'key3']);
    $pass = $values['key1'] === 'value1' && $values['key2'] === 'value2' && $values['key3'] === 'value3';
    echo "Multiple values: " . ($pass ? '✓ PASS' : '✗ FAIL') . "\n\n";

    // Test 6: Delete Multiple
    echo "Test 6: Delete Multiple\n";
    Cache::deleteMultiple(['key1', 'key2', 'key3']);
    $has1 = Cache::has('key1');
    $has2 = Cache::has('key2');
    $has3 = Cache::has('key3');
    echo "Deleted multiple: " . (!$has1 && !$has2 && !$has3 ? '✓ PASS' : '✗ FAIL') . "\n\n";

    // Test 7: Remember
    echo "Test 7: Remember\n";
    $callCount = 0;
    $value1 = Cache::remember('expensive_key', 60, function() use (&$callCount) {
        $callCount++;
        return 'expensive_value';
    });
    $value2 = Cache::remember('expensive_key', 60, function() use (&$callCount) {
        $callCount++;
        return 'expensive_value';
    });
    echo "Remember (callback called " . $callCount . " time): " . ($callCount === 1 ? '✓ PASS' : '✗ FAIL') . "\n\n";

    // Test 8: Cache complex data
    echo "Test 8: Cache complex data\n";
    $complexData = [
        'id' => 1,
        'name' => 'Test User',
        'roles' => ['admin', 'editor'],
        'meta' => ['created_at' => date('Y-m-d H:i:s')]
    ];
    Cache::set('complex_key', $complexData, 60);
    $retrieved = Cache::get('complex_key');
    $pass = $retrieved['id'] === 1 && $retrieved['name'] === 'Test User';
    echo "Complex data: " . ($pass ? '✓ PASS' : '✗ FAIL') . "\n\n";

    // Test 9: TTL expiration (short TTL)
    echo "Test 9: TTL expiration\n";
    Cache::set('short_ttl_key', 'value', 1); // 1 second
    echo "Set with 1s TTL, waiting 2 seconds...\n";
    sleep(2);
    $value = Cache::get('short_ttl_key');
    echo "Expired: " . ($value === null ? '✓ PASS' : '✗ FAIL') . "\n\n";

    // Test 10: Clear all
    echo "Test 10: Clear all\n";
    Cache::set('clear_test_1', 'value1', 60);
    Cache::set('clear_test_2', 'value2', 60);
    Cache::clear();
    $has1 = Cache::has('clear_test_1');
    $has2 = Cache::has('clear_test_2');
    echo "Cleared all: " . (!$has1 && !$has2 ? '✓ PASS' : '✗ FAIL') . "\n\n";

    echo "=== All tests passed! ===\n";

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

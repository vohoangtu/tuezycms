<?php
require_once __DIR__ . '/vendor/autoload.php';

use Shared\Infrastructure\Helper\ModuleHelper;

echo "Testing ModuleHelper...\n\n";

// Test 1: Check if system modules are enabled
echo "=== Test 1: System Modules (should be enabled) ===\n";
$systemModules = ['user_management', 'role_management', 'product_management'];
foreach ($systemModules as $module) {
    $isEnabled = ModuleHelper::isEnabled($module);
    echo "{$module}: " . ($isEnabled ? '✓ Enabled' : '✗ Disabled') . "\n";
}

// Test 2: Check if optional modules are disabled
echo "\n=== Test 2: Optional Modules (should be disabled) ===\n";
$optionalModules = ['branch_management', 'department_management', 'seo_tools'];
foreach ($optionalModules as $module) {
    $isEnabled = ModuleHelper::isEnabled($module);
    echo "{$module}: " . ($isEnabled ? '✓ Enabled' : '✗ Disabled') . "\n";
}

// Test 3: Get all enabled modules
echo "\n=== Test 3: All Enabled Modules ===\n";
$enabledModules = ModuleHelper::getEnabledModules();
echo "Total enabled: " . count($enabledModules) . "\n";
foreach ($enabledModules as $module) {
    echo "  - {$module->getName()} ({$module->getDisplayName()})\n";
}

// Test 4: Get modules by category
echo "\n=== Test 4: Modules by Category ===\n";
$categories = ['user', 'product', 'content', 'system'];
foreach ($categories as $category) {
    $modules = ModuleHelper::getModulesByCategory($category);
    echo "{$category}: " . count($modules) . " modules\n";
    foreach ($modules as $module) {
        $status = $module->isEnabled() ? 'ON' : 'OFF';
        echo "  - {$module->getDisplayName()} [{$status}]\n";
    }
}

// Test 5: Get module config
echo "\n=== Test 5: Module Config ===\n";
$config = ModuleHelper::getConfig('user_management');
echo "user_management config: " . json_encode($config) . "\n";

echo "\n✓ All tests completed!\n";

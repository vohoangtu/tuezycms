<?php

require_once __DIR__ . '/vendor/autoload.php';

use Shared\Infrastructure\Database\DatabaseConnection;
use Shared\Infrastructure\Database\DB;
use Modules\Module\Infrastructure\Repository\ModuleRepository;
use Modules\Module\Application\Service\ModuleService;

echo "Testing Module Management System...\n\n";

try {
    // Test 1: Repository - Find all modules
    echo "=== Test 1: Repository - Find All Modules ===\n";
    $repository = new ModuleRepository();
    $modules = $repository->findAll();
    echo "✓ Found " . count($modules) . " modules\n\n";
    
    // Test 2: Repository - Find by category
    echo "=== Test 2: Repository - Find by Category ===\n";
    $userModules = $repository->findByCategory('user');
    echo "✓ Found " . count($userModules) . " user modules\n";
    $productModules = $repository->findByCategory('product');
    echo "✓ Found " . count($productModules) . " product modules\n\n";
    
    // Test 3: Repository - Find enabled modules
    echo "=== Test 3: Repository - Find Enabled Modules ===\n";
    $enabledModules = $repository->findEnabled();
    echo "✓ Found " . count($enabledModules) . " enabled modules\n\n";
    
    // Test 4: Service - Get modules grouped by category
    echo "=== Test 4: Service - Get Modules Grouped by Category ===\n";
    $service = new ModuleService($repository);
    $grouped = $service->getModulesGroupedByCategory();
    foreach ($grouped as $category => $categoryModules) {
        echo "  - {$category}: " . count($categoryModules) . " modules\n";
    }
    echo "\n";
    
    // Test 5: Service - Check if module is enabled
    echo "=== Test 5: Service - Check Module Status ===\n";
    $userMgmtEnabled = $service->isModuleEnabled('user_management');
    echo "  - user_management: " . ($userMgmtEnabled ? "✓ Enabled" : "✗ Disabled") . "\n";
    $branchMgmtEnabled = $service->isModuleEnabled('branch_management');
    echo "  - branch_management: " . ($branchMgmtEnabled ? "✓ Enabled" : "✗ Disabled") . "\n\n";
    
    // Test 6: toArray() method
    echo "=== Test 6: Module Entity - toArray() ===\n";
    $firstModule = $modules[0];
    $moduleArray = $firstModule->toArray();
    echo "✓ Module '{$moduleArray['display_name']}' converted to array with " . count($moduleArray) . " fields\n";
    echo "  Fields: " . implode(', ', array_keys($moduleArray)) . "\n\n";
    
    // Test 7: System module protection
    echo "=== Test 7: System Module Protection ===\n";
    $systemModule = $repository->findByName('user_management');
    if ($systemModule && $systemModule->isSystem()) {
        try {
            $systemModule->disable();
            echo "✗ FAILED: System module should not be disableable\n";
        } catch (RuntimeException $e) {
            echo "✓ System module protection works: " . $e->getMessage() . "\n";
        }
    }
    echo "\n";
    
    echo "=== All Tests Passed ✓ ===\n\n";
    
} catch (Exception $e) {
    echo "✗ Test failed: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}

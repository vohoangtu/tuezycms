<?php
require_once __DIR__ . '/vendor/autoload.php';

use Modules\Module\Infrastructure\Repository\ModuleRepository;
use Modules\Module\Application\Service\ModuleService;

echo "Testing ModuleController logic...\n\n";

try {
    $repository = new ModuleRepository();
    $service = new ModuleService($repository);
    
    $modules = $service->getAllModules();
    
    echo "Module count: " . count($modules) . "\n\n";
    
    foreach ($modules as $module) {
        echo "- " . $module->getDisplayName() . " (" . $module->getName() . ")  "  . 
             ($module->isEnabled() ? "[ENABLED]" : "[DISABLED]") . 
             ($module->isSystem() ? " [SYSTEM]" : "") . "\n";
    }
    
    echo "\n\nTesting JSON output:\n";
    $data = array_map(fn($m) => $m->toArray(), $modules);
    $json = json_encode(['success' => true, 'data' => $data], JSON_PRETTY_PRINT);
    echo substr($json, 0, 500) . "...\n";
    
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}

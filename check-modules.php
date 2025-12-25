<?php
require_once __DIR__ . '/vendor/autoload.php';

use Shared\Infrastructure\Database\DatabaseConnection;

echo "Checking modules table...\n";

try {
    $pdo = DatabaseConnection::getInstance();
    
    $stmt = $pdo->query("SELECT * FROM modules");
    $modules = $stmt->fetchAll(PDO::FETCH_OBJ);
    
    echo "Module count: " . count($modules) . "\n\n";
    
    foreach ($modules as $module) {
        echo "ID: {$module->id}\n";
        echo "Name: {$module->name}\n";
        echo "Display: {$module->display_name}\n";
        echo "Enabled: " . ($module->is_enabled ? 'Yes' : 'No') . "\n";
        echo "System: " . ($module->is_system ? 'Yes' : 'No') . "\n";
        echo "---\n";
    }
    
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

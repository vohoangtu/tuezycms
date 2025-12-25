<?php

require_once __DIR__ . '/vendor/autoload.php';

use Shared\Infrastructure\Database\DatabaseConnection;

echo "Checking database migration status...\n\n";

try {
    $pdo = DatabaseConnection::getInstance();
    
    // Check if modules table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'modules'");
    $result = $stmt->fetch();
    
    if ($result) {
        echo "✓ Modules table EXISTS\n";
        
        // Count modules
        $stmt = $pdo->query('SELECT COUNT(*) as count FROM modules');
        $count = $stmt->fetch();
        echo "✓ Total modules: " . $count['count'] . "\n\n";
        
        // Show modules
        echo "Current modules:\n";
        $stmt = $pdo->query('SELECT id, name, display_name, category, is_enabled, is_system FROM modules ORDER BY sort_order');
        $modules = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($modules as $module) {
            $status = $module['is_enabled'] ? '✓ Enabled' : '✗ Disabled';
            $type = $module['is_system'] ? '[SYSTEM]' : '[OPTIONAL]';
            echo sprintf(
                "  %d. %-30s %-10s %s %s\n",
                $module['id'],
                $module['display_name'],
                "({$module['category']})",
                $type,
                $status
            );
        }
    } else {
        echo "✗ Modules table DOES NOT EXIST\n";
        echo "\nTo create the table, run:\n";
        echo "  php run-module-migration.php\n";
    }
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}

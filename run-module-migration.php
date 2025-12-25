<?php

require_once __DIR__ . '/vendor/autoload.php';

use Shared\Infrastructure\Database\DatabaseConnection;

echo "Running Module System Migration...\n";

try {
    // Read the migration file
    $sql = file_get_contents(__DIR__ . '/database/migrations/add_module_system.sql');
    
    // Get PDO connection
    $pdo = DatabaseConnection::getInstance();
    
    // Execute the migration
    $pdo->exec($sql);
    
    echo "✓ Migration completed successfully!\n";
    echo "✓ Modules table created\n";
    echo "✓ System modules inserted\n";
    echo "✓ Optional modules inserted\n";
    
} catch (\Exception $e) {
    echo "✗ Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}

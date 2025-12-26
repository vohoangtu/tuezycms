<?php

require_once __DIR__ . '/vendor/autoload.php';

use Shared\Infrastructure\Database\DatabaseConnection;

echo "Running Security Module Migration...\n";

try {
    // Read the migration file
    $sql = file_get_contents(__DIR__ . '/database/migrations/add_security_tables.sql');
    
    // Get PDO connection
    $pdo = DatabaseConnection::getInstance();
    
    // Execute the migration check/run multiple statements
    // PDO::exec usually runs one statement. We might need to split.
    // However, existing run-module-migration.php used exec($sql) directly.
    // MySQL PDO can often run multiple statements if configured, but default might not.
    // Let's try splitting by ; just in case, or assume existing Infrastructure supports it.
    // Given the previous file used exec($sql) on a file that likely had multiple inserts, let's try direct exec first.
    // If it fails, I'll split.
    
    $pdo->exec($sql);
    
    echo "✓ Migration completed successfully!\n";
    echo "✓ Table 'security_logs' created/checked.\n";
    echo "✓ Table 'blocked_ips' created/checked.\n";
    
} catch (\Exception $e) {
    echo "✗ Migration failed: " . $e->getMessage() . "\n";
    // Fallback: Split by delimiter if syntax error implies multi-statement issue
    if (str_contains($e->getMessage(), 'syntax')) {
         echo "Attempting to split statements...\n";
         try {
             $statements = array_filter(array_map('trim', explode(';', $sql)));
             foreach ($statements as $stmt) {
                 if (!empty($stmt)) {
                     $pdo->exec($stmt);
                 }
             }
             echo "✓ Migration completed via split statements!\n";
         } catch (\Exception $e2) {
             echo "✗ Split execution failed: " . $e2->getMessage() . "\n";
             exit(1);
         }
    } else {
        exit(1);
    }
}

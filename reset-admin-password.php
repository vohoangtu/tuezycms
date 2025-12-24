<?php

/**
 * Reset admin password
 * Run: php reset-admin-password.php
 */

require_once __DIR__ . '/vendor/autoload.php';

use Shared\Infrastructure\Database\DB;

echo "=== Reset Admin Password ===\n\n";

try {
    // Check if admin user exists
    $admin = DB::table('users')
        ->where('email', '=', 'admin@tuzycms.com')
        ->first();
    
    if (!$admin) {
        echo "Admin user not found. Creating new admin user...\n";
        
        // Create admin user
        $id = DB::table('users')->insert([
            'email' => 'admin@tuzycms.com',
            'password_hash' => password_hash('admin123', PASSWORD_BCRYPT),
            'full_name' => 'Administrator',
            'role' => 'admin',
            'is_active' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        echo "✓ Admin user created with ID: {$id}\n";
    } else {
        echo "Admin user found (ID: {$admin['id']})\n";
        echo "Resetting password...\n";
        
        // Update password
        DB::table('users')
            ->where('id', '=', $admin['id'])
            ->update([
                'password_hash' => password_hash('admin123', PASSWORD_BCRYPT),
                'is_active' => 1,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        
        echo "✓ Password reset successfully\n";
    }
    
    echo "\nCredentials:\n";
    echo "Email: admin@tuzycms.com\n";
    echo "Password: admin123\n";
    echo "\nYou can now login at: http://localhost:88/admin/login\n";
    
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

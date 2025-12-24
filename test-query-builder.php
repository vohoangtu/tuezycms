<?php

/**
 * Test Query Builder
 * Run: php test-query-builder.php
 */

require_once __DIR__ . '/vendor/autoload.php';

use Shared\Infrastructure\Database\DB;

echo "=== Testing Query Builder ===\n\n";

try {
    // Test 1: Select all
    echo "Test 1: Select all users\n";
    $users = DB::table('users')->get();
    echo "Found " . count($users) . " users\n\n";

    // Test 2: Select with where
    echo "Test 2: Select active users\n";
    $activeUsers = DB::table('users')
        ->where('is_active', '=', 1)
        ->get();
    echo "Found " . count($activeUsers) . " active users\n\n";

    // Test 3: Select with limit and order
    echo "Test 3: Select 5 latest users\n";
    $latestUsers = DB::table('users')
        ->orderBy('created_at', 'DESC')
        ->limit(5)
        ->get();
    echo "Found " . count($latestUsers) . " latest users\n\n";

    // Test 4: Find by ID
    echo "Test 4: Find user by ID\n";
    $user = DB::table('users')->find(1);
    if ($user) {
        echo "Found user: " . ($user['email'] ?? 'N/A') . "\n\n";
    } else {
        echo "User not found\n\n";
    }

    // Test 5: Count
    echo "Test 5: Count users\n";
    $count = DB::table('users')->count();
    echo "Total users: {$count}\n\n";

    // Test 6: Insert (commented out to avoid creating test data)
    /*
    echo "Test 6: Insert user\n";
    $id = DB::table('users')->insert([
        'email' => 'test@example.com',
        'password_hash' => password_hash('password', PASSWORD_BCRYPT),
        'full_name' => 'Test User',
        'is_active' => 1,
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ]);
    echo "Inserted user with ID: {$id}\n\n";
    */

    // Test 7: Update (commented out)
    /*
    echo "Test 7: Update user\n";
    $affected = DB::table('users')
        ->where('id', '=', $id)
        ->update(['full_name' => 'Updated User']);
    echo "Updated {$affected} rows\n\n";
    */

    // Test 8: Delete (commented out)
    /*
    echo "Test 8: Delete user\n";
    $affected = DB::table('users')
        ->where('id', '=', $id)
        ->delete();
    echo "Deleted {$affected} rows\n\n";
    */

    // Test 9: Join
    echo "Test 9: Join users with roles\n";
    $usersWithRoles = DB::table('users as u')
        ->join('user_roles as ur', 'u.id', '=', 'ur.user_id')
        ->join('roles as r', 'ur.role_id', '=', 'r.id')
        ->select('u.id', 'u.email', 'r.display_name as role')
        ->get();
    echo "Found " . count($usersWithRoles) . " users with roles\n\n";

    // Test 10: Transaction
    echo "Test 10: Transaction test\n";
    DB::transaction(function() {
        echo "Inside transaction\n";
        // Perform operations
        return true;
    });
    echo "Transaction completed\n\n";

    echo "=== All tests passed! ===\n";

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

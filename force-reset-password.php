<?php

/**
 * Force reset admin password with proper hash
 */

require_once __DIR__ . '/vendor/autoload.php';

use Shared\Infrastructure\Database\DatabaseConnection;

echo "=== Force Reset Admin Password ===\n\n";

try {
    $pdo = DatabaseConnection::getInstance();
    
    $email = 'admin@tuzycms.com';
    $password = 'admin123';
    $hash = password_hash($password, PASSWORD_BCRYPT);
    
    echo "New password hash: {$hash}\n\n";
    
    // Update directly with PDO
    $stmt = $pdo->prepare("
        UPDATE users 
        SET password_hash = :hash, 
            is_active = 1,
            updated_at = :updated_at
        WHERE email = :email
    ");
    
    $stmt->execute([
        ':hash' => $hash,
        ':email' => $email,
        ':updated_at' => date('Y-m-d H:i:s')
    ]);
    
    echo "✓ Password updated\n\n";
    
    // Verify
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        echo "User info:\n";
        echo "  ID: {$user['id']}\n";
        echo "  Email: {$user['email']}\n";
        echo "  Name: {$user['full_name']}\n";
        echo "  Active: {$user['is_active']}\n";
        echo "  Hash: " . substr($user['password_hash'], 0, 30) . "...\n\n";
        
        // Test password
        if (password_verify($password, $user['password_hash'])) {
            echo "✓ Password verification: SUCCESS\n\n";
            echo "Credentials:\n";
            echo "  Email: {$email}\n";
            echo "  Password: {$password}\n";
        } else {
            echo "✗ Password verification: FAILED\n";
        }
    }
    
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

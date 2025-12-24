<?php

/**
 * Test login functionality
 * Run: php test-login.php
 */

require_once __DIR__ . '/vendor/autoload.php';

use Modules\User\Infrastructure\Repository\UserRepository;
use Modules\User\Application\Service\AuthService;

echo "=== Test Login ===\n\n";

try {
    $userRepo = new UserRepository();
    $authService = new AuthService($userRepo);
    
    $email = 'admin@tuzycms.com';
    $password = 'admin123';
    
    echo "Testing login with:\n";
    echo "Email: {$email}\n";
    echo "Password: {$password}\n\n";
    
    // Test 1: Find user
    echo "Test 1: Find user by email\n";
    $user = $userRepo->findByEmail($email);
    if ($user) {
        echo "✓ User found: {$user->getEmail()}\n";
        echo "  ID: {$user->getId()}\n";
        echo "  Name: {$user->getFullName()}\n";
        echo "  Active: " . ($user->isActive() ? 'Yes' : 'No') . "\n";
        echo "  Password hash: " . substr($user->getPasswordHash(), 0, 20) . "...\n\n";
    } else {
        echo "✗ User not found\n\n";
        exit(1);
    }
    
    // Test 2: Verify password
    echo "Test 2: Verify password\n";
    $verified = password_verify($password, $user->getPasswordHash());
    if ($verified) {
        echo "✓ Password verified\n\n";
    } else {
        echo "✗ Password verification failed\n\n";
        exit(1);
    }
    
    // Test 3: Authenticate
    echo "Test 3: Authenticate via AuthService\n";
    $authenticatedUser = $authService->authenticate($email, $password);
    if ($authenticatedUser) {
        echo "✓ Authentication successful\n";
        echo "  User: {$authenticatedUser->getEmail()}\n\n";
    } else {
        echo "✗ Authentication failed\n\n";
        exit(1);
    }
    
    echo "=== All tests passed! ===\n";
    echo "\nYou should be able to login with these credentials.\n";
    
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "\nStack trace:\n" . $e->getTraceAsString() . "\n";
}

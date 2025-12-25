<?php
/**
 * Debug Script - Check Super Admin Status
 * Run this file to check if current user is Super Admin
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use Core\Container\ServiceContainer;
use Modules\User\Application\Service\AuthService;

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get container and services
$container = ServiceContainer::getInstance();
$authService = $container->make(AuthService::class);

echo "=== Super Admin Debug ===\n\n";

// Check if authenticated
if (!$authService->isAuthenticated()) {
    echo "❌ Not authenticated\n";
    exit;
}

$user = $authService->getCurrentUser();
echo "✅ Authenticated as: " . $user->getEmail() . "\n";
echo "   Full Name: " . $user->getFullName() . "\n";
echo "   User ID: " . $user->getId() . "\n\n";

// Check if Super Admin
echo "Checking Super Admin status...\n";
try {
    $isSuperAdmin = $authService->isSuperAdmin();
    if ($isSuperAdmin) {
        echo "✅ User IS Super Admin\n";
    } else {
        echo "❌ User is NOT Super Admin\n";
    }
} catch (Exception $e) {
    echo "❌ Error checking Super Admin: " . $e->getMessage() . "\n";
    echo "   This might mean AuthorizationService is not properly injected\n";
}

echo "\n";

// Check AuthorizationService
echo "Checking AuthorizationService...\n";
try {
    $authorizationService = $container->make(\Modules\Authorization\Application\Service\AuthorizationService::class);
    echo "✅ AuthorizationService is available\n";
    
    // Get user roles
    $roles = $authorizationService->getUserRoles($user);
    echo "\nUser Roles:\n";
    if (empty($roles)) {
        echo "   ❌ No roles found\n";
    } else {
        foreach ($roles as $role) {
            echo "   - " . $role->getName() . " (" . $role->getDisplayName() . ")\n";
        }
    }
    
    // Check specific role
    $hasSuperAdmin = $authorizationService->userHasRole($user, 'super_admin');
    echo "\nHas 'super_admin' role: " . ($hasSuperAdmin ? "✅ YES" : "❌ NO") . "\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n=== End Debug ===\n";

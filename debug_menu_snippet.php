<?php
/**
 * Quick Fix Script for Super Admin Menu
 * Add this temporarily to menu.php to debug
 */

// Add this at the top of menu.php after getting $authService

echo "<!-- DEBUG INFO -->\n";
echo "<!-- User ID: " . $user->getId() . " -->\n";
echo "<!-- User Email: " . $user->getEmail() . " -->\n";

// Check roles
try {
    $container = ServiceContainer::getInstance();
    $authorizationService = $container->make(\Modules\Authorization\Application\Service\AuthorizationService::class);
    $roles = $authorizationService->getUserRoles($user);
    
    echo "<!-- User Roles: ";
    foreach ($roles as $role) {
        echo $role->getName() . " ";
    }
    echo "-->\n";
    
    $isSuperAdmin = $authorizationService->userHasRole($user, 'super_admin');
    echo "<!-- Is Super Admin: " . ($isSuperAdmin ? 'YES' : 'NO') . " -->\n";
    
    $isSuperAdminViaService = $authService->isSuperAdmin();
    echo "<!-- Is Super Admin (via AuthService): " . ($isSuperAdminViaService ? 'YES' : 'NO') . " -->\n";
    
} catch (Exception $e) {
    echo "<!-- Error: " . $e->getMessage() . " -->\n";
}
echo "<!-- END DEBUG -->\n";

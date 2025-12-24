<?php

declare(strict_types=1);

namespace Modules\Authorization\Application\Service;

use Modules\User\Domain\Model\User;
use Modules\Authorization\Domain\Model\Role;
use Modules\Authorization\Domain\Model\Permission;
use Modules\User\Infrastructure\Repository\UserRepository;
use Modules\Authorization\Infrastructure\Repository\RoleRepository;
use Modules\Authorization\Infrastructure\Repository\PermissionRepository;

/**
 * Authorization Service
 * Handles permission and role checks for users
 */
class AuthorizationService
{
    private UserRepository $userRepository;
    private RoleRepository $roleRepository;
    private PermissionRepository $permissionRepository;
    
    /** @var array<int, Permission[]> Cache of user permissions */
    private array $userPermissionsCache = [];
    
    /** @var array<int, Role[]> Cache of user roles */
    private array $userRolesCache = [];

    public function __construct(
        UserRepository $userRepository,
        RoleRepository $roleRepository,
        PermissionRepository $permissionRepository
    ) {
        $this->userRepository = $userRepository;
        $this->roleRepository = $roleRepository;
        $this->permissionRepository = $permissionRepository;
    }

    /**
     * Check if user has a specific permission
     *
     * @param User $user
     * @param string $permissionName Permission name (e.g., 'articles.create')
     * @return bool
     */
    public function userCan(User $user, string $permissionName): bool
    {
        $permissions = $this->getUserPermissions($user);
        
        foreach ($permissions as $permission) {
            if ($permission->matches($permissionName)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Check if user has a specific role
     *
     * @param User $user
     * @param string $roleName Role name (e.g., 'admin')
     * @return bool
     */
    public function userHasRole(User $user, string $roleName): bool
    {
        $roles = $this->getUserRoles($user);
        
        foreach ($roles as $role) {
            if ($role->getName() === $roleName) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Check if user has permission for resource and action
     *
     * @param User $user
     * @param string $resource Resource name (e.g., 'articles')
     * @param string $action Action name (e.g., 'create')
     * @return bool
     */
    public function userCanAccessResource(User $user, string $resource, string $action): bool
    {
        $permissions = $this->getUserPermissions($user);
        
        foreach ($permissions as $permission) {
            if ($permission->matchesResourceAction($resource, $action)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Get all permissions for a user
     *
     * @param User $user
     * @return Permission[]
     */
    public function getUserPermissions(User $user): array
    {
        $userId = $user->getId();
        
        if ($userId === null) {
            return [];
        }
        
        // Check cache first
        if (isset($this->userPermissionsCache[$userId])) {
            return $this->userPermissionsCache[$userId];
        }
        
        // Get permissions from database
        $permissionData = $this->userRepository->getPermissions($userId);
        
        $permissions = [];
        foreach ($permissionData as $data) {
            $permission = new Permission(
                $data['name'],
                $data['display_name'],
                $data['resource'],
                $data['action'],
                $data['description']
            );
            $permission->setId((int)$data['id']);
            $permission->setCreatedAt(new \DateTimeImmutable($data['created_at']));
            
            $permissions[] = $permission;
        }
        
        // Cache the result
        $this->userPermissionsCache[$userId] = $permissions;
        
        return $permissions;
    }

    /**
     * Get all roles for a user
     *
     * @param User $user
     * @return Role[]
     */
    public function getUserRoles(User $user): array
    {
        $userId = $user->getId();
        
        if ($userId === null) {
            return [];
        }
        
        // Check cache first
        if (isset($this->userRolesCache[$userId])) {
            return $this->userRolesCache[$userId];
        }
        
        // Get roles from database
        $roleData = $this->userRepository->getRoles($userId);
        
        $roles = [];
        foreach ($roleData as $data) {
            $role = new Role(
                $data['name'],
                $data['display_name'],
                $data['description'],
                (bool)$data['is_system']
            );
            $role->setId((int)$data['id']);
            $role->setCreatedAt(new \DateTimeImmutable($data['created_at']));
            $role->setUpdatedAt(new \DateTimeImmutable($data['updated_at']));
            
            $roles[] = $role;
        }
        
        // Cache the result
        $this->userRolesCache[$userId] = $roles;
        
        return $roles;
    }

    /**
     * Assign roles to a user
     *
     * @param User $user
     * @param int[] $roleIds
     * @return void
     */
    public function assignRolesToUser(User $user, array $roleIds): void
    {
        $userId = $user->getId();
        
        if ($userId === null) {
            throw new \RuntimeException('Cannot assign roles to user without ID');
        }
        
        $this->userRepository->syncRoles($userId, $roleIds);
        
        // Clear cache
        unset($this->userPermissionsCache[$userId]);
        unset($this->userRolesCache[$userId]);
    }

    /**
     * Clear permission cache for a user
     *
     * @param User $user
     * @return void
     */
    public function clearUserCache(User $user): void
    {
        $userId = $user->getId();
        
        if ($userId !== null) {
            unset($this->userPermissionsCache[$userId]);
            unset($this->userRolesCache[$userId]);
        }
    }

    /**
     * Clear all caches
     *
     * @return void
     */
    public function clearAllCaches(): void
    {
        $this->userPermissionsCache = [];
        $this->userRolesCache = [];
    }
}

<?php

declare(strict_types=1);

namespace Modules\Authorization\Presentation\Controller;

use Modules\User\Application\Service\AuthService;
use Modules\Authorization\Infrastructure\Repository\RoleRepository;
use Modules\Authorization\Infrastructure\Repository\PermissionRepository;
use Modules\Authorization\Domain\Model\Role;
use Shared\Infrastructure\Http\Request;
use Shared\Infrastructure\Http\Response;
use Shared\Infrastructure\Security\KeyValidator;
use Shared\Infrastructure\Controller\BaseController;
use Shared\Infrastructure\Database\DB;
use Shared\Infrastructure\Cache\Cache;

/**
 * Role Controller
 * 
 * Handles role API operations
 */
class RoleController extends BaseController
{
    private RoleRepository $roleRepository;
    private PermissionRepository $permissionRepository;

    public function __construct(
        AuthService $authService,
        KeyValidator $keyValidator,
        Request $request,
        Response $response,
        RoleRepository $roleRepository,
        PermissionRepository $permissionRepository
    ) {
        parent::__construct($authService, $keyValidator, $request, $response);
        $this->roleRepository = $roleRepository;
        $this->permissionRepository = $permissionRepository;
    }

    /**
     * GET /admin/api/roles
     * Get all roles with permission counts
     */
    public function index(): void
    {
        if ($this->request->method() !== 'GET') {
            $this->json(['success' => false, 'message' => 'Method not allowed'], 405);
            return;
        }

        $roles = $this->roleRepository->findAll();
        
        // Enrich with permission counts and user counts
        $enrichedRoles = [];
        foreach ($roles as $role) {
            $permissions = $this->roleRepository->getPermissions($role->getId());
            
            // Get user count for this role
            $userCount = DB::table('user_roles')
                ->where('role_id', '=', $role->getId())
                ->count();
            
            $enrichedRoles[] = [
                'id' => $role->getId(),
                'name' => $role->getName(),
                'display_name' => $role->getDisplayName(),
                'description' => $role->getDescription(),
                'is_system' => $role->isSystem(),
                'permissions_count' => count($permissions),
                'users_count' => $userCount,
                'created_at' => $role->getCreatedAt()->format('Y-m-d H:i:s'),
                'updated_at' => $role->getUpdatedAt()->format('Y-m-d H:i:s'),
            ];
        }

        $this->json([
            'success' => true,
            'data' => $enrichedRoles
        ]);
    }

    /**
     * GET /admin/api/roles/{id}
     * Get a single role with full details
     */
    public function show(int $id): void
    {
        try {
            if ($this->request->method() !== 'GET') {
                $this->json(['success' => false, 'message' => 'Method not allowed'], 405);
                return;
            }

            $role = $this->roleRepository->findById($id);

            if (!$role) {
                $this->json(['success' => false, 'message' => 'Role not found'], 404);
                return;
            }

            $permissions = $this->roleRepository->getPermissions($id);
            
            $this->json([
                'success' => true,
                'data' => [
                    'id' => $role->getId(),
                    'name' => $role->getName(),
                    'display_name' => $role->getDisplayName(),
                    'description' => $role->getDescription(),
                    'is_system' => $role->isSystem(),
                    'permissions' => $permissions,
                    'created_at' => $role->getCreatedAt()->format('Y-m-d H:i:s'),
                    'updated_at' => $role->getUpdatedAt()->format('Y-m-d H:i:s'),
                ]
            ]);
        } catch (\Throwable $e) {
            // Log the error for debugging
            error_log("RoleController::show error: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            
            $this->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 500);
        }
    }

    /**
     * POST /admin/api/roles
     * Create new role
     */
    public function store(): void
    {
        if ($this->request->method() !== 'POST') {
            $this->json(['success' => false, 'message' => 'Method not allowed'], 405);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);

        // Validation
        if (empty($data['name'])) {
            $this->json(['success' => false, 'message' => 'Role name is required'], 400);
            return;
        }

        if (empty($data['display_name'])) {
            $this->json(['success' => false, 'message' => 'Display name is required'], 400);
            return;
        }

        // Check if name already exists
        $existing = $this->roleRepository->findByName($data['name']);
        if ($existing) {
            $this->json(['success' => false, 'message' => 'Role name already exists'], 400);
            return;
        }

        // Create role
        $role = new Role(
            $data['name'],
            $data['display_name'],
            $data['description'] ?? '',
            false // New roles are not system roles
        );

        $this->roleRepository->save($role);

        // Assign permissions if provided
        if (!empty($data['permissions']) && is_array($data['permissions'])) {
            $this->roleRepository->syncPermissions($role->getId(), $data['permissions']);
        }

        $this->json([
            'success' => true,
            'message' => 'Role created successfully',
            'data' => [
                'id' => $role->getId(),
                'name' => $role->getName(),
                'display_name' => $role->getDisplayName()
            ]
        ]);
    }

    /**
     * PUT /admin/api/roles/{id}
     * Update role
     */
    public function update(int $id): void
    {
        if ($this->request->method() !== 'PUT') {
            $this->json(['success' => false, 'message' => 'Method not allowed'], 405);
            return;
        }

        $role = $this->roleRepository->findById($id);
        
        if (!$role) {
            $this->json(['success' => false, 'message' => 'Role not found'], 404);
            return;
        }

        // Don't allow editing system roles' name
        if ($role->isSystem()) {
            $this->json(['success' => false, 'message' => 'Cannot modify system role properties'], 403);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);

        // Update fields
        if (isset($data['name']) && $data['name'] !== $role->getName()) {
            // Check if new name exists
            $existing = $this->roleRepository->findByName($data['name']);
            if ($existing && $existing->getId() !== $id) {
                $this->json(['success' => false, 'message' => 'Role name already exists'], 400);
                return;
            }
            $role->setName($data['name']);
        }

        if (isset($data['display_name'])) {
            $role->setDisplayName($data['display_name']);
        }

        if (isset($data['description'])) {
            $role->setDescription($data['description']);
        }

        $role->setUpdatedAt(new \DateTimeImmutable());
        $this->roleRepository->save($role);

        $this->json([
            'success' => true,
            'message' => 'Role updated successfully'
        ]);
    }

    /**
     * DELETE /admin/api/roles/{id}
     * Delete role
     */
    public function destroy(int $id): void
    {
        if ($this->request->method() !== 'DELETE') {
            $this->json(['success' => false, 'message' => 'Method not allowed'], 405);
            return;
        }

        $role = $this->roleRepository->findById($id);
        
        if (!$role) {
            $this->json(['success' => false, 'message' => 'Role not found'], 404);
            return;
        }

        // Don't allow deleting system roles
        if ($role->isSystem()) {
            $this->json(['success' => false, 'message' => 'Cannot delete system role'], 403);
            return;
        }

        // Check if role is assigned to any users
        $userCount = DB::table('user_roles')
            ->where('role_id', '=', $id)
            ->count();

        if ($userCount > 0) {
            $this->json([
                'success' => false,
                'message' => "Cannot delete role. It is assigned to {$userCount} user(s)"
            ], 400);
            return;
        }

        // Delete role permissions first
        DB::table('role_permissions')
            ->where('role_id', '=', $id)
            ->delete();

        // Delete role
        $this->roleRepository->delete($id);

        $this->json([
            'success' => true,
            'message' => 'Role deleted successfully'
        ]);
    }

    /**
     * GET /admin/api/roles/{id}/permissions
     * Get role permissions
     */
    public function getPermissions(int $id): void
    {
        if ($this->request->method() !== 'GET') {
            $this->json(['success' => false, 'message' => 'Method not allowed'], 405);
            return;
        }

        $role = $this->roleRepository->findById($id);
        
        if (!$role) {
            $this->json(['success' => false, 'message' => 'Role not found'], 404);
            return;
        }

        $permissions = $this->roleRepository->getPermissions($id);

        $this->json([
            'success' => true,
            'data' => $permissions
        ]);
    }

    /**
     * PUT /admin/api/roles/{id}/permissions
     * Update role permissions
     */
    public function updatePermissions(int $id): void
    {
        if ($this->request->method() !== 'PUT') {
            $this->json(['success' => false, 'message' => 'Method not allowed'], 405);
            return;
        }

        $role = $this->roleRepository->findById($id);
        
        if (!$role) {
            $this->json(['success' => false, 'message' => 'Role not found'], 404);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['permissions']) || !is_array($data['permissions'])) {
            $this->json(['success' => false, 'message' => 'Permissions array required'], 400);
            return;
        }

        // Validate permission IDs
        $permissionIds = array_map('intval', $data['permissions']);
        
        // Sync permissions
        $this->roleRepository->syncPermissions($id, $permissionIds);
        
        // Clear user caches (since their permissions changed)
        Cache::flush(); // Simple approach - clear all caches

        $this->json([
            'success' => true,
            'message' => 'Permissions updated successfully',
            'count' => count($permissionIds)
        ]);
    }
}

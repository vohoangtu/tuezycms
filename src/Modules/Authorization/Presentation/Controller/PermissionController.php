<?php

declare(strict_types=1);

namespace Modules\Authorization\Presentation\Controller;

use Modules\User\Application\Service\AuthService;
use Modules\Authorization\Infrastructure\Repository\PermissionRepository;
use Shared\Infrastructure\Http\Request;
use Shared\Infrastructure\Http\Response;
use Shared\Infrastructure\Security\KeyValidator;
use Shared\Infrastructure\Controller\BaseController;

/**
 * Permission Controller
 * 
 * Handles permission API operations
 */
class PermissionController extends BaseController
{
    private PermissionRepository $permissionRepository;

    public function __construct(
        AuthService $authService,
        KeyValidator $keyValidator,
        Request $request,
        Response $response,
        PermissionRepository $permissionRepository
    ) {
        parent::__construct($authService, $keyValidator, $request, $response);
        $this->permissionRepository = $permissionRepository;
    }

    /**
     * GET /admin/api/permissions
     * Get all permissions
     */
    public function index(): void
    {
        if ($this->request->method() !== 'GET') {
            $this->json(['success' => false, 'message' => 'Method not allowed'], 405);
            return;
        }

        $permissions = $this->permissionRepository->findAll();
        
        // Convert to array
        $permissionsArray = [];
        foreach ($permissions as $permission) {
            $permissionsArray[] = [
                'id' => $permission->getId(),
                'name' => $permission->getName(),
                'display_name' => $permission->getDisplayName(),
                'resource' => $permission->getResource(),
                'action' => $permission->getAction(),
                'description' => $permission->getDescription(),
                'created_at' => $permission->getCreatedAt()->format('Y-m-d H:i:s'),
            ];
        }

        $this->json([
            'success' => true,
            'data' => $permissionsArray
        ]);
    }

    /**
     * GET /admin/api/permissions/by-resource
     * Get permissions grouped by resource
     */
    public function byResource(): void
    {
        if ($this->request->method() !== 'GET') {
            $this->json(['success' => false, 'message' => 'Method not allowed'], 405);
            return;
        }

        $permissions = $this->permissionRepository->findAll();
        
        // Group by resource
        $grouped = [];
        foreach ($permissions as $permission) {
            $resource = $permission->getResource();
            
            if (!isset($grouped[$resource])) {
                $grouped[$resource] = [
                    'resource' => $resource,
                    'permissions' => []
                ];
            }
            
            $grouped[$resource]['permissions'][] = [
                'id' => $permission->getId(),
                'name' => $permission->getName(),
                'display_name' => $permission->getDisplayName(),
                'action' => $permission->getAction(),
                'description' => $permission->getDescription(),
            ];
        }

        // Convert to indexed array and sort
        $result = array_values($grouped);
        usort($result, function($a, $b) {
            return strcmp($a['resource'], $b['resource']);
        });

        $this->json([
            'success' => true,
            'data' => $result
        ]);
    }

    /**
     * GET /admin/api/permissions/{id}
     * Get a single permission
     */
    public function show(int $id): void
    {
        if ($this->request->method() !== 'GET') {
            $this->json(['success' => false, 'message' => 'Method not allowed'], 405);
            return;
        }

        $permission = $this->permissionRepository->findById($id);

        if (!$permission) {
            $this->json(['success' => false, 'message' => 'Permission not found'], 404);
            return;
        }

        $this->json([
            'success' => true,
            'data' => [
                'id' => $permission->getId(),
                'name' => $permission->getName(),
                'display_name' => $permission->getDisplayName(),
                'resource' => $permission->getResource(),
                'action' => $permission->getAction(),
                'description' => $permission->getDescription(),
                'created_at' => $permission->getCreatedAt()->format('Y-m-d H:i:s'),
            ]
        ]);
    }
}

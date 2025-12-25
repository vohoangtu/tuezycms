<?php

declare(strict_types=1);

namespace Modules\Module\Presentation\Controller;

use Shared\Infrastructure\Controller\BaseController;
use Shared\Infrastructure\Http\Request;
use Shared\Infrastructure\Http\Response;
use Modules\User\Application\Service\AuthService;
use Shared\Infrastructure\Security\KeyValidator;
use Modules\Module\Application\Service\ModuleService;
use Modules\Module\Infrastructure\Repository\ModuleRepository;

// Event-Driven imports
use Shared\Infrastructure\Event\EventDispatcher;
use Modules\Module\Domain\Event\ModuleToggledEvent;
use Modules\Module\Domain\Event\ModuleConfiguredEvent;

/**
 * Module Controller
 * API endpoints for module management
 */
class ModuleController extends BaseController
{
    private ModuleService $moduleService;

    public function __construct(
        AuthService $authService,
        KeyValidator $keyValidator,
        Request $request,
        Response $response,
        ModuleRepository $repository
    ) {
        parent::__construct($authService, $keyValidator, $request, $response);
        $this->moduleService = new ModuleService($repository);
    }

    /**
     * GET /admin/api/modules
     * List all modules
     */
    public function index(): void
    {
        if ($this->request->method() !== 'GET') {
            $this->json(['success' => false, 'message' => 'Method not allowed'], 405);
            return;
        }

        try {
            $modules = $this->moduleService->getAllModules();
            
            $data = array_map(fn($module) => $module->toArray(), $modules);
            
            $this->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            $this->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /admin/api/modules/by-category
     * List modules grouped by category
     */
    public function byCategory(): void
    {
        if ($this->request->method() !== 'GET') {
            $this->json(['success' => false, 'message' => 'Method not allowed'], 405);
            return;
        }

        try {
            $grouped = $this->moduleService->getModulesGroupedByCategory();
            
            $data = [];
            foreach ($grouped as $category => $modules) {
                $data[$category] = array_map(fn($module) => $module->toArray(), $modules);
            }
            
            $this->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            $this->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /admin/api/modules/{id}
     * Get single module details
     */
    public function show(int $id): void
    {
        if ($this->request->method() !== 'GET') {
            $this->json(['success' => false, 'message' => 'Method not allowed'], 405);
            return;
        }

        try {
            $repository = new ModuleRepository();
            $module = $repository->findById($id);

            if (!$module) {
                $this->json(['success' => false, 'message' => 'Module not found'], 404);
                return;
            }
            
            $this->json([
                'success' => true,
                'data' => $module->toArray()
            ]);
        } catch (\Exception $e) {
            $this->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * PUT /admin/api/modules/{id}/toggle
     * Enable or disable a module
     */
    public function toggle(int $id): void
    {
        if ($this->request->method() !== 'PUT') {
            $this->json(['success' => false, 'message' => 'Method not allowed'], 405);
            return;
        }

        try {
            $repository = new ModuleRepository();
            $module = $repository->findById($id);

            if (!$module) {
                $this->json(['success' => false, 'message' => 'Module not found'], 404);
                return;
            }

            $data = $this->request->json();
            $enabled = $data['enabled'] ?? !$module->isEnabled();

            $this->moduleService->toggleModule($module->getName(), (bool) $enabled);
            
            // Dispatch ModuleToggledEvent
            EventDispatcher::getInstance()->dispatch(
                new ModuleToggledEvent($id, $module->getName(), (bool)$enabled)
            );
            
            $this->json([
                'success' => true,
                'message' => $enabled ? 'Module enabled successfully' : 'Module disabled successfully'
            ]);
        } catch (\RuntimeException $e) {
            $this->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        } catch (\Exception $e) {
            $this->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * PUT /admin/api/modules/{id}/config
     * Update module configuration
     */
    public function updateConfig(int $id): void
    {
        if ($this->request->method() !== 'PUT') {
            $this->json(['success' => false, 'message' => 'Method not allowed'], 405);
            return;
        }

        try {
            $repository = new ModuleRepository();
            $module = $repository->findById($id);

            if (!$module) {
                $this->json(['success' => false, 'message' => 'Module not found'], 404);
                return;
            }

            $data = $this->request->json();
            $config = $data['config'] ?? [];

            if (!is_array($config)) {
                $this->json([
                    'success' => false,
                    'message' => 'Config must be an array'
                ], 400);
                return;
            }

            $this->moduleService->updateModuleConfig($module->getName(), $config);
            
            // Dispatch ModuleConfiguredEvent
            EventDispatcher::getInstance()->dispatch(
                new ModuleConfiguredEvent($id, $module->getName(), $config)
            );
            
            $this->json([
                'success' => true,
                'message' => 'Module configuration updated successfully'
            ]);
        } catch (\Exception $e) {
            $this->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}

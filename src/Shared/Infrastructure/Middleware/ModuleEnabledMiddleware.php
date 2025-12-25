<?php

declare(strict_types=1);

namespace Shared\Infrastructure\Middleware;

use Modules\Module\Infrastructure\Repository\ModuleRepository;
use Shared\Infrastructure\Http\Request;
use Shared\Infrastructure\Http\Response;

/**
 * Module Enabled Middleware
 * Checks if a module is enabled before allowing access
 */
class ModuleEnabledMiddleware
{
    private ModuleRepository $moduleRepository;

    public function __construct(ModuleRepository $moduleRepository)
    {
        $this->moduleRepository = $moduleRepository;
    }

    /**
     * Check if module is enabled
     *
     * @param string $moduleName Module name (e.g., 'Article', 'Product')
     * @param Request $request
     * @param Response $response
     * @return bool
     */
    public function check(string $moduleName, Request $request, Response $response): bool
    {
        $module = $this->moduleRepository->findByName($moduleName);
        
        if (!$module || !$module->isEnabled()) {
            // Module is disabled
            $response->json([
                'success' => false,
                'message' => "Module '{$moduleName}' is currently disabled."
            ], 403);
            return false;
        }
        
        return true;
    }

    /**
     * Require module to be enabled
     *
     * @param string $moduleName
     * @param Request $request
     * @param Response $response
     * @throws \Exception
     */
    public function require(string $moduleName, Request $request, Response $response): void
    {
        if (!$this->check($moduleName, $request, $response)) {
            throw new \Exception("Module '{$moduleName}' is disabled");
        }
    }
}

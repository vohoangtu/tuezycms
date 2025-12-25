<?php

declare(strict_types=1);

namespace Shared\Infrastructure\Middleware;

use Shared\Infrastructure\Http\Request;
use Shared\Infrastructure\Http\Response;
use Modules\Module\Application\Service\ModuleService;
use Modules\Module\Infrastructure\Repository\ModuleRepository;

/**
 * Module Middleware
 * Kiểm tra xem module có được bật hay không
 */
class ModuleMiddleware
{
    private ModuleService $moduleService;

    public function __construct()
    {
        $repository = new ModuleRepository();
        $this->moduleService = new ModuleService($repository);
    }

    /**
     * Kiểm tra module có enabled không
     * 
     * @param Request $request
     * @param string $moduleName Tên module cần kiểm tra (vd: 'product_management')
     * @return bool True nếu module enabled, false nếu disabled
     */
    public function handle(Request $request, string $moduleName): bool
    {
        // Kiểm tra module có enabled không
        $isEnabled = $this->moduleService->isModuleEnabled($moduleName);
        
        if (!$isEnabled) {
            // Log để debug
            error_log("Module '{$moduleName}' is disabled. Access denied.");
            return false;
        }
        
        return true;
    }

    /**
     * Middleware handler cho routing system
     * Trả về Response nếu module disabled
     */
    public function check(Request $request, Response $response, string $moduleName): ?Response
    {
        if (!$this->handle($request, $moduleName)) {
            // Trả về 403 Forbidden
            $response->json([
                'success' => false,
                'message' => "Module '{$moduleName}' is not enabled",
                'code' => 403
            ], 403);
            
            return $response;
        }
        
        return null; // Cho phép tiếp tục
    }
}

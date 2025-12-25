<?php

declare(strict_types=1);

namespace Shared\Infrastructure\Helper;

use Modules\Module\Application\Service\ModuleService;
use Modules\Module\Infrastructure\Repository\ModuleRepository;

/**
 * Module Helper
 * Static helper functions để làm việc với modules
 */
class ModuleHelper
{
    private static ?ModuleService $moduleService = null;

    /**
     * Get module service instance (singleton)
     */
    private static function getModuleService(): ModuleService
    {
        if (self::$moduleService === null) {
            $repository = new ModuleRepository();
            self::$moduleService = new ModuleService($repository);
        }
        
        return self::$moduleService;
    }

    /**
     * Kiểm tra module có enabled không
     * 
     * @param string $moduleName Tên module (vd: 'product_management')
     * @return bool
     */
    public static function isEnabled(string $moduleName): bool
    {
        try {
            return self::getModuleService()->isModuleEnabled($moduleName);
        } catch (\Exception $e) {
            error_log("ModuleHelper::isEnabled error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Lấy config của module
     * 
     * @param string $moduleName Tên module
     * @param string|null $key Key cụ thể trong config, null để lấy toàn bộ
     * @return mixed
     */
    public static function getConfig(string $moduleName, ?string $key = null)
    {
        try {
            $config = self::getModuleService()->getModuleConfig($moduleName);
            
            if ($key === null) {
                return $config;
            }
            
            return $config[$key] ?? null;
        } catch (\Exception $e) {
            error_log("ModuleHelper::getConfig error: " . $e->getMessage());
            return $key === null ? [] : null;
        }
    }

    /**
     * Lấy giá trị config cụ thể
     * 
     * @param string $moduleName Tên module
     * @param string $key Key trong config
     * @param mixed $default Giá trị mặc định nếu không tìm thấy
     * @return mixed
     */
    public static function getConfigValue(string $moduleName, string $key, $default = null)
    {
        try {
            return self::getModuleService()->getModuleConfigValue($moduleName, $key, $default);
        } catch (\Exception $e) {
            error_log("ModuleHelper::getConfigValue error: " . $e->getMessage());
            return $default;
        }
    }

    /**
     * Lấy danh sách tất cả modules đã enabled
     * 
     * @return array Array of Module objects
     */
    public static function getEnabledModules(): array
    {
        try {
            return self::getModuleService()->getEnabledModules();
        } catch (\Exception $e) {
            error_log("ModuleHelper::getEnabledModules error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Lấy danh sách tất cả modules
     * 
     * @return array Array of Module objects
     */
    public static function getAllModules(): array
    {
        try {
            return self::getModuleService()->getAllModules();
        } catch (\Exception $e) {
            error_log("ModuleHelper::getAllModules error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Lấy modules theo category
     * 
     * @param string $category Category name (user, product, content, system)
     * @return array Array of Module objects
     */
    public static function getModulesByCategory(string $category): array
    {
        try {
            return self::getModuleService()->getModulesByCategory($category);
        } catch (\Exception $e) {
            error_log("ModuleHelper::getModulesByCategory error: " . $e->getMessage());
            return [];
        }
    }
}

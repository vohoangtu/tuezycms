#!/usr/bin/env php
<?php
/**
 * Module Migration Script
 * Automates the migration of modules to DDD Modular structure
 */

class ModuleMigrator
{
    private string $srcPath;
    private string $modulesPath;
    
    public function __construct(string $projectRoot)
    {
        $this->srcPath = $projectRoot . '/src';
        $this->modulesPath = $this->srcPath . '/Modules';
    }
    
    /**
     * Migrate a module
     */
    public function migrateModule(string $moduleName): void
    {
        echo "Migrating {$moduleName} module...\n";
        
        // 1. Create module structure
        $this->createModuleStructure($moduleName);
        
        // 2. Move Domain models
        $this->moveDomainModels($moduleName);
        
        // 3. Move Repositories
        $this->moveRepositories($moduleName);
        
        // 4. Move Services
        $this->moveServices($moduleName);
        
        // 5. Move Controllers
        $this->moveControllers($moduleName);
        
        // 6. Move Views
        $this->moveViews($moduleName);
        
        echo "✓ {$moduleName} module migrated successfully!\n\n";
    }
    
    private function createModuleStructure(string $module): void
    {
        $dirs = [
            "{$this->modulesPath}/{$module}/Domain/Model",
            "{$this->modulesPath}/{$module}/Domain/Repository",
            "{$this->modulesPath}/{$module}/Application/Service",
            "{$this->modulesPath}/{$module}/Infrastructure/Repository",
            "{$this->modulesPath}/{$module}/Presentation/Controller",
            "{$this->modulesPath}/{$module}/Presentation/View/admin",
        ];
        
        foreach ($dirs as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
                echo "  Created: {$dir}\n";
            }
        }
    }
    
    private function moveDomainModels(string $module): void
    {
        $source = "{$this->srcPath}/Domain/{$module}";
        $dest = "{$this->modulesPath}/{$module}/Domain/Model";
        
        if (!is_dir($source)) {
            echo "  ⚠ No domain models found for {$module}\n";
            return;
        }
        
        $files = glob("{$source}/*.php");
        foreach ($files as $file) {
            $filename = basename($file);
            $newFile = "{$dest}/{$filename}";
            
            // Read file content
            $content = file_get_contents($file);
            
            // Update namespace
            $content = str_replace(
                "namespace TuzyCMS\\Domain\\{$module};",
                "namespace Modules\\{$module}\\Domain\\Model;",
                $content
            );
            
            // Write to new location
            file_put_contents($newFile, $content);
            echo "  Moved: {$filename} → Domain/Model/\n";
        }
    }
    
    private function moveRepositories(string $module): void
    {
        $pattern = "{$this->srcPath}/Infrastructure/Repository/{$module}*Repository.php";
        $files = glob($pattern);
        
        foreach ($files as $file) {
            $filename = basename($file);
            $dest = "{$this->modulesPath}/{$module}/Infrastructure/Repository/{$filename}";
            
            // Read and update content
            $content = file_get_contents($file);
            $content = str_replace(
                "namespace TuzyCMS\\Infrastructure\\Repository;",
                "namespace Modules\\{$module}\\Infrastructure\\Repository;",
                $content
            );
            
            // Update use statements
            $content = str_replace(
                "use TuzyCMS\\Domain\\{$module}\\",
                "use Modules\\{$module}\\Domain\\Model\\",
                $content
            );
            
            file_put_contents($dest, $content);
            echo "  Moved: {$filename} → Infrastructure/Repository/\n";
        }
    }
    
    private function moveServices(string $module): void
    {
        $pattern = "{$this->srcPath}/Application/Service/{$module}Service.php";
        $files = glob($pattern);
        
        foreach ($files as $file) {
            $filename = basename($file);
            $dest = "{$this->modulesPath}/{$module}/Application/Service/{$filename}";
            
            $content = file_get_contents($file);
            $content = str_replace(
                "namespace TuzyCMS\\Application\\Service;",
                "namespace Modules\\{$module}\\Application\\Service;",
                $content
            );
            
            // Update repository references
            $content = str_replace(
                "use TuzyCMS\\Infrastructure\\Repository\\{$module}",
                "use Modules\\{$module}\\Infrastructure\\Repository\\{$module}",
                $content
            );
            
            file_put_contents($dest, $content);
            echo "  Moved: {$filename} → Application/Service/\n";
        }
    }
    
    private function moveControllers(string $module): void
    {
        $patterns = [
            "{$this->srcPath}/Presentation/Controller/{$module}Controller.php",
            "{$this->srcPath}/Presentation/Controller/{$module}PageController.php",
        ];
        
        foreach ($patterns as $pattern) {
            $files = glob($pattern);
            foreach ($files as $file) {
                $filename = basename($file);
                $dest = "{$this->modulesPath}/{$module}/Presentation/Controller/{$filename}";
                
                $content = file_get_contents($file);
                $content = str_replace(
                    "namespace TuzyCMS\\Presentation\\Controller;",
                    "namespace Modules\\{$module}\\Presentation\\Controller;",
                    $content
                );
                
                // Update service references
                $content = str_replace(
                    "use TuzyCMS\\Application\\Service\\{$module}Service;",
                    "use Modules\\{$module}\\Application\\Service\\{$module}Service;",
                    $content
                );
                
                file_put_contents($dest, $content);
                echo "  Moved: {$filename} → Presentation/Controller/\n";
            }
        }
    }
    
    private function moveViews(string $module): void
    {
        $moduleLower = strtolower($module);
        $source = "{$this->srcPath}/Presentation/View/admin/{$moduleLower}s.php";
        
        if (!file_exists($source)) {
            $source = "{$this->srcPath}/Presentation/View/admin/{$moduleLower}.php";
        }
        
        if (file_exists($source)) {
            $dest = "{$this->modulesPath}/{$module}/Presentation/View/admin/index.php";
            copy($source, $dest);
            echo "  Moved: view → Presentation/View/admin/index.php\n";
        }
    }
}

// Run migration
if (php_sapi_name() !== 'cli') {
    die("This script must be run from command line\n");
}

$projectRoot = dirname(__DIR__);
$migrator = new ModuleMigrator($projectRoot);

$modules = ['Article', 'Product', 'User', 'Authorization', 'Order', 'Media', 'Promotion'];

echo "=== Module Migration Script ===\n\n";
echo "This will migrate " . count($modules) . " modules to DDD Modular structure.\n";
echo "Press Enter to continue or Ctrl+C to cancel...\n";
fgets(STDIN);

foreach ($modules as $module) {
    $migrator->migrateModule($module);
}

echo "\n=== Migration Complete! ===\n";
echo "Next steps:\n";
echo "1. Run: composer dump-autoload\n";
echo "2. Update routes in AdminRoutes.php\n";
echo "3. Test all functionality\n";

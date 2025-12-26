<?php

declare(strict_types=1);

namespace Shared\Infrastructure\View;

use Shared\Infrastructure\Config\AppConfig;

/**
 * Master Layout Renderer
 * Handles rendering views with master admin template from master/ folder
 */
class MasterLayoutRenderer
{
    private string $masterPath;
    private string $viewPath;
    private array $sharedData = [];

    public function __construct()
    {
        $basePath = dirname(__DIR__, 4);  // Up 4 levels from src/Shared/Infrastructure/View -> Root
        $this->masterPath = $basePath . '/master';  // Master theme path
        // Fix: Removed extra '/src' because we are already at root
        $this->viewPath = $basePath . '/src/Shared/Presentation/View';
    }

    /**
     * Render view with master layout
     *
     * @param string $view View path (e.g., 'admin/dashboard')
     * @param array $data Data to pass to view
     * @param string $layout Layout name (default: 'admin-main')
     * @return string Rendered HTML
     */
    public function render(string $view, array $data = [], string $layout = 'admin-main'): string
    {
        // Merge shared data
        $data = array_merge($this->sharedData, $data);
        
        // Extract data to variables
        extract($data);

        // Start output buffering for view content
        ob_start();
        
        // Include the view file
        $viewFile = $this->viewPath . '/' . $view . '.php';

        if (!file_exists($viewFile)) {
            throw new \RuntimeException("View not found: {$view}");
        }
        
        include $viewFile;
        $content = ob_get_clean();

        // If layout is 'none', return content without wrapper
        if ($layout === 'none') {
            return $content;
        }

        // Now render with master layout
        ob_start();
        
        // Set global variable for content
        $GLOBALS['pageContent'] = $content;
        $GLOBALS['pageData'] = $data;
        
        // Include master layout from master/layouts/
        $layoutFile = $this->masterPath . '/layouts/' . $layout . '.php';
        if (!file_exists($layoutFile)) {
            // Fallback to admin-main.php
            $layoutFile = $this->masterPath . '/layouts/admin-main.php';
        }
        
        if (file_exists($layoutFile)) {
            include $layoutFile;
        } else {
            // No layout, just return content
            echo $content;
        }
        
        return ob_get_clean();
    }

    /**
     * Get asset URL from master folder
     *
     * @param string $path Asset path relative to master/assets/
     * @return string Full asset URL
     */
    public function getAssetUrl(string $path): string
    {
        $path = ltrim($path, '/');
        return '/master/assets/' . $path;
    }

    /**
     * Include a layout component
     *
     * @param string $name Layout component name (e.g., 'sidebar', 'topbar')
     * @param array $data Data to pass to component
     * @return void
     */
    public function includeLayout(string $name, array $data = []): void
    {
        extract($data);
        
        $layoutFile = $this->masterPath . '/layouts/' . $name . '.php';
        if (file_exists($layoutFile)) {
            include $layoutFile;
        }
    }

    /**
     * Share data across all views
     *
     * @param string|array $key Key or array of key-value pairs
     * @param mixed $value Value (if key is string)
     * @return void
     */
    public function share(string|array $key, mixed $value = null): void
    {
        if (is_array($key)) {
            $this->sharedData = array_merge($this->sharedData, $key);
        } else {
            $this->sharedData[$key] = $value;
        }
    }

    /**
     * Helper function for views to include layout components
     * This is a wrapper for includeLayout that can be called from views
     *
     * @param string $filePath Layout file path
     * @param array $variables Variables to pass
     * @param bool $print Whether to print or return
     * @return string|null
     */
    public function includeFileWithVariables(string $filePath, array $variables = [], bool $print = true): ?string
    {
        $output = null;
        
        // Check if path is relative to master/layouts/
        if (!str_starts_with($filePath, '/') && !str_contains($filePath, ':')) {
            $fullPath = $this->masterPath . '/' . $filePath;
        } else {
            $fullPath = $filePath;
        }
        
        if (file_exists($fullPath)) {
            extract($variables);
            ob_start();
            include $fullPath;
            $output = ob_get_clean();
        }
        
        if ($print) {
            echo $output;
        }
        
        return $output;
    }
}

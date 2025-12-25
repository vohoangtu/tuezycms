<?php

declare(strict_types=1);

namespace Shared\Infrastructure\View;

use Modules\User\Application\Service\AuthService;
use Shared\Infrastructure\I18n\Translator;
use Shared\Infrastructure\Config\AppConfig;

/**
 * View Helper Functions
 * Provides utility functions for views
 */
class ViewHelper
{
    private static ?ViewHelper $instance = null;
    private ?AuthService $authService = null;
    private ?Translator $translator = null;
    private ?AppConfig $config = null;

    private function __construct()
    {
        $this->config = AppConfig::getInstance();
    }

    public static function getInstance(): ViewHelper
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Set AuthService
     */
    public function setAuthService(AuthService $authService): void
    {
        $this->authService = $authService;
    }

    /**
     * Set Translator
     */
    public function setTranslator(Translator $translator): void
    {
        $this->translator = $translator;
    }

    /**
     * Include a file with variables
     *
     * @param string $filePath
     * @param array $variables
     * @param bool $print
     * @return string|null
     */
    public static function includeFileWithVariables(string $filePath, array $variables = [], bool $print = true): ?string
    {
        $output = null;
        if (file_exists($filePath)) {
            // Extract the variables to a local namespace
            extract($variables);

            // Start output buffering
            ob_start();

            // Include the template file
            include $filePath;

            // End buffering and return its contents
            $output = ob_get_clean();
        }
        if ($print) {
            print $output;
        }
        return $output;
    }

    /**
     * Generate asset URL with versioning
     *
     * @param string $path
     * @param string|null $version
     * @param bool $fromMaster
     * @return string
     */
    public static function asset(string $path, ?string $version = null, bool $fromMaster = false): string
    {
        $path = ltrim($path, '/');
        
        if ($fromMaster) {
            $url = '/master/assets/' . $path;
        } else {
            $url = '/assets/' . $path;
        }
        
        if ($version !== null) {
            $url .= '?v=' . $version;
        }
        return $url;
    }

    /**
     * Generate admin asset URL
     *
     * @param string $path
     * @return string
     */
    public static function adminAsset(string $path): string
    {
        return '/admin/assets/' . ltrim($path, '/');
    }

    /**
     * Check if current route matches
     *
     * @param string $route
     * @param string $currentPage
     * @return bool
     */
    public static function isActive(string $route, string $currentPage): bool
    {
        return $route === $currentPage;
    }

    /**
     * Generate active class for menu items
     *
     * @param string $route
     * @param string $currentPage
     * @return string
     */
    public static function activeClass(string $route, string $currentPage): string
    {
        return self::isActive($route, $currentPage) ? 'active' : '';
    }

    /**
     * Generate route URL
     *
     * @param string $name Route name
     * @param array $params Route parameters
     * @return string Route URL
     */
    public function route(string $name, array $params = []): string
    {
        // Simple route generation for now
        $url = '/' . ltrim($name, '/');
        
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }
        
        return $url;
    }

    /**
     * Translate text
     *
     * @param string $key Translation key
     * @param array $params Parameters for translation
     * @return string Translated text
     */
    public function trans(string $key, array $params = []): string
    {
        if ($this->translator) {
            return $this->translator->trans($key, $params);
        }
        
        return $key;
    }

    /**
     * Get current user
     *
     * @return \Modules\User\Domain\Model\User|null Current user or null
     */
    public function user(): ?\Modules\User\Domain\Model\User
    {
        if ($this->authService) {
            return $this->authService->getCurrentUser();
        }
        
        return null;
    }

    /**
     * Get config value
     *
     * @param string $key Config key (dot notation supported)
     * @param mixed $default Default value
     * @return mixed Config value
     */
    public function config(string $key, mixed $default = null): mixed
    {
        if ($this->config) {
            return $this->config->get($key, $default);
        }
        
        return $default;
    }

    /**
     * Escape HTML
     *
     * @param string $value Value to escape
     * @return string Escaped value
     */
    public function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Generate CSRF token
     *
     * @return string CSRF token
     */
    public function csrf(): string
    {
        $token = \Shared\Infrastructure\Session\SessionManager::get('csrf_token');
        
        if (!$token) {
            $token = bin2hex(random_bytes(32));
            \Shared\Infrastructure\Session\SessionManager::set('csrf_token', $token);
        }
        
        return $token;
    }

    /**
     * Generate CSRF input field
     *
     * @return string HTML input field
     */
    public function csrfField(): string
    {
        return '<input type="hidden" name="csrf_token" value="' . $this->csrf() . '">';
    }
}

// Global helper functions for views
if (!function_exists('vhelper')) {
    function vhelper(): ViewHelper {
        return ViewHelper::getInstance();
    }
}


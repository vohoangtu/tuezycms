<?php

declare(strict_types=1);

namespace Shared\Infrastructure\Controller;

use Modules\User\Application\Service\AuthService;
use Shared\Infrastructure\Http\Request;
use Shared\Infrastructure\Http\Response;
use Shared\Infrastructure\Security\KeyValidator;

/**
 * Cache Controller
 * Handles cache operations
 */
class CacheController extends BaseController
{
    public function __construct(
        AuthService $authService,
        KeyValidator $keyValidator,
        Request $request,
        Response $response
    ) {
        parent::__construct($authService, $keyValidator, $request, $response);
    }

    /**
     * Clear all cache
     */
    public function clear(): void
    {
        try {
            $cleared = 0;
            
            // Clear file cache
            $cacheDir = dirname(__DIR__, 4) . '/storage/cache';
            if (is_dir($cacheDir)) {
                $files = glob($cacheDir . '/*');
                foreach ($files as $file) {
                    if (is_file($file)) {
                        unlink($file);
                        $cleared++;
                    }
                }
            }
            
            // Clear session cache if needed
            $sessionDir = dirname(__DIR__, 4) . '/storage/sessions';
            if (is_dir($sessionDir)) {
                // Don't delete current session
                $currentSessionId = session_id();
                $files = glob($sessionDir . '/*');
                foreach ($files as $file) {
                    if (is_file($file) && !str_contains($file, $currentSessionId)) {
                        // Only delete old sessions (older than 1 hour)
                        if (filemtime($file) < time() - 3600) {
                            unlink($file);
                            $cleared++;
                        }
                    }
                }
            }
            
            $this->json([
                'success' => true,
                'message' => "Đã xóa {$cleared} file cache",
                'cleared' => $cleared
            ]);
        } catch (\Exception $e) {
            $this->json([
                'success' => false,
                'message' => 'Lỗi khi xóa cache: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get cache info
     */
    public function info(): void
    {
        try {
            $cacheDir = dirname(__DIR__, 4) . '/storage/cache';
            $totalFiles = 0;
            $totalSize = 0;
            
            if (is_dir($cacheDir)) {
                $files = glob($cacheDir . '/*');
                $totalFiles = count($files);
                
                foreach ($files as $file) {
                    if (is_file($file)) {
                        $totalSize += filesize($file);
                    }
                }
            }
            
            $this->json([
                'success' => true,
                'data' => [
                    'total_files' => $totalFiles,
                    'total_size' => $totalSize,
                    'total_size_formatted' => $this->formatBytes($totalSize)
                ]
            ]);
        } catch (\Exception $e) {
            $this->json([
                'success' => false,
                'message' => 'Lỗi khi lấy thông tin cache: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Format bytes to human readable
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
}

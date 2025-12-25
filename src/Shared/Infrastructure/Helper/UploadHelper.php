<?php

declare(strict_types=1);

namespace Shared\Infrastructure\Helper;

use Shared\Infrastructure\Database\DB;

/**
 * Upload Helper
 * Validates file uploads based on system configuration
 */
class UploadHelper
{
    /**
     * Get max upload size from configuration
     */
    public static function getMaxUploadSize(): int
    {
        $config = DB::table('configurations')
            ->where('name', '=', 'file_upload_limit')
            ->first();

        if (!$config || !$config['is_enabled']) {
            // Default to PHP's upload_max_filesize
            return self::parseSize(ini_get('upload_max_filesize'));
        }

        $configData = json_decode($config['config'] ?? '{}', true);
        $maxSize = $configData['max_size'] ?? '10MB';

        return self::parseSize($maxSize);
    }

    /**
     * Validate uploaded file size
     */
    public static function validateFileSize(array $file): array
    {
        $maxSize = self::getMaxUploadSize();
        $fileSize = $file['size'] ?? 0;

        if ($fileSize > $maxSize) {
            return [
                'valid' => false,
                'error' => sprintf(
                    'File quá lớn. Kích thước tối đa: %s',
                    self::formatSize($maxSize)
                )
            ];
        }

        return ['valid' => true];
    }

    /**
     * Parse size string to bytes (e.g., "10MB" -> 10485760)
     */
    private static function parseSize(string $size): int
    {
        $size = trim($size);
        $unit = strtoupper(substr($size, -2));
        $value = (int)$size;

        switch ($unit) {
            case 'GB':
                return $value * 1024 * 1024 * 1024;
            case 'MB':
                return $value * 1024 * 1024;
            case 'KB':
                return $value * 1024;
            default:
                // Check for single letter (G, M, K)
                $unit = strtoupper(substr($size, -1));
                if ($unit === 'G') return $value * 1024 * 1024 * 1024;
                if ($unit === 'M') return $value * 1024 * 1024;
                if ($unit === 'K') return $value * 1024;
                return $value; // Bytes
        }
    }

    /**
     * Format bytes to human readable size
     */
    private static function formatSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Get formatted max upload size for display
     */
    public static function getMaxUploadSizeFormatted(): string
    {
        return self::formatSize(self::getMaxUploadSize());
    }
}

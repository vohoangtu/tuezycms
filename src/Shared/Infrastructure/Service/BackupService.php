<?php

declare(strict_types=1);

namespace Shared\Infrastructure\Service;

use Shared\Infrastructure\Database\DB;

/**
 * Database Backup Service
 * Creates MySQL database backups
 */
class BackupService
{
    private string $backupDir;

    public function __construct()
    {
        $this->backupDir = dirname(__DIR__, 4) . '/storage/backups';
        
        // Create backup directory if it doesn't exist
        if (!is_dir($this->backupDir)) {
            mkdir($this->backupDir, 0755, true);
        }
    }

    /**
     * Create database backup
     */
    public function createBackup(): array
    {
        // Check if auto backup is enabled
        $config = DB::table('configurations')
            ->where('name', '=', 'auto_backup')
            ->first();

        if (!$config || !$config['is_enabled']) {
            return [
                'success' => false,
                'message' => 'Auto backup is disabled'
            ];
        }

        try {
            $filename = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
            $filepath = $this->backupDir . '/' . $filename;

            // Get database credentials from environment or config
            $host = $_ENV['DB_HOST'] ?? 'localhost';
            $database = $_ENV['DB_NAME'] ?? 'tuzycms';
            $username = $_ENV['DB_USER'] ?? 'root';
            $password = $_ENV['DB_PASS'] ?? '';

            // Build mysqldump command
            $command = sprintf(
                'mysqldump -h %s -u %s %s %s > %s 2>&1',
                escapeshellarg($host),
                escapeshellarg($username),
                !empty($password) ? '-p' . escapeshellarg($password) : '',
                escapeshellarg($database),
                escapeshellarg($filepath)
            );

            // Execute backup
            exec($command, $output, $returnCode);

            if ($returnCode !== 0) {
                throw new \Exception('Mysqldump failed: ' . implode("\n", $output));
            }

            // Verify file was created
            if (!file_exists($filepath) || filesize($filepath) === 0) {
                throw new \Exception('Backup file was not created or is empty');
            }

            // Clean old backups (keep last 10)
            $this->cleanOldBackups(10);

            return [
                'success' => true,
                'message' => 'Backup created successfully',
                'filename' => $filename,
                'filepath' => $filepath,
                'size' => filesize($filepath)
            ];

        } catch (\Exception $e) {
            error_log('Backup error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Backup failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Clean old backup files
     */
    private function cleanOldBackups(int $keep = 10): void
    {
        $files = glob($this->backupDir . '/backup_*.sql');
        
        if (count($files) <= $keep) {
            return;
        }

        // Sort by modification time (oldest first)
        usort($files, function($a, $b) {
            return filemtime($a) - filemtime($b);
        });

        // Delete oldest files
        $toDelete = count($files) - $keep;
        for ($i = 0; $i < $toDelete; $i++) {
            unlink($files[$i]);
        }
    }

    /**
     * List all backups
     */
    public function listBackups(): array
    {
        $files = glob($this->backupDir . '/backup_*.sql');
        $backups = [];

        foreach ($files as $file) {
            $backups[] = [
                'filename' => basename($file),
                'size' => filesize($file),
                'created_at' => date('Y-m-d H:i:s', filemtime($file))
            ];
        }

        // Sort by creation time (newest first)
        usort($backups, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });

        return $backups;
    }

    /**
     * Delete a backup file
     */
    public function deleteBackup(string $filename): bool
    {
        $filepath = $this->backupDir . '/' . basename($filename);
        
        if (!file_exists($filepath)) {
            return false;
        }

        return unlink($filepath);
    }
}

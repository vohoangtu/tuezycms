<?php

declare(strict_types=1);

namespace Shared\Infrastructure\Controller;

use Modules\User\Application\Service\AuthService;
use Shared\Infrastructure\Http\Request;
use Shared\Infrastructure\Http\Response;
use Shared\Infrastructure\Security\KeyValidator;
use Shared\Infrastructure\Service\BackupService;

/**
 * Backup Controller
 * Handles database backup operations
 */
class BackupController extends BaseController
{
    private BackupService $backupService;

    public function __construct(
        AuthService $authService,
        KeyValidator $keyValidator,
        Request $request,
        Response $response,
        BackupService $backupService
    ) {
        parent::__construct($authService, $keyValidator, $request, $response);
        $this->backupService = $backupService;
    }

    /**
     * Create new backup
     */
    public function create(): void
    {
        $result = $this->backupService->createBackup();
        
        if ($result['success']) {
            $this->json([
                'success' => true,
                'message' => $result['message'],
                'data' => [
                    'filename' => $result['filename'],
                    'size' => $result['size']
                ]
            ]);
        } else {
            $this->json([
                'success' => false,
                'message' => $result['message']
            ], 500);
        }
    }

    /**
     * List all backups
     */
    public function index(): void
    {
        $backups = $this->backupService->listBackups();
        
        $this->json([
            'success' => true,
            'data' => $backups
        ]);
    }

    /**
     * Delete a backup
     */
    public function delete(): void
    {
        $data = $this->request->json();
        $filename = $data['filename'] ?? '';

        if (empty($filename)) {
            $this->json([
                'success' => false,
                'message' => 'Filename is required'
            ], 400);
            return;
        }

        $result = $this->backupService->deleteBackup($filename);

        if ($result) {
            $this->json([
                'success' => true,
                'message' => 'Backup deleted successfully'
            ]);
        } else {
            $this->json([
                'success' => false,
                'message' => 'Failed to delete backup'
            ], 500);
        }
    }
}

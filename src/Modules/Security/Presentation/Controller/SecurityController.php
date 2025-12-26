<?php

declare(strict_types=1);

namespace Modules\Security\Presentation\Controller;

use Modules\Security\Application\Service\SecurityService;
use Shared\Infrastructure\Http\Request;
use Shared\Infrastructure\Http\Response;

class SecurityController
{
    private SecurityService $securityService;
    private Request $request;
    private Response $response;

    public function __construct(
        SecurityService $securityService,
        Request $request,
        Response $response
    ) {
        $this->securityService = $securityService;
        $this->request = $request;
        $this->response = $response;
    }

    public function index(): void
    {
        $limit = (int)$this->request->get('limit', 50);
        // Add filtering if needed
        $logs = $this->securityService->getRecentLogs($limit);

        // Map logs to array if they are entities
        $data = array_map(function($log) {
            return [
                'id' => $log->getId(),
                'action' => $log->getAction(),
                'ip_address' => $log->getIpAddress(),
                'user_id' => $log->getUserId(),
                'description' => $log->getDescription(),
                'level' => $log->getLevel(),
                'created_at' => $log->getCreatedAt()->format('Y-m-d H:i:s'),
            ];
        }, $logs);

        $this->response->success($data);
    }

    public function ips(): void
    {
        $limit = (int)$this->request->get('limit', 50);
        $ips = $this->securityService->getBlockedIps($limit);

        $data = array_map(function($ip) {
            return [
                'id' => $ip->getId(),
                'ip_address' => $ip->getIpAddress(),
                'reason' => $ip->getReason(),
                'blocked_by' => $ip->getBlockedBy(),
                'expires_at' => $ip->getExpiresAt()?->format('Y-m-d H:i:s'),
                'is_active' => $ip->isActive(),
                'created_at' => $ip->getCreatedAt()->format('Y-m-d H:i:s'),
            ];
        }, $ips);

        $this->response->success($data);
    }

    public function blockIp(): void
    {
        $ip = $this->request->input('ip_address');
        $reason = $this->request->input('reason', 'Manual block');
        $duration = $this->request->input('duration') ? (int)$this->request->input('duration') : null;

        if (!$ip) {
            $this->response->error('IP Address is required');
            return;
        }

        // Get current user ID from session? Or passed via middleware/auth service?
        // Assuming SessionManager has it or we can get it from AuthService if injected.
        $blockedBy = \Shared\Infrastructure\Session\SessionManager::get('user_id');

        $this->securityService->blockIp($ip, $reason, (int)$blockedBy, $duration);

        $this->response->success([], 'IP blocked successfully');
    }

    public function unblockIp(string $ip): void
    {
        $unblockedBy = \Shared\Infrastructure\Session\SessionManager::get('user_id');
        $this->securityService->unblockIp($ip, (int)$unblockedBy);

        $this->response->success([], 'IP unblocked successfully');
    }

    public function checkIntegrity(): void
    {
        // Simple check (Boolean)
        $isSecure = $this->securityService->checkIntegrity();
        $this->response->success(['is_secure' => $isSecure]);
    }

    public function scanIntegrity(): void
    {
        // Detailed scan
        try {
            $result = $this->securityService->runIntegrityScan();
            $this->response->success($result);
        } catch (\Throwable $e) {
            $this->response->error($e->getMessage());
        }
    }

    public function approveIntegrity(): void
    {
        // Update manifest
        try {
            $count = $this->securityService->approveIntegrityChanges();
            $this->response->success(['files_scanned' => $count], 'Manifest updated successfully');
        } catch (\Throwable $e) {
            $this->response->error($e->getMessage());
        }
    }

    // --- Tamper Protection Tools ---

    public function generateKeys(): void
    {
        try {
            $service = new \Modules\Security\Infrastructure\Service\TamperProtectionService();
            $keys = $service->generateKeys();

            // Save to storage
            $storageKeys = __DIR__ . '/../../../../../storage/security/keys';
            if (!is_dir($storageKeys)) {
                mkdir($storageKeys, 0700, true);
            }
            file_put_contents($storageKeys . '/private.pem', $keys['private']);
            file_put_contents($storageKeys . '/public.pem', $keys['public']);

            $this->response->success([], 'New Keypair Generated Successfully');
        } catch (\Throwable $e) {
            $this->response->error('Keygen Failed: ' . $e->getMessage());
        }
    }

    public function signSystem(): void
    {
        try {
            $service = new \Modules\Security\Infrastructure\Service\TamperProtectionService();
            $keyPath = __DIR__ . '/../../../../../storage/security/keys/private.pem';
            
            if (!file_exists($keyPath)) {
                throw new \Exception("Private Key not found. Please generate keys first.");
            }

            $privateKey = file_get_contents($keyPath);
            $signature = $service->signSource($privateKey);

            // Save Signature and Public Key for Runtime
            file_put_contents(__DIR__ . '/../../../../../integrity.sig', $signature);
            copy(__DIR__ . '/../../../../../storage/security/keys/public.pem', __DIR__ . '/../../../../../integrity.pub');

            $this->response->success([], 'System Signed Successfully! Integrity protection is active.');
        } catch (\Throwable $e) {
            $this->response->error('Signing Failed: ' . $e->getMessage());
        }
    }

    public function scanMalware(): void
    {
        try {
            $scanner = new \Modules\Security\Application\Service\MalwareScanner();
            $rootPath = dirname(__DIR__, 5); // Navigate to root
            $results = $scanner->scan($rootPath);

            $this->response->success(['results' => $results], 'Malware Scan Completed');
        } catch (\Throwable $e) {
            $this->response->error('Scan Failed: ' . $e->getMessage());
        }
    }
}

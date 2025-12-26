<?php

declare(strict_types=1);

namespace Modules\Security\Presentation\Controller;

use Modules\Security\Application\Service\SecurityService;
use Shared\Infrastructure\Http\Request;
use Shared\Infrastructure\Http\Response;

class SecurityPageController
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
        // Dashboard
        $logs = $this->securityService->getRecentLogs(10);
        $blockedIps = $this->securityService->getBlockedIps(5);
        $isSecure = $this->securityService->checkIntegrity();

        $this->response->view('admin/security/dashboard', [
            'pageTitle' => 'Security Dashboard',
            'logs' => $logs,
            'blockedIps' => $blockedIps,
            'isSecure' => $isSecure
        ]);
    }

    public function logs(): void
    {
        $limit = (int)$this->request->get('limit', 50);
        $logs = $this->securityService->getRecentLogs($limit);

        $this->response->view('admin/security/logs', [
            'pageTitle' => 'Security Logs',
            'logs' => $logs
        ]);
    }

    public function ips(): void
    {
        $ips = $this->securityService->getBlockedIps(100);

        $this->response->view('admin/security/ips', [
            'pageTitle' => 'Blocked IPs',
            'ips' => $ips
        ]);
    }

    public function integrity(): void
    {
        $isSecure = $this->securityService->checkIntegrity();

        $this->response->view('admin/security/integrity', [
            'pageTitle' => 'System Integrity',
            'isSecure' => $isSecure
        ]);
    }

    public function tamper(): void
    {
        $hasKey = file_exists(__DIR__ . '/../../../../../../storage/security/keys/private.pem');
        $isSigned = file_exists(__DIR__ . '/../../../../../../integrity.sig');
        
        $this->response->view('admin/security/tamper', [
            'pageTitle' => 'Anti-Tamper Tools',
            'hasKey' => $hasKey,
            'isSigned' => $isSigned
        ]);
    }

    public function malware(): void
    {
        $this->response->view('admin/security/malware', [
            'pageTitle' => 'Malware Scanner'
        ]);
    }
}

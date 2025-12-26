<?php

declare(strict_types=1);

namespace Modules\Security\Application\Middleware;

use Modules\Security\Application\Service\FileIntegrityScanner;
use Shared\Infrastructure\Http\Request;
use Shared\Infrastructure\Http\Response;

class CriticalIntegrityMiddleware
{
    private FileIntegrityScanner $scanner;
    
    // Critical files relative to root
    private array $criticalFiles = [
        '/public/index.php',
        '/src/Core/Container/AppServiceProvider.php',
        '/src/Modules/Security/Application/Service/SecurityService.php',
        '/src/Modules/Security/Infrastructure/Service/TamperProtectionService.php'
    ];

    public function __construct(FileIntegrityScanner $scanner)
    {
        $this->scanner = $scanner;
    }

    public function handle(Request $request, Response $response, callable $next)
    {
        // Skip check for CLI or specific safe routes if needed
        // But for safety, we check on every web request.
        
        // Use a simple file cache to avoid hashing on EVERY request (e.g. check every 5 mins)
        // Or check mtime. But hashing is safer.
        // For performance, we can implement memory caching or file caching here.
        // For now, let's implement a simple cache using a temp file.
        
        $cacheFile = sys_get_temp_dir() . '/tuzy_integrity_check_ts';
        $lastCheck = file_exists($cacheFile) ? (int)file_get_contents($cacheFile) : 0;
        
        // check every 60 seconds
        if (time() - $lastCheck > 60) {
            if (!$this->scanner->verifySpecificFiles($this->criticalFiles)) {
                http_response_code(503);
                die('<h1>System Error</h1><p>Critical Integrity Violation. Core files have been modified.</p>');
            }
            file_put_contents($cacheFile, time());
        }

        return $next($request, $response);
    }
}

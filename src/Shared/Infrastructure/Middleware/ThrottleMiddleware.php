<?php

declare(strict_types=1);

namespace Shared\Infrastructure\Middleware;

use Shared\Infrastructure\Http\Request;
use Shared\Infrastructure\Http\Response;
use Shared\Infrastructure\Database\DB;
use Shared\Infrastructure\Cache\Cache;

/**
 * API Rate Limit Middleware
 * Implements token bucket algorithm for rate limiting
 */
class ThrottleMiddleware
{
    private Response $response;
    private Cache $cache;

    public function __construct(Response $response, Cache $cache)
    {
        $this->response = $response;
        $this->cache = $cache;
    }

    /**
     * Handle rate limiting
     */
    public function handle(Request $request, callable $next): mixed
    {
        // Get rate limit configuration
        $config = DB::table('configurations')
            ->where('name', '=', 'api_rate_limit')
            ->first();

        if (!$config || !$config['is_enabled']) {
            // Rate limiting is disabled
            return $next($request);
        }

        $configData = json_decode($config['config'] ?? '{}', true);
        $limit = (int)($configData['limit'] ?? 100); // Max requests
        $window = (int)($configData['window'] ?? 60); // Time window in seconds

        // Get client identifier (IP address)
        $identifier = $this->getClientIdentifier($request);
        $key = "rate_limit:{$identifier}";

        // Get current request count
        $current = $this->cache->get($key, 0);

        if ($current >= $limit) {
            // Rate limit exceeded
            $this->response->json([
                'success' => false,
                'message' => 'Too many requests. Please try again later.',
                'retry_after' => $window
            ], 429);
            
            header('Retry-After: ' . $window);
            return null;
        }

        // Increment counter
        $this->cache->set($key, $current + 1, $window);

        // Add rate limit headers
        header('X-RateLimit-Limit: ' . $limit);
        header('X-RateLimit-Remaining: ' . ($limit - $current - 1));
        header('X-RateLimit-Reset: ' . (time() + $window));

        return $next($request);
    }

    /**
     * Get client identifier for rate limiting
     */
    private function getClientIdentifier(Request $request): string
    {
        // Use IP address as identifier
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        
        // If behind proxy, try to get real IP
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $ip = trim($ips[0]);
        } elseif (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        }

        return $ip;
    }
}

<?php

declare(strict_types=1);

namespace Shared\Infrastructure\Http;

/**
 * HTTP Request wrapper
 */
class Request
{
    private array $query;
    private array $post;
    private array $server;
    private array $files;
    private array $headers;
    private ?array $jsonBody = null;

    public function __construct()
    {
        $this->query = $_GET ?? [];
        $this->post = $_POST ?? [];
        $this->server = $_SERVER ?? [];
        $this->files = $_FILES ?? [];
        $this->headers = $this->parseHeaders();
        $this->jsonBody = $this->parseJsonBody();
    }

    /**
     * Get a query parameter
     *
     * @param string|null $key
     * @param mixed $default
     * @return mixed
     */
    public function get(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $this->query;
        }

        return $this->query[$key] ?? $default;
    }

    /**
     * Get a POST parameter
     *
     * @param string|null $key
     * @param mixed $default
     * @return mixed
     */
    public function post(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $this->post;
        }

        return $this->post[$key] ?? $default;
    }

    /**
     * Get input from query, post, or JSON body
     *
     * @param string|null $key
     * @param mixed $default
     * @return mixed
     */
    public function input(?string $key = null, mixed $default = null): mixed
    {
        // Check JSON body first
        if ($this->jsonBody !== null) {
            if ($key === null) {
                return $this->jsonBody;
            }

            if (isset($this->jsonBody[$key])) {
                return $this->jsonBody[$key];
            }
        }

        // Check POST
        if (isset($this->post[$key])) {
            return $this->post[$key];
        }

        // Check GET
        if (isset($this->query[$key])) {
            return $this->query[$key];
        }

        return $default;
    }

    /**
     * Get all input data
     *
     * @return array
     */
    public function all(): array
    {
        return array_merge($this->query, $this->post, $this->jsonBody ?? []);
    }

    /**
     * Check if input has a key
     *
     * @param string|array $key
     * @return bool
     */
    public function has(string|array $key): bool
    {
        $keys = is_array($key) ? $key : [$key];

        foreach ($keys as $k) {
            if (!isset($this->query[$k]) && !isset($this->post[$k]) && 
                ($this->jsonBody === null || !isset($this->jsonBody[$k]))) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get a file upload
     *
     * @param string $key
     * @return array|null
     */
    public function file(string $key): ?array
    {
        return $this->files[$key] ?? null;
    }

    /**
     * Get all files
     *
     * @return array
     */
    public function files(): array
    {
        return $this->files;
    }

    /**
     * Get HTTP method
     *
     * @return string
     */
    public function method(): string
    {
        return $this->server['REQUEST_METHOD'] ?? 'GET';
    }

    /**
     * Get request path
     *
     * @return string
     */
    public function path(): string
    {
        $path = parse_url($this->server['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        return $path ?: '/';
    }

    /**
     * Get full URL
     *
     * @return string
     */
    public function url(): string
    {
        $scheme = $this->server['HTTPS'] ?? 'off';
        $scheme = ($scheme === 'on' || $scheme === '1') ? 'https' : 'http';
        $host = $this->server['HTTP_HOST'] ?? $this->server['SERVER_NAME'] ?? 'localhost';
        $uri = $this->server['REQUEST_URI'] ?? '/';

        return $scheme . '://' . $host . $uri;
    }

    /**
     * Get query string
     *
     * @return string
     */
    public function queryString(): string
    {
        return $this->server['QUERY_STRING'] ?? '';
    }

    /**
     * Check if request is JSON
     *
     * @return bool
     */
    public function isJson(): bool
    {
        $contentType = $this->header('Content-Type', '');
        return str_contains($contentType, 'application/json');
    }

    /**
     * Get a header value
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function header(string $key, mixed $default = null): mixed
    {
        $key = strtolower($key);
        return $this->headers[$key] ?? $default;
    }

    /**
     * Get all headers
     *
     * @return array
     */
    public function headers(): array
    {
        return $this->headers;
    }

    /**
     * Get IP address
     *
     * @return string
     */
    public function ip(): string
    {
        $ipKeys = [
            'HTTP_CF_CONNECTING_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_REAL_IP',
            'REMOTE_ADDR'
        ];

        foreach ($ipKeys as $key) {
            if (!empty($this->server[$key])) {
                $ip = $this->server[$key];
                // Handle comma-separated IPs (X-Forwarded-For)
                if (str_contains($ip, ',')) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                return $ip;
            }
        }

        return '0.0.0.0';
    }

    /**
     * Parse headers from $_SERVER
     *
     * @return array
     */
    private function parseHeaders(): array
    {
        $headers = [];

        foreach ($this->server as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $header = str_replace('_', '-', substr($key, 5));
                $headers[strtolower($header)] = $value;
            } elseif (in_array($key, ['CONTENT_TYPE', 'CONTENT_LENGTH', 'CONTENT_MD5'])) {
                $header = str_replace('_', '-', $key);
                $headers[strtolower($header)] = $value;
            }
        }

        return $headers;
    }

    /**
     * Parse JSON body from php://input
     *
     * @return array|null
     */
    private function parseJsonBody(): ?array
    {
        if (!$this->isJson()) {
            return null;
        }

        $input = file_get_contents('php://input');
        if (empty($input)) {
            return null;
        }

        $data = json_decode($input, true);
        return is_array($data) ? $data : null;
    }

    /**
     * Get session data
     *
     * @param string|null $key
     * @param mixed $default
     * @return mixed
     */
    public function session(?string $key = null, mixed $default = null): mixed
    {
        // Start session if not started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if ($key === null) {
            return $_SESSION ?? [];
        }

        return $_SESSION[$key] ?? $default;
    }

    /**
     * Set session data
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function setSession(string $key, mixed $value): void
    {
        // Start session if not started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION[$key] = $value;
    }

    /**
     * Check if session has a key
     *
     * @param string $key
     * @return bool
     */
    public function hasSession(string $key): bool
    {
        // Start session if not started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        return isset($_SESSION[$key]);
    }

    /**
     * Remove session key
     *
     * @param string $key
     * @return void
     */
    public function removeSession(string $key): void
    {
        // Start session if not started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        unset($_SESSION[$key]);
    }

    /**
     * Destroy session
     *
     * @return void
     */
    public function destroySession(): void
    {
        // Start session if not started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION = [];
        
        // Delete session cookie
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();
    }
}


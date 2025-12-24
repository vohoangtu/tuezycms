<?php

declare(strict_types=1);

namespace Shared\Infrastructure\Http;

/**
 * HTTP Response handler
 */
class Response
{
    private int $statusCode = 200;
    private array $headers = [];
    private ?string $content = null;

    /**
     * Set HTTP status code
     *
     * @param int $code
     * @return self
     */
    public function status(int $code): self
    {
        $this->statusCode = $code;
        return $this;
    }

    /**
     * Set a header
     *
     * @param string $name
     * @param string $value
     * @return self
     */
    public function header(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        return $this;
    }

    /**
     * Set multiple headers
     *
     * @param array $headers
     * @return self
     */
    public function headers(array $headers): self
    {
        foreach ($headers as $name => $value) {
            $this->headers[$name] = $value;
        }
        return $this;
    }

    /**
     * Send JSON response
     *
     * @param array $data
     * @param int $status
     * @return void
     */
    public function json(array $data, int $status = 200): void
    {
        $this->status($status);
        $this->header('Content-Type', 'application/json');
        $this->send(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    /**
     * Redirect to URL
     *
     * @param string $url
     * @param int $status
     * @return void
     */
    public function redirect(string $url, int $status = 302): void
    {
        $this->status($status);
        $this->header('Location', $url);
        $this->send();
    }

    /**
     * Render a view
     *
     * @param string $view
     * @param array $data
     * @param bool $useMasterLayout Whether to use master admin layout
     * @return void
     */
    public function view(string $view, array $data = [], bool $useMasterLayout = null): void
    {
        // Auto-detect if we should use master layout for admin views
        if ($useMasterLayout === null) {
            // Login and auth pages should not use master layout
            $useMasterLayout = str_starts_with($view, 'admin/') && $view !== 'admin/login';
        }

        // Use master layout renderer for all admin views
        if (str_starts_with($view, 'admin/')) {
            $renderer = new \Shared\Infrastructure\View\MasterLayoutRenderer();
            // Use 'none' layout for login, 'admin-main' for others
            $layout = $useMasterLayout ? 'admin-main' : 'none';
            $html = $renderer->render($view, $data, $layout);
            $this->send($html);
            return;
        }
        
        // Handle public views - check both new location and old location
        extract($data);
        
        // First try: src/Shared/Presentation/View/ (new structure)
        $viewFile = __DIR__ . '/../../Presentation/View/' . $view . '.php';
        
        // Second try: public/templates/ (legacy location)
        if (!file_exists($viewFile) && str_starts_with($view, 'public/')) {
            $viewName = substr($view, 7); // Remove 'public/' prefix
            $viewFile = __DIR__ . '/../../../public/templates/' . $viewName . '.php';
        } elseif (!file_exists($viewFile)) {
            // Try without any prefix
            $viewFile = __DIR__ . '/../../../public/templates/' . $view . '.php';
        }

        if (!file_exists($viewFile)) {
            throw new \RuntimeException("View not found: {$view}");
        }

        include $viewFile;
        exit;
    }

    /**
     * Render a view with landing page layout
     *
     * @param string $view View name (e.g., 'landing')
     * @param array $data Data to pass to view
     * @return void
     */
    public function landingView(string $view, array $data = []): void
    {
        $renderer = new \Shared\Infrastructure\View\MasterLayoutRenderer();
        $html = $renderer->render($view, $data, 'landing-layout');
        $this->send($html);
    }


    /**
     * Set a cookie
     *
     * @param string $name
     * @param string $value
     * @param int $expire
     * @param string $path
     * @param string|null $domain
     * @param bool $secure
     * @param bool $httpOnly
     * @return self
     */
    public function cookie(
        string $name,
        string $value,
        int $expire = 0,
        string $path = '/',
        ?string $domain = null,
        bool $secure = false,
        bool $httpOnly = true
    ): self {
        setcookie($name, $value, $expire, $path, $domain, $secure, $httpOnly);
        return $this;
    }

    /**
     * Send the response
     *
     * @param string|null $content
     * @return void
     */
    public function send(?string $content = null): void
    {
        http_response_code($this->statusCode);

        foreach ($this->headers as $name => $value) {
            header("{$name}: {$value}");
        }

        if ($content !== null) {
            echo $content;
        } elseif ($this->content !== null) {
            echo $this->content;
        }

        exit;
    }

    /**
     * Set response content
     *
     * @param string $content
     * @return self
     */
    public function content(string $content): self
    {
        $this->content = $content;
        return $this;
    }

    /**
     * Send success JSON response
     *
     * @param array $data
     * @param string $message
     * @param int $status
     * @return void
     */
    public function success(array $data = [], string $message = '', int $status = 200): void
    {
        $response = [
            'success' => true,
            'data' => $data
        ];

        if (!empty($message)) {
            $response['message'] = $message;
        }

        $this->json($response, $status);
    }

    /**
     * Send error JSON response
     *
     * @param string $message
     * @param int $status
     * @param array|null $errors
     * @return void
     */
    public function error(string $message, int $status = 400, ?array $errors = null): void
    {
        $response = [
            'success' => false,
            'message' => $message
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        $this->json($response, $status);
    }

    /**
     * Send paginated JSON response
     *
     * @param array $items
     * @param int $total
     * @param int $page
     * @param int $perPage
     * @return void
     */
    public function paginate(array $items, int $total, int $page, int $perPage): void
    {
        $lastPage = (int) ceil($total / $perPage);

        $response = [
            'success' => true,
            'data' => $items,
            'meta' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => $lastPage,
                'from' => ($page - 1) * $perPage + 1,
                'to' => min($page * $perPage, $total)
            ]
        ];

        $this->json($response, 200);
    }
}


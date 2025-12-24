<?php

declare(strict_types=1);

namespace Core\Routing;

use Shared\Infrastructure\Http\Request;

class Router
{
    private Request $request;
    private array $routes = [];
    private ?string $currentLocale = null;

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->detectLocale();
    }

    /**
     * Detect locale from URL or default to 'vi'
     */
    private function detectLocale(): void
    {
        $path = $this->request->path();
        $path = trim($path, '/');
        
        // Check if path starts with locale (e.g., /en/, /vi/)
        $segments = explode('/', $path);
        $firstSegment = $segments[0] ?? '';
        
        $supportedLocales = ['vi', 'en'];
        if (in_array($firstSegment, $supportedLocales, true)) {
            $this->currentLocale = $firstSegment;
        } else {
            $this->currentLocale = 'vi'; // Default to Vietnamese
        }
    }

    /**
     * Get current locale
     */
    public function getLocale(): string
    {
        return $this->currentLocale;
    }

    /**
     * Get path without locale prefix
     */
    public function getPath(): string
    {
        $path = $this->request->path();
        $path = trim($path, '/');
        
        $segments = explode('/', $path);
        $firstSegment = $segments[0] ?? '';
        
        $supportedLocales = ['vi', 'en'];
        if (in_array($firstSegment, $supportedLocales, true)) {
            array_shift($segments);
            return implode('/', $segments);
        }
        
        return $path;
    }

    /**
     * Match route based on path
     */
    public function match(string $path): ?array
    {
        $path = trim($path, '/');
        
        // Exact matches first
        $exactRoutes = [
            '' => ['type' => 'home', 'handler' => 'home'],
            'lien-he' => ['type' => 'page', 'handler' => 'contact', 'slug' => 'lien-he'],
            'contact' => ['type' => 'page', 'handler' => 'contact', 'slug' => 'contact'],
            'tin-tuc' => ['type' => 'article_type', 'handler' => 'articles', 'type_slug' => 'tin-tuc'],
            'news' => ['type' => 'article_type', 'handler' => 'articles', 'type_slug' => 'tin-tuc'],
            'dich-vu' => ['type' => 'article_type', 'handler' => 'articles', 'type_slug' => 'dich-vu'],
            'services' => ['type' => 'article_type', 'handler' => 'articles', 'type_slug' => 'dich-vu'],
            'kien-thuc' => ['type' => 'article_type', 'handler' => 'articles', 'type_slug' => 'kien-thuc'],
            'knowledge' => ['type' => 'article_type', 'handler' => 'articles', 'type_slug' => 'kien-thuc'],
            'san-pham' => ['type' => 'products', 'handler' => 'products'],
            'products' => ['type' => 'products', 'handler' => 'products'],
        ];

        if (isset($exactRoutes[$path])) {
            return $exactRoutes[$path];
        }

        // Dynamic routes: article detail
        if (preg_match('/^(tin-tuc|news|dich-vu|services|kien-thuc|knowledge)\/([a-z0-9-]+)$/i', $path, $matches)) {
            return [
                'type' => 'article',
                'handler' => 'article_detail',
                'slug' => $matches[2],
                'type_slug' => $matches[1]
            ];
        }

        // Dynamic routes: Try as product or article slug
        // Slug can be same for both product and article, so we need to check both
        // Priority: Product first, then Article
        if (preg_match('/^[a-z0-9-]+$/i', $path)) {
            return [
                'type' => 'content',
                'handler' => 'content_detail',
                'slug' => $path
            ];
        }

        return null;
    }

    /**
     * Generate URL with locale
     */
    public function url(string $path, ?string $locale = null): string
    {
        $locale = $locale ?? $this->currentLocale;
        $path = trim($path, '/');
        
        if ($locale === 'vi') {
            return '/' . $path;
        }
        
        return '/' . $locale . '/' . $path;
    }
}


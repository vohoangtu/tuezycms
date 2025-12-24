<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Core\Container\ServiceContainer;
use Core\Container\AppServiceProvider;
use Shared\Infrastructure\Http\Request;
use Shared\Infrastructure\Http\Response;
use Shared\Infrastructure\Exception\ExceptionHandler;
use Core\Routing\Router;
use Shared\Infrastructure\Security\KeyValidator;
use Modules\Article\Application\Service\ArticleService;
use Modules\Product\Infrastructure\Repository\ProductRepository;
use Modules\Article\Infrastructure\Repository\PageRepository;
use Shared\Infrastructure\I18n\Translator;
use Shared\Infrastructure\Exception\NotFoundException;

// Initialize Service Container
$container = ServiceContainer::getInstance();

// Register services via AppServiceProvider
$appServiceProvider = new AppServiceProvider($container);
$appServiceProvider->register();

// Register event listeners
\Shared\Infrastructure\Event\EventServiceProvider::register();

// Set up global exception handling
$request = $container->make(Request::class);
$response = $container->make(Response::class);
$exceptionHandler = $container->make(ExceptionHandler::class);

set_exception_handler(function (\Throwable $e) use ($exceptionHandler) {
    $exceptionHandler->handle($e);
});

// Validate source code integrity
$keyValidator = $container->make(KeyValidator::class);
if (!$keyValidator->validateSourceIntegrity()) {
    $response->status(403)->send('Source code integrity check failed. Website disabled.');
}

// Initialize router and translator
$router = new Router($request);
$translator = Translator::getInstance();
$translator->setLocale($router->getLocale());

// Get path without locale
$path = $router->getPath();

// Get services from container
$articleService = $container->make(ArticleService::class);
$productRepository = $container->make(ProductRepository::class);
$pageRepository = $container->make(PageRepository::class);

// Match route
$route = $router->match($path);

if ($route === null) {
    throw new NotFoundException("Page not found");
}

// Handle route using match expression
try {
    match ($route['handler']) {
        'home' => (function() use ($articleService, $productRepository, $translator, $router, $response) {
            $articles = $articleService->listArticles(6, 0, $router->getLocale());
            $products = $productRepository->findAll(8, 0, $router->getLocale());
            
            $response->landingView('landing', [
                'articles' => $articles,
                'products' => $products,
                'translator' => $translator,
                'router' => $router,
                'pageTitle' => 'Welcome to TuzyCMS'
            ]);
        })(),
        
        'articles' => (function() use ($route, $articleService, $router, $translator, $response) {
            $typeSlug = $route['type_slug'] ?? 'tin-tuc';
            $articles = $articleService->listArticlesByTypeSlug($typeSlug, 20, 0, $router->getLocale());
            
            $response->view('articles', [
                'articles' => $articles,
                'typeSlug' => $typeSlug,
                'translator' => $translator,
                'router' => $router
            ]);
        })(),
        
        'article_detail' => (function() use ($route, $articleService, $router, $translator, $response) {
            $slug = $route['slug'] ?? '';
            $article = $articleService->getArticleBySlug($slug, $router->getLocale());
            
            if ($article) {
                $response->view('article', [
                    'article' => $article,
                    'router' => $router,
                    'translator' => $translator
                ]);
            } else {
                throw new NotFoundException("Article not found");
            }
        })(),
        
        'products' => (function() use ($productRepository, $router, $translator, $response) {
            $products = $productRepository->findAll(20, 0, $router->getLocale());
            
            $response->view('products', [
                'products' => $products,
                'translator' => $translator,
                'router' => $router
            ]);
        })(),
        
        'product_detail' => (function() use ($route, $productRepository, $router, $translator, $response) {
            $slug = $route['slug'] ?? '';
            $product = $productRepository->findBySlug($slug, $router->getLocale());
            
            if ($product) {
                $response->view('product', [
                    'product' => $product,
                    'router' => $router,
                    'translator' => $translator
                ]);
            } else {
                throw new NotFoundException("Product not found");
            }
        })(),
        
        'content_detail' => (function() use ($route, $articleService, $productRepository, $router, $translator, $response) {
            // Slug can match both product and article
            // Priority: Product first, then Article
            $slug = $route['slug'] ?? '';
            $locale = $router->getLocale();
            
            // Try product first
            $product = $productRepository->findBySlug($slug, $locale);
            if ($product) {
                $response->view('product', [
                    'product' => $product,
                    'router' => $router,
                    'translator' => $translator
                ]);
                return;
            }
            
            // If not product, try article
            $article = $articleService->getArticleBySlug($slug, $locale);
            if ($article) {
                $response->view('article', [
                    'article' => $article,
                    'router' => $router,
                    'translator' => $translator
                ]);
                return;
            }
            
            // Not found
            throw new NotFoundException("Content not found");
        })(),
        
        'contact' => (function() use ($route, $pageRepository, $router, $translator, $response) {
            $slug = $route['slug'] ?? 'lien-he';
            $page = $pageRepository->findBySlug($slug, $router->getLocale());
            
            if ($page) {
                $response->view('page', [
                    'page' => $page,
                    'router' => $router,
                    'translator' => $translator
                ]);
            } else {
                throw new NotFoundException("Page not found");
            }
        })(),
        
        default => throw new NotFoundException("Page not found"),
    };
} catch (\Throwable $e) {
    $exceptionHandler->handle($e);
}

<?php

declare(strict_types=1);

namespace Core\Container;

use Modules\Article\Application\Service\ArticleService;
use Modules\User\Application\Service\AuthService;
use Modules\Order\Application\Service\CartService;
use Modules\Media\Application\Service\MediaService;
use Modules\Article\Application\Service\ModuleService;
use Modules\Order\Application\Service\OrderService;
use Modules\Product\Application\Service\ProductService;
use Modules\Promotion\Application\Service\PromotionService;
use Modules\Article\Application\Service\SeoService;
use Modules\Article\Application\Service\SiteSettingsService;
use Modules\Article\Application\Service\WarehouseService;
use Core\Routing\RouteRegistry;
use Core\Routing\RouteDispatcher;
use Shared\Infrastructure\Database\DatabaseConnection;
use Modules\Article\Infrastructure\Repository\ArticleRepository;
use Modules\Article\Infrastructure\Repository\ArticleTypeRepository;
use Modules\Media\Infrastructure\Repository\MediaRepository;
use Modules\Article\Infrastructure\Repository\ModuleRepository;
use Modules\Order\Infrastructure\Repository\OrderRepository;
use Modules\Product\Infrastructure\Repository\ProductCategoryRepository;
use Modules\Product\Infrastructure\Repository\ProductRepository;
use Modules\Promotion\Infrastructure\Repository\PromotionRepository;
use Modules\Article\Infrastructure\Repository\SeoRepository;
use Modules\User\Infrastructure\Repository\UserRepository;
use Modules\Authorization\Infrastructure\Repository\RoleRepository;
use Modules\Authorization\Infrastructure\Repository\PermissionRepository;
use Modules\Article\Infrastructure\Repository\WarehouseRepository;
use Shared\Infrastructure\Security\ContentEncryption;
use Shared\Infrastructure\Security\KeyValidator;
use Shared\Infrastructure\Media\FileUploader;
use Shared\Infrastructure\Media\ImageProcessor;
use Shared\Infrastructure\Storage\LocalFileStorage;
use Shared\Infrastructure\Middleware\AuthMiddleware;

/**
 * Application Service Provider
 * Registers all services and repositories
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register services
     *
     * @return void
     */
    public function register(): void
    {
        $container = $this->container;

        // Register infrastructure services first
        $container->singleton(ContentEncryption::class, function() {
            return new ContentEncryption();
        });
        $container->singleton(KeyValidator::class, function() {
            return new KeyValidator();
        });
        $container->singleton(LocalFileStorage::class, LocalFileStorage::class);
        $container->singleton(ImageProcessor::class, ImageProcessor::class);
        $container->singleton(FileUploader::class, function($container) {
            return new FileUploader(
                $container->make(LocalFileStorage::class),
                $container->make(ImageProcessor::class)
            );
        });

        // Register Routing Services
        $container->singleton(RouteRegistry::class, RouteRegistry::class);
        $container->singleton(RouteDispatcher::class, function($container) {
            return new RouteDispatcher(
                $container->make(RouteRegistry::class),
                $container
            );
        });

        // Register repositories as singletons
        $container->singleton(ArticleRepository::class, function($container) {
            return new ArticleRepository(
                $container->make(ContentEncryption::class)
            );
        });
        $container->singleton(ArticleTypeRepository::class, ArticleTypeRepository::class);
        $container->singleton(MediaRepository::class, MediaRepository::class);
        $container->singleton(ModuleRepository::class, ModuleRepository::class);
        $container->singleton(OrderRepository::class, OrderRepository::class);
        $container->singleton(ProductRepository::class, function($container) {
            return new ProductRepository(
                $container->make(ContentEncryption::class)
            );
        });
        $container->singleton(ProductCategoryRepository::class, ProductCategoryRepository::class);
        $container->singleton(PromotionRepository::class, PromotionRepository::class);
        $container->singleton(SeoRepository::class, SeoRepository::class);
        $container->singleton(UserRepository::class, UserRepository::class);
        $container->singleton(RoleRepository::class, RoleRepository::class);
        $container->singleton(PermissionRepository::class, PermissionRepository::class);
        $container->singleton(WarehouseRepository::class, WarehouseRepository::class);
        $container->singleton(\Modules\Article\Infrastructure\Repository\PageRepository::class, function($container) {
            return new \Modules\Article\Infrastructure\Repository\PageRepository(
                $container->make(ContentEncryption::class)
            );
        });

        // Register services as singletons
        $container->singleton(ArticleService::class, ArticleService::class);
        
        // Register AuthorizationService
        $container->singleton(\Modules\Authorization\Application\Service\AuthorizationService::class, function($container) {
            return new \Modules\Authorization\Application\Service\AuthorizationService(
                $container->make(UserRepository::class),
                $container->make(RoleRepository::class),
                $container->make(PermissionRepository::class)
            );
        });
        
        // Register AuthService with AuthorizationService
        $container->singleton(AuthService::class, function($container) {
            $authService = new AuthService(
                $container->make(UserRepository::class),
                $container->make(\Modules\Authorization\Application\Service\AuthorizationService::class)
            );
            return $authService;
        });
        
        $container->singleton(CartService::class, CartService::class);
        $container->singleton(MediaService::class, function($container) {
            return new MediaService(
                $container->make(\Modules\Media\Infrastructure\Repository\MediaRepository::class),
                $container->make(FileUploader::class),
                $container->make(LocalFileStorage::class)
            );
        });
        $container->singleton(ModuleService::class, ModuleService::class);
        $container->singleton(OrderService::class, OrderService::class);
        $container->singleton(ProductService::class, ProductService::class);
        $container->singleton(PromotionService::class, PromotionService::class);
        $container->singleton(SeoService::class, SeoService::class);
        $container->singleton(SiteSettingsService::class, function($container) {
            return new SiteSettingsService(
                $container->make(MediaService::class)
            );
        });
        $container->singleton(WarehouseService::class, WarehouseService::class);

        // Register notification and utility services
        $container->singleton(\Shared\Infrastructure\Service\EmailService::class, \Shared\Infrastructure\Service\EmailService::class);
        $container->singleton(\Shared\Infrastructure\Service\SmsService::class, \Shared\Infrastructure\Service\SmsService::class);
        $container->singleton(\Shared\Infrastructure\Service\BackupService::class, \Shared\Infrastructure\Service\BackupService::class);

        // Register middleware
        $container->singleton(AuthMiddleware::class, function($container) {
            return new AuthMiddleware(
                $container->make(AuthService::class)
            );
        });

        $container->singleton(\Shared\Infrastructure\Middleware\SuperAdminMiddleware::class, function($container) {
            return new \Shared\Infrastructure\Middleware\SuperAdminMiddleware(
                $container->make(AuthService::class)
            );
        });

        $container->singleton(\Shared\Infrastructure\Middleware\MaintenanceMiddleware::class, function($container) {
            return new \Shared\Infrastructure\Middleware\MaintenanceMiddleware(
                $container->make(AuthService::class),
                $container->make(Response::class)
            );
        });

        $container->singleton(\Shared\Infrastructure\Middleware\SessionTimeoutMiddleware::class, function($container) {
            return new \Shared\Infrastructure\Middleware\SessionTimeoutMiddleware(
                $container->make(AuthService::class),
                $container->make(Response::class)
            );
        });

        $container->singleton(\Shared\Infrastructure\Middleware\ThrottleMiddleware::class, function($container) {
            return new \Shared\Infrastructure\Middleware\ThrottleMiddleware(
                $container->make(Response::class),
                $container->make(\Shared\Infrastructure\Cache\Cache::class)
            );
        });

        // Register Request and Response as factories (new instance per request)
        // Note: These should NOT be singletons as each request needs a new instance
        $container->bind(\Shared\Infrastructure\Http\Request::class, function() {
            return new \Shared\Infrastructure\Http\Request();
        });
        $container->bind(\Shared\Infrastructure\Http\Response::class, function() {
            return new \Shared\Infrastructure\Http\Response();
        });
        
        // Register ExceptionHandler as singleton (requires Response, but Response is created per-request)
        $container->singleton(\Shared\Infrastructure\Exception\ExceptionHandler::class, function($container) {
            $debug = ($_ENV['APP_DEBUG'] ?? 'false') === 'true';
            // Create a new Response instance for ExceptionHandler
            // ExceptionHandler will use this Response instance for error responses
            $response = new \Shared\Infrastructure\Http\Response();
            return new \Shared\Infrastructure\Exception\ExceptionHandler($response, $debug);
        });
    }

    /**
     * Bootstrap services
     *
     * @return void
     */
    public function boot(): void
    {
        // Any boot-time logic can go here
    }
}


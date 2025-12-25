<?php

declare(strict_types=1);

namespace Core\Routing;

use Core\Routing\RouteRegistry;
use Shared\Infrastructure\Controller\AdminController;
use Modules\User\Presentation\Controller\AuthController;
use Modules\Order\Presentation\Controller\CartController;
use Shared\Infrastructure\Controller\SettingsController;
use Shared\Infrastructure\Controller\SettingsPageController;

// Module-based controllers
use Modules\Article\Presentation\Controller\ArticleController;
use Modules\Article\Presentation\Controller\ArticlePageController;
use Modules\Product\Presentation\Controller\ProductController;
use Modules\Product\Presentation\Controller\ProductPageController;
use Modules\Order\Presentation\Controller\OrderController;
use Modules\Order\Presentation\Controller\OrderPageController;
use Modules\Promotion\Presentation\Controller\PromotionController;
use Modules\Promotion\Presentation\Controller\PromotionPageController;
use Modules\Media\Presentation\Controller\MediaController;
use Modules\Media\Presentation\Controller\MediaPageController;
use Modules\Authorization\Presentation\Controller\RoleController;
use Modules\Authorization\Presentation\Controller\RolePageController;
use Modules\Authorization\Presentation\Controller\PermissionController;
use Modules\User\Presentation\Controller\UserController;
use Modules\User\Presentation\Controller\UserPageController;

/**
 * Admin routes definition
 */
class AdminRoutes
{
    public static function register(RouteRegistry $registry): void
    {
        // Public routes (no auth required)
        $registry->get('/login', [AuthController::class, 'login'])->name('admin.login');
        $registry->post('/login', [AuthController::class, 'login']);
        $registry->get('/logout', [AuthController::class, 'logout'])->name('admin.logout');

        // Protected routes (require auth)
        $registry->group(['middleware' => ['auth']], function (RouteRegistry $registry) {
            // Page routes
            $registry->get('/', [AdminController::class, 'dashboard'])->name('admin.dashboard');
            $registry->get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
            $registry->get('/articles', [ArticlePageController::class, 'index'])->name('admin.articles');
            $registry->get('/products', [ProductPageController::class, 'index'])->name('admin.products');
            $registry->get('/orders', [OrderPageController::class, 'index'])->name('admin.orders');
            $registry->get('/promotions', [PromotionPageController::class, 'index'])->name('admin.promotions');
            $registry->get('/media', [MediaPageController::class, 'index'])->name('admin.media');
            $registry->get('/settings', [SettingsPageController::class, 'index'])->name('admin.settings');
            
            // RBAC Page routes
            $registry->get('/roles', [RolePageController::class, 'index'])->name('admin.roles');
            $registry->get('/users', [UserPageController::class, 'index'])->name('admin.users');
            $registry->get('/modules', [\Modules\Module\Presentation\Controller\ModulePageController::class, 'index'])->name('admin.modules');

            // API routes
            $registry->group(['prefix' => '/api'], function (RouteRegistry $registry) {
                // Articles API
                $registry->get('/articles', [ArticleController::class, 'index'])->name('api.articles.index');
                $registry->get('/articles/{id:\d+}', [ArticleController::class, 'show'])->name('api.articles.show');
                $registry->post('/articles', [ArticleController::class, 'store'])->name('api.articles.store');
                $registry->delete('/articles', [ArticleController::class, 'delete'])->name('api.articles.delete');

                // Products API
                $registry->get('/products', [ProductController::class, 'index'])->name('api.products.index');
                $registry->get('/products/{id:\d+}', [ProductController::class, 'show'])->name('api.products.show');
                $registry->post('/products', [ProductController::class, 'store'])->name('api.products.store');
                $registry->delete('/products', [ProductController::class, 'delete'])->name('api.products.delete');

                // Orders API
                $registry->get('/orders', [OrderController::class, 'index'])->name('api.orders.index');
                $registry->get('/orders/{id:\d+}', [OrderController::class, 'show'])->name('api.orders.show');

                // Promotions API
                $registry->get('/promotions', [PromotionController::class, 'index'])->name('api.promotions.index');
                $registry->get('/promotions/{id:\d+}', [PromotionController::class, 'show'])->name('api.promotions.show');

                // Cart API
                $registry->post('/cart/validate', [CartController::class, 'validate'])->name('api.cart.validate');

                // Media API
                $registry->get('/media', [MediaController::class, 'index'])->name('api.media.index');
                $registry->get('/media/{id:\d+}', [MediaController::class, 'show'])->name('api.media.show');
                $registry->post('/media', [MediaController::class, 'store'])->name('api.media.store');
                $registry->delete('/media', [MediaController::class, 'delete'])->name('api.media.delete');

                // Settings API
                $registry->get('/settings', [SettingsController::class, 'index'])->name('api.settings.index');
                $registry->post('/settings', [SettingsController::class, 'store'])->name('api.settings.store');
                
                // ========== RBAC API Routes ==========
                
                // Roles API
                $registry->get('/roles', [RoleController::class, 'index'])->name('api.roles.index');
                $registry->get('/roles/{id:\\d+}', [RoleController::class, 'show'])->name('api.roles.show');
                $registry->post('/roles', [RoleController::class, 'store'])->name('api.roles.store');
                $registry->put('/roles/{id:\\d+}', [RoleController::class, 'update'])->name('api.roles.update');
                $registry->delete('/roles/{id:\\d+}', [RoleController::class, 'destroy'])->name('api.roles.destroy');
                $registry->get('/roles/{id:\\d+}/permissions', [RoleController::class, 'getPermissions'])->name('api.roles.permissions');
                $registry->put('/roles/{id:\\d+}/permissions', [RoleController::class, 'updatePermissions'])->name('api.roles.permissions.update');
                
                // Permissions API
                $registry->get('/permissions', [PermissionController::class, 'index'])->name('api.permissions.index');
                $registry->get('/permissions/by-resource', [PermissionController::class, 'byResource'])->name('api.permissions.by-resource');
                $registry->get('/permissions/{id:\\d+}', [PermissionController::class, 'show'])->name('api.permissions.show');

                // Module Management API
                $registry->get('/modules', [\Modules\Module\Presentation\Controller\ModuleController::class, 'index'])->name('api.modules.index');
                $registry->get('/modules/by-category', [\Modules\Module\Presentation\Controller\ModuleController::class, 'byCategory'])->name('api.modules.by-category');
                $registry->get('/modules/{id:\\d+}', [\Modules\Module\Presentation\Controller\ModuleController::class, 'show'])->name('api.modules.show');
                $registry->put('/modules/{id:\\d+}/toggle', [\Modules\Module\Presentation\Controller\ModuleController::class, 'toggle'])->name('api.modules.toggle');
                $registry->put('/modules/{id:\\d+}/config', [\Modules\Module\Presentation\Controller\ModuleController::class, 'updateConfig'])->name('api.modules.config');
                
                // User CRUD API
                $registry->get('/users', [UserController::class, 'index'])->name('api.users.index');
                $registry->get('/users/{id:\\d+}', [UserController::class, 'show'])->name('api.users.show');
                $registry->post('/users', [UserController::class, 'store'])->name('api.users.store');
                $registry->put('/users/{id:\\d+}', [UserController::class, 'update'])->name('api.users.update');
                $registry->delete('/users/{id:\\d+}', [UserController::class, 'destroy'])->name('api.users.destroy');
                
                // User Details & Actions
                $registry->get('/users/{id:\\d+}/details', [UserController::class, 'getUserDetails'])->name('api.users.details');
                $registry->post('/users/{id:\\d+}/reset-password', [UserController::class, 'resetPassword'])->name('api.users.reset-password');
                $registry->post('/users/{id:\\d+}/send-email', [UserController::class, 'sendEmail'])->name('api.users.send-email');
                
                // Bulk actions
                $registry->post('/users/bulk-activate', [UserController::class, 'bulkActivate'])->name('api.users.bulk-activate');
                $registry->post('/users/bulk-deactivate', [UserController::class, 'bulkDeactivate'])->name('api.users.bulk-deactivate');
                $registry->post('/users/bulk-delete', [UserController::class, 'bulkDelete'])->name('api.users.bulk-delete');
                $registry->post('/users/bulk-assign-role', [UserController::class, 'bulkAssignRole'])->name('api.users.bulk-assign-role');
                
                // Export
                $registry->get('/users/export', [UserController::class, 'exportCsv'])->name('api.users.export');
            });
        });
    }
}


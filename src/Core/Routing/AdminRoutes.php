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

use Modules\Security\Presentation\Controller\SecurityController;
use Modules\Security\Presentation\Controller\SecurityPageController;

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
            
            // RBAC Page routes
            $registry->get('/roles', [RolePageController::class, 'index'])->name('admin.roles');
            $registry->get('/users', [UserPageController::class, 'index'])->name('admin.users');
            
            // Configurations Page (Module - có thể tắt qua Modules)
            // Configurations Page (Module - có thể tắt qua Modules)
            $registry->get('/configurations', [\Shared\Infrastructure\Controller\ConfigurationsPageController::class, 'index'])
                ->name('admin.configurations');
            
            // Security Pages
            $registry->get('/security', [SecurityPageController::class, 'index'])->name('admin.security');
            $registry->get('/security/logs', [SecurityPageController::class, 'logs'])->name('admin.security.logs');
            $registry->get('/security/ips', [SecurityPageController::class, 'ips'])->name('admin.security.ips');
            $registry->get('/security/integrity', [SecurityPageController::class, 'integrity'])->name('admin.security.integrity');
            $registry->get('/security/tamper', [SecurityPageController::class, 'tamper'])->name('admin.security.tamper');
            $registry->get('/security/malware', [SecurityPageController::class, 'malware'])->name('admin.security.malware');
            
            // Super Admin only routes
            $registry->get('/modules', [\Modules\Module\Presentation\Controller\ModulePageController::class, 'index'])
                ->name('admin.modules')
                ->middleware('super_admin');
            $registry->get('/settings', [SettingsPageController::class, 'index'])
                ->name('admin.settings')
                ->middleware('super_admin');

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

                // Settings API (Super Admin only)
                $registry->get('/settings', [SettingsController::class, 'index'])
                    ->name('api.settings.index')
                    ->middleware('super_admin');
                $registry->post('/settings', [SettingsController::class, 'store'])
                    ->name('api.settings.store')
                    ->middleware('super_admin');
                
                // Configurations API (Super Admin only)
                $registry->get('/configurations', [SettingsController::class, 'index'])
                    ->name('api.configurations.index')
                    ->middleware('super_admin');
                $registry->get('/configurations/{id:\\d+}', [SettingsController::class, 'show'])
                    ->name('api.configurations.show')
                    ->middleware('super_admin');
                $registry->put('/configurations/{id:\\d+}/toggle', [SettingsController::class, 'toggle'])
                    ->name('api.configurations.toggle')
                    ->middleware('super_admin');
                $registry->put('/configurations/{id:\\d+}/config', [SettingsController::class, 'updateConfig'])
                    ->name('api.configurations.config')
                    ->middleware('super_admin');
                $registry->post('/configurations', [SettingsController::class, 'store'])
                    ->name('api.configurations.store')
                    ->middleware('super_admin');
                
                // Cache API
                $registry->post('/cache/clear', [\Shared\Infrastructure\Controller\CacheController::class, 'clear'])
                    ->name('api.cache.clear');
                $registry->get('/cache/info', [\Shared\Infrastructure\Controller\CacheController::class, 'info'])
                    ->name('api.cache.info');
                
                // Backup API
                $registry->post('/backup/create', [\Shared\Infrastructure\Controller\BackupController::class, 'create'])
                    ->name('api.backup.create');
                $registry->get('/backup/list', [\Shared\Infrastructure\Controller\BackupController::class, 'index'])
                    ->name('api.backup.list');
                $registry->delete('/backup/delete', [\Shared\Infrastructure\Controller\BackupController::class, 'delete'])
                    ->name('api.backup.delete');
                
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

                // Module Management API (Super Admin only)
                $registry->get('/modules', [\Modules\Module\Presentation\Controller\ModuleController::class, 'index'])
                    ->name('api.modules.index')
                    ->middleware('super_admin');
                $registry->get('/modules/by-category', [\Modules\Module\Presentation\Controller\ModuleController::class, 'byCategory'])
                    ->name('api.modules.by-category')
                    ->middleware('super_admin');
                $registry->get('/modules/{id:\\d+}', [\Modules\Module\Presentation\Controller\ModuleController::class, 'show'])
                    ->name('api.modules.show')
                    ->middleware('super_admin');
                $registry->put('/modules/{id:\\d+}/toggle', [\Modules\Module\Presentation\Controller\ModuleController::class, 'toggle'])
                    ->name('api.modules.toggle')
                    ->middleware('super_admin');
                $registry->put('/modules/{id:\\d+}/config', [\Modules\Module\Presentation\Controller\ModuleController::class, 'updateConfig'])
                    ->name('api.modules.config')
                    ->middleware('super_admin');
                
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
                // Export
                $registry->get('/users/export', [UserController::class, 'exportCsv'])->name('api.users.export');
 
                // Security API
                $registry->get('/security/logs', [SecurityController::class, 'index'])->name('api.security.logs.index');
                $registry->get('/security/ips', [SecurityController::class, 'ips'])->name('api.security.ips.index');
                $registry->post('/security/ips', [SecurityController::class, 'blockIp'])->name('api.security.ips.block');
                $registry->delete('/security/ips/{ip:[\d\.]+}', [SecurityController::class, 'unblockIp'])->name('api.security.ips.unblock');
                $registry->get('/security/integrity', [SecurityController::class, 'checkIntegrity'])->name('api.security.integrity');
                $registry->post('/security/integrity/scan', [SecurityController::class, 'scanIntegrity'])->name('api.security.integrity.scan');
                $registry->post('/security/integrity/approve', [SecurityController::class, 'approveIntegrity'])->name('api.security.integrity.approve');
                
                // Tamper Tools
                $registry->post('/security/tamper/keygen', [SecurityController::class, 'generateKeys'])->name('api.security.tamper.keygen');
                $registry->post('/security/tamper/sign', [SecurityController::class, 'signSystem'])->name('api.security.tamper.sign');
                
                // Malware Scanner
                $registry->post('/security/malware/scan', [SecurityController::class, 'scanMalware'])->name('api.security.malware.scan');
            });
        });
    }
}


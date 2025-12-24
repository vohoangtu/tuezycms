<?php
/**
 * Custom menu for TuzyCMS Admin Panel
 * Based on master theme sidebar structure
 */

use Modules\User\Application\Service\AuthService;
use Core\Container\ServiceContainer;

$container = ServiceContainer::getInstance();
$authService = $container->make(AuthService::class);
$user = $authService->getCurrentUser();
$currentPage = basename($pageFile ?? 'dashboard', '.php');

// Helper function to check if menu item is active
$isActive = function($page) use ($currentPage) {
    return $currentPage === $page ? 'active' : '';
};
?>
<!-- ========== App Menu ========== -->
<div class="app-menu navbar-menu">
    <!-- LOGO -->
    <div class="navbar-brand-box">
        <!-- Dark Logo-->
        <a href="/admin" class="logo logo-dark">
            <span class="logo-sm">
                <img src="/assets/images/logo-sm.png" alt="TuzyCMS" height="22" onerror="this.style.display='none'; this.nextElementSibling.style.display='inline';">
                <span style="display:none; font-weight: bold; font-size: 18px;">TC</span>
            </span>
            <span class="logo-lg">
                <img src="/assets/images/logo-dark.png" alt="TuzyCMS" height="17" onerror="this.style.display='none'; this.nextElementSibling.style.display='inline';">
                <span style="display:none; font-weight: bold; font-size: 16px;">TuzyCMS</span>
            </span>
        </a>
        <!-- Light Logo-->
        <a href="/admin" class="logo logo-light">
            <span class="logo-sm">
                <img src="/assets/images/logo-sm.png" alt="TuzyCMS" height="22" onerror="this.style.display='none'; this.nextElementSibling.style.display='inline';">
                <span style="display:none; font-weight: bold; font-size: 18px;">TC</span>
            </span>
            <span class="logo-lg">
                <img src="/assets/images/logo-light.png" alt="TuzyCMS" height="17" onerror="this.style.display='none'; this.nextElementSibling.style.display='inline';">
                <span style="display:none; font-weight: bold; font-size: 16px;">TuzyCMS</span>
            </span>
        </a>
        <button type="button" class="btn btn-sm p-0 fs-20 header-item float-end btn-vertical-sm-hover" id="vertical-hover">
            <i class="ri-record-circle-line"></i>
        </button>
    </div>

    <div class="dropdown sidebar-user m-1 rounded">
        <button type="button" class="btn material-shadow-none" id="page-header-user-dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <span class="d-flex align-items-center gap-2">
                <img class="rounded header-profile-user" src="/assets/images/users/avatar-1.jpg" alt="User Avatar" onerror="this.src='/assets/images/users/avatar-1.jpg';">
                <span class="text-start">
                    <span class="d-block fw-medium sidebar-user-name-text"><?= htmlspecialchars($user->getFullName()) ?></span>
                    <span class="d-block fs-14 sidebar-user-name-sub-text">
                        <i class="ri ri-circle-fill fs-10 text-success align-baseline"></i> 
                        <span class="align-middle">Online</span>
                    </span>
                </span>
            </span>
        </button>
        <div class="dropdown-menu dropdown-menu-end">
            <h6 class="dropdown-header">Welcome <?= htmlspecialchars($user->getFullName()) ?>!</h6>
            <a class="dropdown-item" href="/admin/settings"><i class="mdi mdi-cog-outline text-muted fs-16 align-middle me-1"></i> <span class="align-middle">Settings</span></a>
            <div class="dropdown-divider"></div>
            <a class="dropdown-item" href="/admin/logout"><i class="mdi mdi-logout text-muted fs-16 align-middle me-1"></i> <span class="align-middle">Logout</span></a>
        </div>
    </div>

    <div id="scrollbar">
        <div class="container-fluid">
            <ul class="navbar-nav" id="navbar-nav">
                <li class="menu-title"><span>Menu</span></li>
                
                <!-- Dashboard -->
                <li class="nav-item">
                    <a class="nav-link menu-link <?= $isActive('dashboard') ?>" href="/admin">
                        <i class="ri-dashboard-2-line"></i> <span>Dashboard</span>
                    </a>
                </li>

                <!-- Bài viết -->
                <li class="nav-item">
                    <a class="nav-link menu-link <?= $isActive('articles') ?>" href="/admin/articles">
                        <i class="ri-article-line"></i> <span>Bài viết</span>
                    </a>
                </li>

                <!-- Sản phẩm -->
                <li class="nav-item">
                    <a class="nav-link menu-link <?= $isActive('products') ?>" href="/admin/products">
                        <i class="ri-shopping-bag-line"></i> <span>Sản phẩm</span>
                    </a>
                </li>

                <!-- Đơn hàng -->
                <li class="nav-item">
                    <a class="nav-link menu-link <?= $isActive('orders') ?>" href="/admin/orders">
                        <i class="ri-shopping-cart-line"></i> <span>Đơn hàng</span>
                    </a>
                </li>

                <!-- Khuyến mãi -->
                <li class="nav-item">
                    <a class="nav-link menu-link <?= $isActive('promotions') ?>" href="/admin/promotions">
                        <i class="ri-price-tag-3-line"></i> <span>Khuyến mãi</span>
                    </a>
                </li>

                <!-- Media Library -->
                <li class="nav-item">
                    <a class="nav-link menu-link <?= $isActive('media') ?>" href="/admin/media">
                        <i class="ri-image-line"></i> <span>Media Library</span>
                    </a>
                </li>

                <!-- Cài đặt -->
                <li class="nav-item">
                    <a class="nav-link menu-link <?= $isActive('settings') ?>" href="/admin/settings">
                        <i class="ri-settings-3-line"></i> <span>Cài đặt</span>
                    </a>
                </li>

            </ul>
        </div>
    </div>

    <div class="sidebar-background"></div>
</div>
<!-- Left Sidebar End -->
<!-- Vertical Overlay-->
<div class="vertical-overlay"></div>


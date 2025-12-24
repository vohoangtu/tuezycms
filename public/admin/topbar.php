<?php
/**
 * Topbar for TuzyCMS Admin Panel
 * Simplified version based on master theme
 */
?>
<header id="page-topbar">
    <div class="layout-width">
        <div class="navbar-header">
            <div class="d-flex">
                <!-- LOGO -->
                <div class="navbar-brand-box horizontal-logo">
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
                </div>

                <button type="button" class="btn btn-sm px-3 fs-16 header-item vertical-menu-btn topnav-hamburger material-shadow-none" id="topnav-hamburger-icon">
                    <span class="hamburger-icon">
                        <span></span>
                        <span></span>
                        <span></span>
                    </span>
                </button>

                <!-- App Search-->
                <form class="app-search d-none d-md-block">
                    <div class="position-relative">
                        <input type="text" class="form-control" placeholder="Search..." autocomplete="off" id="search-options" value="">
                        <span class="mdi mdi-magnify search-widget-icon"></span>
                        <span class="mdi mdi-close-circle search-widget-icon search-widget-icon-close d-none" id="search-close-options"></span>
                    </div>
                </form>
            </div>

            <div class="d-flex align-items-center">

                <div class="ms-1 header-item d-none d-sm-flex">
                    <button type="button" class="btn btn-icon btn-topbar material-shadow-none btn-ghost-secondary rounded-circle" data-toggle="fullscreen">
                        <i class='bx bx-fullscreen fs-22'></i>
                    </button>
                </div>

                <div class="ms-1 header-item d-none d-sm-flex">
                    <button type="button" class="btn btn-icon btn-topbar material-shadow-none btn-ghost-secondary rounded-circle light-dark-mode">
                        <i class='bx bx-moon fs-22'></i>
                    </button>
                </div>

                <div class="dropdown ms-sm-3 header-item topbar-user">
                    <button type="button" class="btn material-shadow-none" id="page-header-user-dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <span class="d-flex align-items-center">
                            <img class="rounded-circle header-profile-user" src="/assets/images/users/avatar-1.jpg" alt="Header Avatar" onerror="this.src='/assets/images/users/avatar-1.jpg';">
                            <span class="text-start ms-xl-2">
                                <span class="d-none d-xl-inline-block ms-1 fw-medium user-name-text"><?= htmlspecialchars($user->getFullName()) ?></span>
                                <span class="d-none d-xl-block ms-1 fs-12 user-name-sub-text"><?= htmlspecialchars($user->getEmail()) ?></span>
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
            </div>
        </div>
    </div>
</header>


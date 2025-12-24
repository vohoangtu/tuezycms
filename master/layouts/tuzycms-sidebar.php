<!-- ========== App Menu ========== -->
<div class="app-menu navbar-menu">
    <!-- LOGO -->
    <div class="navbar-brand-box">
        <!-- Dark Logo-->
        <a href="/admin/dashboard" class="logo logo-dark">
            <span class="logo-sm">
                <img src="/assets/images/logo-sm.png" alt="" height="22">
            </span>
            <span class="logo-lg">
                <img src="/assets/images/logo-dark.png" alt="" height="17">
            </span>
        </a>
        <!-- Light Logo-->
        <a href="/admin/dashboard" class="logo logo-light">
            <span class="logo-sm">
                <img src="/assets/images/logo-sm.png" alt="" height="22">
            </span>
            <span class="logo-lg">
                <img src="/assets/images/logo-light.png" alt="" height="17">
            </span>
        </a>
        <button type="button" class="btn btn-sm p-0 fs-20 header-item float-end btn-vertical-sm-hover" id="vertical-hover">
            <i class="ri-record-circle-line"></i>
        </button>
    </div>

    <div class="dropdown sidebar-user m-1 rounded">
        <button type="button" class="btn material-shadow-none" id="page-header-user-dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <span class="d-flex align-items-center gap-2">
                <img class="rounded header-profile-user" src="/assets/images/users/avatar-1.jpg" alt="Header Avatar">
                <span class="text-start">
                    <span class="d-block fw-medium sidebar-user-name-text">Admin</span>
                    <span class="d-block fs-14 sidebar-user-name-sub-text"><i class="ri ri-circle-fill fs-10 text-success align-baseline"></i> <span class="align-middle">Online</span></span>
                </span>
            </span>
        </button>
        <div class="dropdown-menu dropdown-menu-end">
            <!-- item-->
            <h6 class="dropdown-header">Chào mừng Admin!</h6>
            <a class="dropdown-item" href="/admin/settings"><i class="mdi mdi-cog-outline text-muted fs-16 align-middle me-1"></i> <span class="align-middle">Cài đặt</span></a>
            <div class="dropdown-divider"></div>
            <a class="dropdown-item" href="/admin/logout"><i class="mdi mdi-logout text-muted fs-16 align-middle me-1"></i> <span class="align-middle">Đăng xuất</span></a>
        </div>
    </div>

    <div id="scrollbar">
        <div class="container-fluid">

            <div id="two-column-menu">
            </div>
            <ul class="navbar-nav" id="navbar-nav">
                <li class="menu-title"><span>Menu Chính</span></li>
                
                <li class="nav-item">
                    <a class="nav-link menu-link" href="/admin/dashboard">
                        <i class="ri-dashboard-2-line"></i> <span>Dashboard</span>
                    </a>
                </li>

                <li class="menu-title"><i class="ri-more-fill"></i> <span>Quản lý nội dung</span></li>

                <li class="nav-item">
                    <a class="nav-link menu-link" href="/admin/articles">
                        <i class="ri-file-text-line"></i> <span>Bài viết</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link menu-link" href="/admin/products">
                        <i class="ri-shopping-bag-line"></i> <span>Sản phẩm</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link menu-link" href="/admin/orders">
                        <i class="ri-shopping-cart-line"></i> <span>Đơn hàng</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link menu-link" href="/admin/promotions">
                        <i class="ri-percent-line"></i> <span>Khuyến mãi</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link menu-link" href="/admin/media">
                        <i class="ri-image-line"></i> <span>Thư viện Media</span>
                    </a>
                </li>

                <li class="menu-title"><i class="ri-more-fill"></i> <span>Hệ thống</span></li>

                <li class="nav-item">
                    <a class="nav-link menu-link" href="/admin/users">
                        <i class="ri-user-line"></i> <span>Users</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link menu-link" href="/admin/roles">
                        <i class="ri-shield-user-line"></i> <span>Roles & Permissions</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link menu-link" href="/admin/settings">
                        <i class="ri-settings-3-line"></i> <span>Cài đặt</span>
                    </a>
                </li>

            </ul>
        </div>
        <!-- Sidebar -->
    </div>

    <div class="sidebar-background"></div>
</div>
<!-- Left Sidebar End -->
<!-- Vertical Overlay-->
<div class="vertical-overlay"></div>

<?php
/**
 * Settings Page View (Super Admin Only)
 * System-wide settings
 */
?>

<!-- start page title -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">Cài đặt hệ thống</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="/admin/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item active">Settings</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">
                    <i class="ri-settings-3-line me-2"></i>
                    Cài đặt hệ thống
                    <span class="badge bg-danger-subtle text-danger ms-2">Super Admin Only</span>
                </h4>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="ri-information-line me-2"></i>
                    <strong>Trang Settings:</strong> Dành cho Super Admin để cấu hình hệ thống cốt lõi.
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <h5 class="mb-3">Thông tin hệ thống</h5>
                        <ul class="list-unstyled">
                            <li class="mb-2"><strong>Phiên bản:</strong> TuzyCMS v1.0</li>
                            <li class="mb-2"><strong>PHP Version:</strong> <?= PHP_VERSION ?></li>
                            <li class="mb-2"><strong>Database:</strong> MySQL</li>
                        </ul>
                    </div>
                    
                    <div class="col-md-6">
                        <h5 class="mb-3">Quick Actions</h5>
                        <div class="d-flex flex-column gap-2">
                            <a href="/admin/modules" class="btn btn-soft-primary">
                                <i class="ri-puzzle-line me-2"></i>Quản lý Modules
                            </a>
                            <a href="/admin/configurations" class="btn btn-soft-info">
                                <i class="ri-list-settings-line me-2"></i>Quản lý Configurations
                            </a>
                            <a href="/admin/users" class="btn btn-soft-success">
                                <i class="ri-user-line me-2"></i>Quản lý Users
                            </a>
                            <a href="/admin/roles" class="btn btn-soft-warning">
                                <i class="ri-shield-user-line me-2"></i>Quản lý Roles
                            </a>
                        </div>
                    </div>
                </div>
                
                <hr class="my-4">
                
                <p class="text-muted mb-0">
                    <i class="ri-lightbulb-line me-2"></i>
                    <strong>Gợi ý:</strong> Sử dụng trang <strong>Configurations</strong> để quản lý các cấu hình có thể bật/tắt. 
                    Trang này dành cho cài đặt hệ thống cấp cao.
                </p>
            </div>
        </div>
    </div>
</div>

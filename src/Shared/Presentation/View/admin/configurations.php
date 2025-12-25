<?php
/**
 * Configurations View
 * Similar to modules page - list of config items that can be toggled on/off
 */
?>

<!-- start page title -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">Cấu hình hệ thống</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="/admin/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item active">Configurations</li>
                </ol>
            </div>
        </div>
    </div>
</div>
<!-- end page title -->

<!-- General Action Panel -->
<div class="row mb-3">
    <div class="col-12">
        <div class="card">
            <div class="card-body py-2">
                <div class="d-flex align-items-center justify-content-between">
                    <h6 class="mb-0 text-muted">
                        <i class="ri-tools-line me-1"></i> General Actions
                    </h6>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-soft-info btn-sm" id="cacheBtn" disabled>
                            <i class="ri-database-2-line me-1"></i> Cache
                        </button>
                        <button type="button" class="btn btn-soft-danger btn-sm" id="clearCacheBtn">
                            <i class="ri-delete-bin-line me-1"></i> Clear Cache
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- end General Action Panel -->

<!-- Tabs for categories -->
<ul class="nav nav-tabs nav-tabs-custom nav-success mb-3" role="tablist">
    <li class="nav-item">
        <a class="nav-link active" data-bs-toggle="tab" href="#all-configs" role="tab">
            Tất cả
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-bs-toggle="tab" href="#system-configs" role="tab">
            Hệ thống
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-bs-toggle="tab" href="#security-configs" role="tab">
            Bảo mật
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-bs-toggle="tab" href="#notification-configs" role="tab">
            Thông báo
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-bs-toggle="tab" href="#performance-configs" role="tab">
            Hiệu suất
        </a>
    </li>
</ul>

<!-- Tab content -->
<div class="tab-content">
    <div class="tab-pane active" id="all-configs" role="tabpanel">
        <div class="row" id="config-list-all">
            <div class="col-12 text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        </div>
    </div>
    <div class="tab-pane" id="system-configs" role="tabpanel">
        <div class="row" id="config-list-system"></div>
    </div>
    <div class="tab-pane" id="security-configs" role="tabpanel">
        <div class="row" id="config-list-security"></div>
    </div>
    <div class="tab-pane" id="notification-configs" role="tabpanel">
        <div class="row" id="config-list-notification"></div>
    </div>
    <div class="tab-pane" id="performance-configs" role="tabpanel">
        <div class="row" id="config-list-performance"></div>
    </div>
</div>

<!-- Configuration Modal -->
<div class="modal fade" id="configModal" tabindex="-1" aria-labelledby="configModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="configModalLabel">Cấu hình</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="configModalBody">
                <!-- Config form will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                <button type="button" class="btn btn-primary" id="saveConfigBtn">Lưu cấu hình</button>
            </div>
        </div>
    </div>
</div>

<script src="/public/admin/assets/js/configurations.js"></script>

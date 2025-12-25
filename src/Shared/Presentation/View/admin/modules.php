<?php
$pageTitle = $pageData['pageTitle'] ?? 'Modules Management';
?>

<!-- Page Title-->
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">Module Management</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="/admin">Dashboard</a></li>
                    <li class="breadcrumb-item active">Modules</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Category Tabs -->
<div class="row">
    <div class="col-12">
        <ul class="nav nav-tabs nav-tabs-custom nav-justified mb-3" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" data-bs-toggle="tab" href="#all-modules" role="tab">
                    <lord-icon src="https://cdn.lordicon.com/nocvdjmh.json" trigger="loop" colors="primary:#405189,secondary:#0ab39c" style="width:20px;height:20px"></lord-icon>
                    All Modules
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#user-modules" role="tab">
                    <i class="ri-user-line align-middle"></i>
                    User Management
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#product-modules" role="tab">
                    <i class="ri-shopping-bag-line align-middle"></i>
                    Product Management
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#content-modules" role="tab">
                    <i class="ri-article-line align-middle"></i>
                    Content Management
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#system-modules" role="tab">
                    <i class="ri-settings-3-line align-middle"></i>
                    System
                </a>
            </li>
        </ul>

        <div class="tab-content text-muted">
            <!-- All Modules Tab -->
            <div class="tab-pane active" id="all-modules" role="tabpanel">
                <div id="all-modules-container" class="row"></div>
            </div>

            <!-- User Modules Tab -->
            <div class="tab-pane" id="user-modules" role="tabpanel">
                <div id="user-modules-container" class="row"></div>
            </div>

            <!-- Product Modules Tab -->
            <div class="tab-pane" id="product-modules" role="tabpanel">
                <div id="product-modules-container" class="row"></div>
            </div>

            <!-- Content Modules Tab -->
            <div class="tab-pane" id="content-modules" role="tabpanel">
                <div id="content-modules-container" class="row"></div>
            </div>

            <!-- System Modules Tab -->
            <div class="tab-pane" id="system-modules" role="tabpanel">
                <div id="system-modules-container" class="row"></div>
            </div>
        </div>
    </div>
</div>

<!-- Module Config Modal -->
<div class="modal fade" id="moduleConfigModal" tabindex="-1" aria-labelledby="moduleConfigModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="moduleConfigModalLabel">Module Configuration</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="moduleConfigId">
                <div id="configFields">
                    <p class="text-muted">No configuration options available for this module.</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveConfigBtn" onclick="saveModuleConfig()">Save Configuration</button>
            </div>
        </div>
    </div>
</div>

<script src="/public/admin/assets/js/modules.js"></script>

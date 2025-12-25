<?php
$pageTitle = $pageData['pageTitle'] ?? 'Roles & Permissions';
?>

<!-- Stats Cards -->
<div class="row">
    <div class="col-xl-3 col-md-6">
        <div class="card card-animate">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1 overflow-hidden">
                        <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Total Roles</p>
                    </div>
                </div>
                <div class="d-flex align-items-end justify-content-between mt-4">
                    <div>
                        <h4 class="fs-22 fw-semibold ff-secondary mb-4" id="totalRoles">-</h4>
                    </div>
                    <div class="avatar-sm flex-shrink-0">
                        <span class="avatar-title bg-primary-subtle rounded fs-3">
                            <i class="bx bx-shield text-primary"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6">
        <div class="card card-animate">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1 overflow-hidden">
                        <p class="text-uppercase fw-medium text-muted text-truncate mb-0">System Roles</p>
                    </div>
                </div>
                <div class="d-flex align-items-end justify-content-between mt-4">
                    <div>
                        <h4 class="fs-22 fw-semibold ff-secondary mb-4" id="systemRoles">-</h4>
                    </div>
                    <div class="avatar-sm flex-shrink-0">
                        <span class="avatar-title bg-info-subtle rounded fs-3">
                            <i class="bx bx-lock text-info"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6">
        <div class="card card-animate">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1 overflow-hidden">
                        <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Custom Roles</p>
                    </div>
                </div>
                <div class="d-flex align-items-end justify-content-between mt-4">
                    <div>
                        <h4 class="fs-22 fw-semibold ff-secondary mb-4" id="customRoles">-</h4>
                    </div>
                    <div class="avatar-sm flex-shrink-0">
                        <span class="avatar-title bg-success-subtle rounded fs-3">
                            <i class="bx bx-wrench text-success"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6">
        <div class="card card-animate">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1 overflow-hidden">
                        <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Total Permissions</p>
                    </div>
                </div>
                <div class="d-flex align-items-end justify-content-between mt-4">
                    <div>
                        <h4 class="fs-22 fw-semibold ff-secondary mb-4" id="totalPermissions">-</h4>
                    </div>
                    <div class="avatar-sm flex-shrink-0">
                        <span class="avatar-title bg-warning-subtle rounded fs-3">
                            <i class="bx bx-key text-warning"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Roles Table -->
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h5 class="card-title mb-0">Roles List</h5>
                    </div>
                    <div class="flex-shrink-0">
                        <button class="btn btn-success add-btn" onclick="openCreateModal()">
                            <i class="ri-add-line align-bottom me-1"></i> Add Role
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="card-body">
                <!-- Table -->
                <div class="table-card" style="overflow: visible;">
                    <div style="overflow-x: auto;">
                        <table class="table align-middle table-nowrap" id="rolesTable">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Display Name</th>
                                    <th>Permissions</th>
                                    <th>Users</th>
                                    <th>Type</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="rolesTableBody">
                                <tr>
                                    <td colspan="7" class="text-center">
                                        <div class="py-4">
                                            <div class="spinner-border text-primary" role="status">
                                                <span class="sr-only">Loading...</span>
                                            </div>
                                            <p class="text-muted mt-2">Loading roles...</p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Role Modal -->
<div class="modal fade" id="roleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light p-3">
                <h5 class="modal-title" id="roleModalLabel">Add Role</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="roleForm">
                <div class="modal-body">
                    <input type="hidden" id="roleId">
                    
                    <div class="mb-3">
                        <label for="roleName" class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="roleName" required 
                            placeholder="e.g., editor">
                        <div class="form-text">Lowercase, no spaces (use underscores)</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="roleDisplayName" class="form-label">Display Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="roleDisplayName" required
                            placeholder="e.g., Editor">
                    </div>
                    
                    <div class="mb-3">
                        <label for="roleDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="roleDescription" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-success" onclick="saveRole()">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Permissions Modal -->
<div class="modal fade" id="permissionsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-light p-3">
                <h5 class="modal-title">
                    <i class="ri-key-line me-2"></i>
                    Manage Permissions: <span id="permissionsRoleName" class="text-primary"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="permissionsRoleId">
                
                <div class="alert alert-info">
                    <i class="ri-information-line me-2"></i>
                    Select permissions to grant this role. Permissions are grouped by resource.
                </div>
                
                <div id="permissionsMatrix">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="text-muted mt-2">Loading permissions...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" onclick="savePermissions()">
                    <i class="ri-save-line me-1"></i> Save Permissions
                </button>
            </div>
        </div>
    </div>
</div>

<script src="/public/admin/assets/js/roles-datatable.js"></script>

<script>
// Override renderTable for custom template
renderTable = function(roles) {
    const tbody = document.getElementById('rolesTableBody');
    
    if (roles.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="text-center py-5">
                    <i class="ri-inbox-line" style="font-size: 48px; color: #ccc;"></i>
                    <p class="text-muted mt-3 mb-0">No roles found</p>
                </td>
            </tr>
        `;
        return;
    }
    
    tbody.innerHTML = roles.map(role => `
        <tr>
            <td><span class="badge bg-secondary-subtle text-secondary">#${role.id}</span></td>
            <td>
                <div class="d-flex align-items-center">
                    <div class="avatar-xs me-2">
                        <div class="avatar-title rounded-circle bg-${role.is_system ? 'info' : 'success'}-subtle text-${role.is_system ? 'info' : 'success'}">
                            <i class="bx bx-${role.is_system ? 'lock' : 'wrench'}"></i>
                        </div>
                    </div>
                    <code>${role.name}</code>
                </div>
            </td>
            <td class="fw-medium">${role.display_name}</td>
            <td>
                <span class="badge bg-primary">${role.permissions_count} permissions</span>
            </td>
            <td>
                <span class="badge bg-info">${role.users_count} users</span>
            </td>
            <td>
                <span class="badge bg-${role.is_system ? 'info' : 'success'}">
                    ${role.is_system ? 'System' : 'Custom'}
                </span>
            </td>
            <td>
                <div class="dropdown">
                    <button class="btn btn-sm btn-light dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="ri-more-fill"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" onclick="editRole(${role.id})">
                            <i class="ri-edit-line me-2"></i> Edit
                        </a></li>
                        <li><a class="dropdown-item" onclick="managePermissions(${role.id})">
                            <i class="ri-key-line me-2"></i> Manage Permissions
                        </a></li>
                        ${!role.is_system ? `
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" onclick="deleteRole(${role.id})">
                                <i class="ri-delete-bin-line me-2"></i> Delete
                            </a></li>
                        ` : ''}
                    </ul>
                </div>
            </td>
        </tr>
    `).join('');
};
</script>

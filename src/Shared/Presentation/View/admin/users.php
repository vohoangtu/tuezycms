<?php
$pageTitle = $pageData['pageTitle'] ?? 'User Management';
$roles = $pageData['roles'] ?? [];
?>

<!-- Stats Cards -->
<div class="row">
    <div class="col-xl-3 col-md-6">
        <div class="card card-animate">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1 overflow-hidden">
                        <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Total Users</p>
                    </div>
                </div>
                <div class="d-flex align-items-end justify-content-between mt-4">
                    <div>
                        <h4 class="fs-22 fw-semibold ff-secondary mb-4" id="totalUsers">-</h4>
                    </div>
                    <div class="avatar-sm flex-shrink-0">
                        <span class="avatar-title bg-primary-subtle rounded fs-3">
                            <i class="bx bx-user text-primary"></i>
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
                        <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Active Users</p>
                    </div>
                </div>
                <div class="d-flex align-items-end justify-content-between mt-4">
                    <div>
                        <h4 class="fs-22 fw-semibold ff-secondary mb-4" id="activeUsers">-</h4>
                    </div>
                    <div class="avatar-sm flex-shrink-0">
                        <span class="avatar-title bg-success-subtle rounded fs-3">
                            <i class="bx bx-user-check text-success"></i>
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
                        <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Inactive Users</p>
                    </div>
                </div>
                <div class="d-flex align-items-end justify-content-between mt-4">
                    <div>
                        <h4 class="fs-22 fw-semibold ff-secondary mb-4" id="inactiveUsers">-</h4>
                    </div>
                    <div class="avatar-sm flex-shrink-0">
                        <span class="avatar-title bg-warning-subtle rounded fs-3">
                            <i class="bx bx-user-x text-warning"></i>
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
                        <p class="text-uppercase fw-medium text-muted text-truncate mb-0">New This Month</p>
                    </div>
                </div>
                <div class="d-flex align-items-end justify-content-between mt-4">
                    <div>
                        <h4 class="fs-22 fw-semibold ff-secondary mb-4" id="newUsers">-</h4>
                    </div>
                    <div class="avatar-sm flex-shrink-0">
                        <span class="avatar-title bg-info-subtle rounded fs-3">
                            <i class="bx bx-user-plus text-info"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Search & Filters -->
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Search & Filters</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-lg-4">
                        <div class="search-box">
                            <input type="text" class="form-control search" id="searchInput" placeholder="Search email or name...">
                            <i class="ri-search-line search-icon"></i>
                        </div>
                    </div>
                    
                    <div class="col-lg-2">
                        <select class="form-select" id="roleFilter">
                            <option value="">All Roles</option>
                            <?php foreach ($roles as $role): ?>
                                <option value="<?= $role->getId() ?>"><?= htmlspecialchars($role->getDisplayName()) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-lg-2">
                        <select class="form-select" id="statusFilter">
                            <option value="">All Status</option>
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                    
                    <div class="col-lg-2">
                        <input type="date" class="form-control" id="dateFrom" placeholder="From Date">
                    </div>
                    
                    <div class="col-lg-2">
                        <input type="date" class="form-control" id="dateTo" placeholder="To Date">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Users Table -->
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h5 class="card-title mb-0">Users List</h5>
                        <p class="text-muted mb-0" id="resultsInfo">Loading...</p>
                    </div>
                    <div class="flex-shrink-0">
                        <button class="btn btn-success add-btn" onclick="openCreateModal()">
                            <i class="ri-add-line align-bottom me-1"></i> Add User
                        </button>
                        <div class="btn-group">
                            <button type="button" class="btn btn-soft-primary dropdown-toggle" data-bs-toggle="dropdown">
                                <i class="ri-download-2-line align-bottom me-1"></i> Export
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" onclick="exportData('selected')">
                                    <i class="ri-file-list-line align-bottom me-2"></i>Export Selected
                                </a></li>
                                <li><a class="dropdown-item" onclick="exportData('filtered')">
                                    <i class="ri-filter-line align-bottom me-2"></i>Export Filtered
                                </a></li>
                                <li><a class="dropdown-item" onclick="exportData('all')">
                                    <i class="ri-file-text-line align-bottom me-2"></i>Export All
                                </a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card-body">
                <!-- Bulk Actions -->
                <div id="bulkActionsBar" class="alert alert-primary alert-borderless mb-3 d-none" role="alert">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <i class="ri-checkbox-multiple-line me-2"></i>
                            <strong><span id="selectedCount">0</span> users selected</strong>
                        </div>
                        <div class="flex-shrink-0">
                            <div class="btn-group">
                                <button class="btn btn-sm btn-success" onclick="bulkActivate()">
                                    <i class="ri-check-line me-1"></i> Activate
                                </button>
                                <button class="btn btn-sm btn-warning" onclick="bulkDeactivate()">
                                    <i class="ri-close-line me-1"></i> Deactivate
                                </button>
                                <button class="btn btn-sm btn-info dropdown-toggle" data-bs-toggle="dropdown">
                                    <i class="ri-more-2-fill me-1"></i> More
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" onclick="bulkAssignRole()">
                                        <i class="ri-shield-user-line me-2"></i>Assign Role
                                    </a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item text-danger" onclick="bulkDelete()">
                                        <i class="ri-delete-bin-line me-2"></i>Delete
                                    </a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Table -->
                <div class="table-responsive table-card">
                    <table class="table align-middle table-nowrap" id="usersTable">
                        <thead class="table-light">
                            <tr>
                                <th scope="col" style="width: 50px;">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="selectAll">
                                    </div>
                                </th>
                                <th class="sort" data-sort="id" style="cursor: pointer;">ID</th>
                                <th class="sort" data-sort="email" style="cursor: pointer;">User</th>
                                <th>Roles</th>
                                <th class="sort" data-sort="is_active" style="cursor: pointer;">Status</th>
                                <th class="sort" data-sort="created_at" style="cursor: pointer;">Created Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody class="list form-check-all" id="usersTableBody">
                            <tr>
                                <td colspan="7" class="text-center">
                                    <div class="py-4">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="sr-only">Loading...</span>
                                        </div>
                                        <p class="text-muted mt-2">Loading users...</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <div class="d-flex justify-content-end mt-3">
                    <div class="pagination-wrap hstack gap-2">
                        <select class="form-select form-select-sm" id="perPageSelect" style="width: auto;">
                            <option value="10">10</option>
                            <option value="20" selected>20</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                        <ul class="pagination listjs-pagination mb-0" id="pagination"></ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- User Modal -->
<div class="modal fade" id="userModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light p-3">
                <h5 class="modal-title" id="userModalLabel">Add User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="userForm">
                <div class="modal-body">
                    <input type="hidden" id="userId">
                    
                    <div class="mb-3">
                        <label for="userEmail" class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="userEmail" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="userFullName" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="userFullName">
                    </div>
                    
                    <div class="mb-3">
                        <label for="userPassword" class="form-label">Password <span id="passwordRequired" class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="userPassword">
                        <div class="form-text">Leave blank to keep current password (when editing)</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="userRoles" class="form-label">Roles</label>
                        <select class="form-select" id="userRoles" multiple size="4">
                            <?php foreach ($roles as $role): ?>
                                <option value="<?= $role->getId() ?>"><?= htmlspecialchars($role->getDisplayName()) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text">Hold Ctrl/Cmd to select multiple roles</div>
                    </div>
                    
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="userIsActive" checked>
                        <label class="form-check-label" for="userIsActive">Active Status</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-success" onclick="saveUser()">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bulk Role Modal -->
<div class="modal fade" id="bulkRoleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light p-3">
                <h5 class="modal-title">Assign Role to Users</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Select role to assign to <strong id="bulkRoleCount">0</strong> selected users:</p>
                <select class="form-select" id="bulkRoleSelect">
                    <?php foreach ($roles as $role): ?>
                        <option value="<?= $role->getId() ?>"><?= htmlspecialchars($role->getDisplayName()) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" onclick="confirmBulkAssignRole()">Assign Role</button>
            </div>
        </div>
    </div>
</div>

<script src="/public/admin/assets/js/users-datatable.js"></script>

<script>
// Override render function
const originalRenderTable = renderTable;
renderTable = function(users) {
    const tbody = document.getElementById('usersTableBody');
    
    if (users.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="7">
                    <div class="noresult">
                        <div class="text-center">
                            <lord-icon src="https://cdn.lordicon.com/msoeawqm.json" trigger="loop" colors="primary:#121331,secondary:#08a88a" style="width:75px;height:75px"></lord-icon>
                            <h5 class="mt-2">Sorry! No Result Found</h5>
                            <p class="text-muted mb-0">We did not find any users matching your search.</p>
                        </div>
                    </div>
                </td>
            </tr>
        `;
        return;
    }
    
    tbody.innerHTML = users.map(user => `
        <tr>
            <th scope="row">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="${user.id}" 
                        ${selectedUsers.has(user.id) ? 'checked' : ''}
                        onchange="toggleUserSelection(${user.id}, this.checked)">
                </div>
            </th>
            <td class="id"><a href="#" class="fw-medium link-primary">#${user.id}</a></td>
            <td class="email">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-2">
                        <div class="avatar-xs">
                            <div class="avatar-title rounded-circle bg-primary-subtle text-primary">
                                ${user.email.charAt(0).toUpperCase()}
                            </div>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <h5 class="fs-14 mb-0">${user.email}</h5>
                        <p class="text-muted mb-0">${user.full_name || '-'}</p>
                    </div>
                </div>
            </td>
            <td class="roles">
                ${user.roles.map(r => `<span class="badge bg-primary-subtle text-primary me-1">${r.display_name}</span>`).join('') || '<span class="text-muted">No roles</span>'}
            </td>
            <td class="status">
                <span class="badge bg-${user.is_active ? 'success' : 'danger'}-subtle text-${user.is_active ? 'success' : 'danger'}">
                    ${user.is_active ? 'Active' : 'Inactive'}
                </span>
            </td>
            <td class="created_at">${formatDate(user.created_at)}</td>
            <td>
                <div class="dropdown">
                    <button class="btn btn-soft-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="ri-more-fill align-middle"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" onclick="editUser(${user.id})">
                            <i class="ri-pencil-fill align-bottom me-2 text-muted"></i> Edit
                        </a></li>
                        <li><a class="dropdown-item" onclick="viewUserDetails(${user.id})">
                            <i class="ri-eye-fill align-bottom me-2 text-muted"></i> View Details
                        </a></li>
                        <li><a class="dropdown-item" onclick="resetUserPassword(${user.id})">
                            <i class="ri-lock-password-fill align-bottom me-2 text-muted"></i> Reset Password
                        </a></li>
                        <li class="dropdown-divider"></li>
                        <li><a class="dropdown-item remove-item-btn" onclick="deleteUser(${user.id})">
                            <i class="ri-delete-bin-fill align-bottom me-2 text-muted"></i> Delete
                        </a></li>
                    </ul>
                </div>
            </td>
        </tr>
    `).join('');
};

// API functions
async function confirmBulkAssignRole() {
    const roleId = document.getElementById('bulkRoleSelect').value;
    
    if (!roleId) {
        alert('Please select a role');
        return;
    }
    
    try {
        const response = await fetch('/admin/api/users/bulk-assign-role', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ 
                user_ids: Array.from(selectedUsers),
                role_id: parseInt(roleId)
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('bulkRoleModal')).hide();
            selectedUsers.clear();
            loadUsers();
            alert(data.message);
        } else {
            alert('Error: ' + data.message);
        }
    } catch (error) {
        alert('Network error');
    }
}

async function viewUserDetails(id) {
    try {
        const response = await fetch(`/admin/api/users/${id}/details`);
        const data = await response.json();
        
        if (data.success) {
            const user = data.data;
            const roles = user.roles.map(r => r.display_name).join(', ') || 'No roles';
            alert(`User Details\n\nEmail: ${user.email}\nFull Name: ${user.full_name || '-'}\nStatus: ${user.is_active ? 'Active' : 'Inactive'}\nRoles: ${roles}\nCreated: ${user.stats.created_at || '-'}\nLast Login: ${user.stats.last_login || 'Never'}`);
        }
    } catch (error) {
        alert('Error loading user details');
    }
}

async function resetUserPassword(id) {
    if (!confirm('Reset password for this user?')) return;
    
    try {
        const response = await fetch(`/admin/api/users/${id}/reset-password`, {
            method: 'POST'
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert(`Password reset successfully!\n\nNew password: ${data.new_password}\n\nPlease save this password.`);
        } else {
            alert('Error: ' + data.message);
        }
    } catch (error) {
        alert('Network error');
    }
}

function exportData(type) {
    let url = '/admin/api/users/export?type=' + type;
    
    if (type === 'selected') {
        if (selectedUsers.size === 0) {
            alert('No users selected');
            return;
        }
        url += '&ids=' + Array.from(selectedUsers).join(',');
    } else if (type === 'filtered') {
        const params = new URLSearchParams(filters);
        url += '&' + params.toString();
    }
    
    window.location.href = url;
}
</script>

/**
 * Roles & Permissions Data Table
 * Handles role management operations
 */

// State
let roles = [];
let permissions = [];
let currentRoleId = null;
let rolePermissions = [];

// ========== Initialization ==========

document.addEventListener('DOMContentLoaded', function () {
    loadRoles();
    loadStats();
});

// ========== Load Data ==========

async function loadRoles() {
    try {
        const response = await fetch('/admin/api/roles');
        const data = await response.json();

        if (data.success) {
            roles = data.data;
            renderTable(roles);
        } else {
            showError('Failed to load roles');
        }
    } catch (error) {
        showError('Network error');
    }
}

async function loadStats() {
    try {
        const response = await fetch('/admin/api/roles');
        const data = await response.json();

        if (data.success) {
            roles = data.data;

            const totalRoles = roles.length;
            const systemRoles = roles.filter(r => r.is_system).length;
            const customRoles = roles.filter(r => !r.is_system).length;

            document.getElementById('totalRoles').textContent = totalRoles;
            document.getElementById('systemRoles').textContent = systemRoles;
            document.getElementById('customRoles').textContent = customRoles;

            // Also load permissions count
            const permResponse = await fetch('/admin/api/permissions');
            const permData = await permResponse.json();
            if (permData.success) {
                document.getElementById('totalPermissions').textContent = permData.data.length;
            }
        }
    } catch (error) {
        console.error('Error loading stats:', error);
    }
}

// ========== Render ==========

function renderTable(roles) {
    // This will be overridden by the view template
    console.log('Default renderTable called');
}

function showError(message) {
    const tbody = document.getElementById('rolesTableBody');
    tbody.innerHTML = `
        <tr>
            <td colspan="7" class="text-center py-5 text-danger">
                <i class="ri-error-warning-line" style="font-size: 48px;"></i>
                <p class="mt-3 mb-0">${message}</p>
                <button class="btn btn-sm btn-primary mt-2" onclick="loadRoles()">
                    <i class="ri-refresh-line me-1"></i> Retry
                </button>
            </td>
        </tr>
    `;
}

// ========== CRUD Operations ==========

function openCreateModal() {
    document.getElementById('roleModalLabel').textContent = 'Add Role';
    document.getElementById('roleId').value = '';
    document.getElementById('roleName').value = '';
    document.getElementById('roleDisplayName').value = '';
    document.getElementById('roleDescription').value = '';
    document.getElementById('roleName').readOnly = false;

    new bootstrap.Modal(document.getElementById('roleModal')).show();
}

async function editRole(id) {
    try {
        const response = await fetch(`/admin/api/roles/${id}`);
        const data = await response.json();

        if (data.success) {
            const role = data.data;

            document.getElementById('roleModalLabel').textContent = 'Edit Role';
            document.getElementById('roleId').value = role.id;
            document.getElementById('roleName').value = role.name;
            document.getElementById('roleDisplayName').value = role.display_name;
            document.getElementById('roleDescription').value = role.description;

            // Don't allow editing system role names
            if (role.is_system) {
                document.getElementById('roleName').readOnly = true;
            } else {
                document.getElementById('roleName').readOnly = false;
            }

            new bootstrap.Modal(document.getElementById('roleModal')).show();
        } else {
            showToast('Error', data.message, 'danger');
        }
    } catch (error) {
        showToast('Error', 'Network error', 'danger');
    }
}

async function saveRole() {
    const roleId = document.getElementById('roleId').value;
    const name = document.getElementById('roleName').value.trim();
    const displayName = document.getElementById('roleDisplayName').value.trim();
    const description = document.getElementById('roleDescription').value.trim();

    if (!name || !displayName) {
        showToast('Error', 'Name and display name are required', 'warning');
        return;
    }

    // Validate name format (lowercase, no spaces)
    if (!/^[a-z_]+$/.test(name)) {
        showToast('Error', 'Name must be lowercase with underscores only', 'warning');
        return;
    }

    const isEdit = roleId !== '';
    const url = isEdit ? `/admin/api/roles/${roleId}` : '/admin/api/roles';
    const method = isEdit ? 'PUT' : 'POST';

    try {
        const response = await fetch(url, {
            method: method,
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ name, display_name: displayName, description })
        });

        const data = await response.json();

        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('roleModal')).hide();
            loadRoles();
            loadStats();
            showToast('Success', data.message, 'success');
        } else {
            showToast('Error', data.message, 'danger');
        }
    } catch (error) {
        showToast('Error', 'Network error', 'danger');
    }
}

async function deleteRole(id) {
    const role = roles.find(r => r.id === id);

    if (!role) return;

    if (role.is_system) {
        showToast('Error', 'Cannot delete system role', 'danger');
        return;
    }

    if (!confirm(`Are you sure you want to delete role "${role.display_name}"?\n\n` +
        `This will affect ${role.users_count} user(s).`)) {
        return;
    }

    try {
        const response = await fetch(`/admin/api/roles/${id}`, {
            method: 'DELETE'
        });

        const data = await response.json();

        if (data.success) {
            loadRoles();
            loadStats();
            showToast('Success', data.message, 'success');
        } else {
            showToast('Error', data.message, 'danger');
        }
    } catch (error) {
        showToast('Error', 'Network error', 'danger');
    }
}

// ========== Permissions Management ==========

async function managePermissions(roleId) {
    currentRoleId = roleId;
    const role = roles.find(r => r.id === roleId);

    if (!role) return;

    document.getElementById('permissionsRoleName').textContent = role.display_name;
    document.getElementById('permissionsRoleId').value = roleId;

    // Show modal with loading state
    new bootstrap.Modal(document.getElementById('permissionsModal')).show();

    try {
        // Load all permissions grouped by resource
        const permResponse = await fetch('/admin/api/permissions/by-resource');
        const permData = await permResponse.json();

        // Load current role permissions
        const rolePermResponse = await fetch(`/admin/api/roles/${roleId}/permissions`);
        const rolePermData = await rolePermResponse.json();

        if (permData.success && rolePermData.success) {
            permissions = permData.data;
            rolePermissions = rolePermData.data.map(p => p.id);

            renderPermissionsMatrix();
        } else {
            document.getElementById('permissionsMatrix').innerHTML = `
                <div class="alert alert-danger">Failed to load permissions</div>
            `;
        }
    } catch (error) {
        document.getElementById('permissionsMatrix').innerHTML = `
            <div class="alert alert-danger">Network error</div>
        `;
    }
}

function renderPermissionsMatrix() {
    const container = document.getElementById('permissionsMatrix');

    if (permissions.length === 0) {
        container.innerHTML = `
            <div class="alert alert-warning">
                <i class="ri-alert-line me-2"></i>
                No permissions available
            </div>
        `;
        return;
    }

    let html = '';

    permissions.forEach(resource => {
        html += `
            <div class="card mb-3">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="ri-folder-line me-2"></i>
                        ${resource.resource}
                        <span class="badge bg-secondary ms-2">${resource.permissions.length} permissions</span>
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
        `;

        resource.permissions.forEach(perm => {
            const isChecked = rolePermissions.includes(perm.id);

            html += `
                <div class="col-md-6 col-lg-4 mb-2">
                    <div class="form-check">
                        <input 
                            class="form-check-input permission-checkbox" 
                            type="checkbox" 
                            value="${perm.id}" 
                            id="perm-${perm.id}"
                            ${isChecked ? 'checked' : ''}
                        >
                        <label class="form-check-label" for="perm-${perm.id}">
                            <strong>${perm.action}</strong>
                            <br>
                            <small class="text-muted">${perm.display_name}</small>
                        </label>
                    </div>
                </div>
            `;
        });

        html += `
                    </div>
                </div>
            </div>
        `;
    });

    container.innerHTML = html;
}

async function savePermissions() {
    const roleId = document.getElementById('permissionsRoleId').value;

    // Get all checked permission IDs
    const checkboxes = document.querySelectorAll('.permission-checkbox:checked');
    const permissionIds = Array.from(checkboxes).map(cb => parseInt(cb.value));

    try {
        const response = await fetch(`/admin/api/roles/${roleId}/permissions`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ permissions: permissionIds })
        });

        const data = await response.json();

        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('permissionsModal')).hide();
            loadRoles(); // Reload to update permission counts
            showToast('Success', data.message, 'success');
        } else {
            showToast('Error', data.message, 'danger');
        }
    } catch (error) {
        showToast('Error', 'Network error', 'danger');
    }
}

// ========== Utilities ==========

function showToast(title, message, type = 'info') {
    // Simple toast implementation
    // You can replace this with your toast library
    const bgClass = type === 'success' ? 'bg-success' :
        type === 'danger' ? 'bg-danger' :
            type === 'warning' ? 'bg-warning' : 'bg-info';

    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-white ${bgClass} border-0`;
    toast.setAttribute('role', 'alert');
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">
                <strong>${title}</strong><br>${message}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;

    // Add to page (you may need to adjust the container)
    let toastContainer = document.querySelector('.toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
        document.body.appendChild(toastContainer);
    }

    toastContainer.appendChild(toast);
    const bsToast = new bootstrap.Toast(toast);
    bsToast.show();

    // Auto remove after hidden
    toast.addEventListener('hidden.bs.toast', () => {
        toast.remove();
    });
}

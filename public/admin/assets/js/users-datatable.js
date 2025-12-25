/**
 * Users DataTable - Advanced Management
 */

// State
let filters = {
    search: '',
    role: '',
    status: '',
    date_from: '',
    date_to: '',
    sort_by: 'created_at',
    sort_dir: 'DESC',
    page: 1,
    per_page: 20
};

let selectedUsers = new Set();
let searchDebounce = null;
let stats = { total: 0, active: 0, inactive: 0, newMonth: 0 };

// Initialize
document.addEventListener('DOMContentLoaded', function () {
    loadUsers();
    bindEvents();
    loadStats();
});

// Bind Events
function bindEvents() {
    // Real-time search
    document.getElementById('searchInput').addEventListener('input', function () {
        clearTimeout(searchDebounce);
        searchDebounce = setTimeout(() => {
            filters.search = this.value;
            filters.page = 1;
            loadUsers();
        }, 300);
    });

    // Filter changes
    ['roleFilter', 'statusFilter', 'dateFrom', 'dateTo'].forEach(id => {
        document.getElementById(id).addEventListener('change', function () {
            filters.role = document.getElementById('roleFilter').value;
            filters.status = document.getElementById('statusFilter').value;
            filters.date_from = document.getElementById('dateFrom').value;
            filters.date_to = document.getElementById('dateTo').value;
            filters.page = 1;
            loadUsers();
        });
    });

    // Per page
    document.getElementById('perPageSelect').addEventListener('change', function () {
        filters.per_page = parseInt(this.value);
        filters.page = 1;
        loadUsers();
    });

    // Sorting
    document.querySelectorAll('.sortable').forEach(th => {
        th.addEventListener('click', function () {
            const column = this.dataset.sort;
            if (filters.sort_by === column) {
                filters.sort_dir = filters.sort_dir === 'ASC' ? 'DESC' : 'ASC';
            } else {
                filters.sort_by = column;
                filters.sort_dir = 'ASC';
            }
            updateSortIcons();
            loadUsers();
        });
    });

    // Select all
    document.getElementById('selectAll').addEventListener('change', function () {
        const checkboxes = document.querySelectorAll('.user-checkbox');
        checkboxes.forEach(cb => {
            cb.checked = this.checked;
            if (this.checked) {
                selectedUsers.add(parseInt(cb.value));
            } else {
                selectedUsers.delete(parseInt(cb.value));
            }
        });
        updateBulkActionsBar();
    });
}

// Update Sort Icons
function updateSortIcons() {
    document.querySelectorAll('.sortable i').forEach(icon => {
        icon.className = 'ri-arrow-up-down-line text-muted';
    });

    const activeSort = document.querySelector(`.sortable[data-sort="${filters.sort_by}"] i`);
    if (activeSort) {
        activeSort.className = filters.sort_dir === 'ASC' ? 'ri-arrow-up-line text-primary' : 'ri-arrow-down-line text-primary';
    }
}

// Reset Filters
function resetFilters() {
    document.getElementById('searchInput').value = '';
    document.getElementById('roleFilter').value = '';
    document.getElementById('statusFilter').value = '';
    document.getElementById('dateFrom').value = '';
    document.getElementById('dateTo').value = '';

    filters = {
        search: '',
        role: '',
        status: '',
        date_from: '',
        date_to: '',
        sort_by: 'created_at',
        sort_dir: 'DESC',
        page: 1,
        per_page: filters.per_page
    };

    loadUsers();
}

// Load Stats
async function loadStats() {
    try {
        const response = await fetch('/admin/api/users?per_page=1000');
        const data = await response.json();

        if (data.success) {
            const users = data.data;
            stats.total = data.pagination.total;
            stats.active = users.filter(u => u.is_active).length;
            stats.inactive = users.filter(u => !u.is_active).length;

            // New this month
            const thisMonth = new Date();
            thisMonth.setDate(1);
            stats.newMonth = users.filter(u => new Date(u.created_at) >= thisMonth).length;

            updateStatsDisplay();
        }
    } catch (error) {
        console.error('Error loading stats:', error);
    }
}

// Update Stats Display
function updateStatsDisplay() {
    document.getElementById('totalUsers').textContent = stats.total;
    document.getElementById('activeUsers').textContent = stats.active;
    document.getElementById('inactiveUsers').textContent = stats.inactive;
    document.getElementById('newUsers').textContent = stats.newMonth;
}

// Load Users
async function loadUsers() {
    showLoading();

    const params = new URLSearchParams(filters);

    try {
        const response = await fetch(`/admin/api/users?${params}`);
        const data = await response.json();

        if (data.success) {
            renderTable(data.data);
            renderPagination(data.pagination);
            updateResultsInfo(data.pagination);
            updateSortIcons();
        } else {
            showError('Failed to load users');
        }
    } catch (error) {
        console.error('Error:', error);
        showError('Network error');
    }
}

// Show Loading
function showLoading() {
    document.getElementById('usersTableBody').innerHTML = `
        <tr>
            <td colspan="8" class="text-center py-5">
                <div class="spinner-border text-primary" role="status"></div>
                <p class="text-muted mt-2">Loading users...</p>
            </td>
        </tr>
    `;
}

// Render Table
function renderTable(users) {
    const tbody = document.getElementById('usersTableBody');

    if (users.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="8" class="text-center py-5">
                    <i class="ri-inbox-line" style="font-size: 48px; color: #ccc;"></i>
                    <p class="text-muted mt-3 mb-0">No users found</p>
                    <small class="text-muted">Try adjusting your filters</small>
                </td>
            </tr>
        `;
        return;
    }

    tbody.innerHTML = users.map(user => `
        <tr class="${selectedUsers.has(user.id) ? 'table-active' : ''}">
            <td>
                <input type="checkbox" class="form-check-input user-checkbox" value="${user.id}" 
                    ${selectedUsers.has(user.id) ? 'checked' : ''}
                    onchange="toggleUserSelection(${user.id}, this.checked)">
            </td>
            <td><span class="badge bg-secondary-subtle text-secondary">#${user.id}</span></td>
            <td>
                <div class="d-flex align-items-center">
                    <div class="avatar-xs me-2">
                        <div class="avatar-title rounded-circle bg-primary-subtle text-primary">
                            ${user.email.charAt(0).toUpperCase()}
                        </div>
                    </div>
                    <div>
                        <div class="fw-medium">${user.email}</div>
                    </div>
                </div>
            </td>
            <td>${user.full_name || '<span class="text-muted">-</span>'}</td>
            <td>
                ${user.roles.map(r => `<span class="badge bg-primary-subtle text-primary me-1">${r.display_name}</span>`).join('') || '<span class="text-muted">No roles</span>'}
            </td>
            <td>
                <span class="badge bg-${user.is_active ? 'success' : 'danger'}">
                    <i class="ri-${user.is_active ? 'checkbox-circle' : 'close-circle'}-line me-1"></i>
                    ${user.is_active ? 'Active' : 'Inactive'}
                </span>
            </td>
            <td>
                <small class="text-muted">${formatDate(user.created_at)}</small>
            </td>
            <td>
                <div class="dropdown">
                    <button class="btn btn-sm btn-light dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="ri-more-fill"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end action-dropdown">
                        <li><a class="dropdown-item" onclick="editUser(${user.id})"><i class="ri-edit-line me-2"></i>Edit</a></li>
                        <li><a class="dropdown-item" onclick="viewUserDetails(${user.id})"><i class="ri-eye-line me-2"></i>View Details</a></li>
                        <li><a class="dropdown-item" onclick="resetUserPassword(${user.id})"><i class="ri-lock-password-line me-2"></i>Reset Password</a></li>
                        <li><a class="dropdown-item" onclick="sendEmailToUser(${user.id})"><i class="ri-mail-send-line me-2"></i>Send Email</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" onclick="deleteUser(${user.id})"><i class="ri-delete-bin-line me-2"></i>Delete</a></li>
                    </ul>
                </div>
            </td>
        </tr>
    `).join('');
}

// Toggle User Selection
function toggleUserSelection(userId, checked) {
    if (checked) {
        selectedUsers.add(userId);
    } else {
        selectedUsers.delete(userId);
    }
    updateBulkActionsBar();
}

// Update Bulk Actions Bar
function updateBulkActionsBar() {
    const bar = document.getElementById('bulkActionsBar');
    const count = document.getElementById('selectedCount');

    count.textContent = selectedUsers.size;

    if (selectedUsers.size > 0) {
        bar.classList.remove('d-none');
    } else {
        bar.classList.add('d-none');
    }
}

// Render Pagination
function renderPagination(pagination) {
    const paginationEl = document.getElementById('pagination');
    const { page, totalPages } = pagination;

    if (totalPages <= 1) {
        paginationEl.innerHTML = '';
        return;
    }

    let html = '';

    html += `
        <li class="page-item ${page === 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="changePage(${page - 1}); return false;">
                <i class="ri-arrow-left-s-line"></i>
            </a>
        </li>
    `;

    for (let i = 1; i <= totalPages; i++) {
        if (i === 1 || i === totalPages || (i >= page - 2 && i <= page + 2)) {
            html += `
                <li class="page-item ${i === page ? 'active' : ''}">
                    <a class="page-link" href="#" onclick="changePage(${i}); return false;">${i}</a>
                </li>
            `;
        } else if (i === page - 3 || i === page + 3) {
            html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
        }
    }

    html += `
        <li class="page-item ${page === totalPages ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="changePage(${page + 1}); return false;">
                <i class="ri-arrow-right-s-line"></i>
            </a>
        </li>
    `;

    paginationEl.innerHTML = html;
}

// Change Page
function changePage(page) {
    filters.page = page;
    loadUsers();
}

// Update Results Info
function updateResultsInfo(pagination) {
    const info = document.getElementById('resultsInfo');
    info.textContent = `Showing ${pagination.from} to ${pagination.to} of ${pagination.total} users`;
}

// Format Date
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
}

// ========== Bulk Actions ==========

async function bulkActivate() {
    if (selectedUsers.size === 0) return;

    if (!confirm(`Activate ${selectedUsers.size} users?`)) return;

    await bulkAction('/admin/api/users/bulk-activate', 'activated');
}

async function bulkDeactivate() {
    if (selectedUsers.size === 0) return;

    if (!confirm(`Deactivate ${selectedUsers.size} users?`)) return;

    await bulkAction('/admin/api/users/bulk-deactivate', 'deactivated');
}

async function bulkDelete() {
    if (selectedUsers.size === 0) return;

    if (!confirm(`Delete ${selectedUsers.size} users? This cannot be undone!`)) return;

    await bulkAction('/admin/api/users/bulk-delete', 'deleted');
}

async function bulkAction(url, action) {
    try {
        const response = await fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ user_ids: Array.from(selectedUsers) })
        });

        const data = await response.json();

        if (data.success) {
            selectedUsers.clear();
            loadUsers();
            loadStats();
            showToast('Success', data.message, 'success');
        } else {
            showToast('Error', data.message, 'danger');
        }
    } catch (error) {
        showToast('Error', 'Network error', 'danger');
    }
}

// Bulk Assign Role
function bulkAssignRole() {
    if (selectedUsers.size === 0) return;

    document.getElementById('bulkRoleCount').textContent = selectedUsers.size;
    new bootstrap.Modal(document.getElementById('bulkRoleModal')).show();
}

async function confirmBulkAssignRole() {
    const roleId = document.getElementById('bulkRoleSelect').value;

    if (!roleId) {
        showToast('Error', 'Please select a role', 'warning');
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
            loadStats();
            showToast('Success', data.message, 'success');
        } else {
            showToast('Error', data.message, 'danger');
        }
    } catch (error) {
        showToast('Error', 'Network error', 'danger');
    }
}

// Bulk Reset Password
function bulkResetPassword() {
    if (selectedUsers.size === 0) return;

    if (!confirm(`Reset password for ${selectedUsers.size} users? They will receive an email with new password.`)) return;

    // TODO: Implement bulk reset password API
    showToast('Info', 'Bulk reset password feature coming soon', 'info');
}

// Bulk Send Email
function bulkSendEmail() {
    if (selectedUsers.size === 0) return;

    // TODO: Implement bulk send email feature
    showToast('Info', 'Bulk send email feature coming soon', 'info');
}

// ========== Individual Actions ==========

function openCreateModal() {
    document.getElementById('userModalLabel').innerHTML = '<i class="ri-user-add-line me-2"></i>Create User';
    document.getElementById('userForm').reset();
    document.getElementById('userId').value = '';
    document.getElementById('userPassword').required = true;
    document.getElementById('passwordRequired').style.display = 'inline';
    new bootstrap.Modal(document.getElementById('userModal')).show();
}

async function editUser(id) {
    try {
        const response = await fetch(`/admin/api/users/${id}`);
        const data = await response.json();

        if (data.success) {
            const user = data.data;

            document.getElementById('userModalLabel').innerHTML = '<i class="ri-edit-line me-2"></i>Edit User';
            document.getElementById('userId').value = user.id;
            document.getElementById('userEmail').value = user.email;
            document.getElementById('userFullName').value = user.full_name;
            document.getElementById('userPassword').value = '';
            document.getElementById('userPassword').required = false;
            document.getElementById('passwordRequired').style.display = 'none';
            document.getElementById('userIsActive').checked = user.is_active;

            const rolesSelect = document.getElementById('userRoles');
            Array.from(rolesSelect.options).forEach(option => {
                option.selected = user.roles.some(r => r.id == option.value);
            });

            new bootstrap.Modal(document.getElementById('userModal')).show();
        }
    } catch (error) {
        showToast('Error', 'Error loading user', 'danger');
    }
}

async function saveUser() {
    const userId = document.getElementById('userId').value;
    const formData = {
        email: document.getElementById('userEmail').value,
        full_name: document.getElementById('userFullName').value,
        password: document.getElementById('userPassword').value,
        is_active: document.getElementById('userIsActive').checked,
        roles: Array.from(document.getElementById('userRoles').selectedOptions).map(o => parseInt(o.value))
    };

    const url = userId ? `/admin/api/users/${userId}` : '/admin/api/users';
    const method = userId ? 'PUT' : 'POST';

    try {
        const response = await fetch(url, {
            method: method,
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(formData)
        });

        const data = await response.json();

        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('userModal')).hide();
            loadUsers();
            loadStats();
            showToast('Success', data.message, 'success');
        } else {
            showToast('Error', data.message, 'danger');
        }
    } catch (error) {
        showToast('Error', 'Network error', 'danger');
    }
}

async function deleteUser(id) {
    if (!confirm('Delete this user? This cannot be undone!')) return;

    try {
        const response = await fetch(`/admin/api/users/${id}`, { method: 'DELETE' });
        const data = await response.json();

        if (data.success) {
            loadUsers();
            loadStats();
            showToast('Success', data.message, 'success');
        } else {
            showToast('Error', data.message, 'danger');
        }
    } catch (error) {
        showToast('Error', 'Network error', 'danger');
    }
}

async function viewUserDetails(id) {
    try {
        const response = await fetch(`/admin/api/users/${id}/details`);
        const data = await response.json();

        if (data.success) {
            const user = data.data;

            // Open modal with user details
            document.getElementById('detailsUserEmail').textContent = user.email;
            document.getElementById('detailsUserName').textContent = user.full_name || '-';
            document.getElementById('detailsUserStatus').innerHTML = user.is_active
                ? '<span class="badge bg-success"><i class="ri-checkbox-circle-line me-1"></i>Active</span>'
                : '<span class="badge bg-danger"><i class="ri-close-circle-line me-1"></i>Inactive</span>';
            document.getElementById('detailsUserRoles').innerHTML = user.roles.length > 0
                ? user.roles.map(r => `<span class="badge bg-primary me-1">${r.display_name}</span>`).join('')
                : '<span class="text-muted">No roles assigned</span>';
            document.getElementById('detailsUserCreated').textContent = user.stats.created_at || '-';
            document.getElementById('detailsUserLastLogin').textContent = user.stats.last_login || 'Never';

            new bootstrap.Modal(document.getElementById('userDetailsModal')).show();
        } else {
            showToast('Error', data.message, 'danger');
        }
    } catch (error) {
        showToast('Error', 'Error loading user details', 'danger');
    }
}

async function resetUserPassword(id) {
    if (!confirm('Reset password for this user? A new random password will be generated.')) return;

    try {
        const response = await fetch(`/admin/api/users/${id}/reset-password`, {
            method: 'POST'
        });

        const data = await response.json();

        if (data.success) {
            // Show the new password to admin
            const message = `Password reset successfully!\n\nNew password: ${data.new_password}\n\nPlease save this password and share it securely with the user.`;
            showToast('Success', message, 'success');
        } else {
            showToast('Error', data.message, 'danger');
        }
    } catch (error) {
        showToast('Error', 'Network error', 'danger');
    }
}

async function sendEmailToUser(id) {
    try {
        // Get user details first
        const response = await fetch(`/admin/api/users/${id}`);
        const data = await response.json();

        if (data.success) {
            // Populate modal
            document.getElementById('emailUserId').value = id;
            document.getElementById('emailUserEmail').textContent = data.data.email;
            document.getElementById('emailSubject').value = '';
            document.getElementById('emailMessage').value = '';

            new bootstrap.Modal(document.getElementById('sendEmailModal')).show();
        }
    } catch (error) {
        showToast('Error', 'Error loading user', 'danger');
    }
}

// ========== Export ==========

function exportData(type) {
    let url = '/admin/api/users/export?type=' + type;

    if (type === 'selected') {
        if (selectedUsers.size === 0) {
            showToast('Warning', 'No users selected', 'warning');
            return;
        }
        url += '&amp;ids=' + Array.from(selectedUsers).join(',');
    } else if (type === 'filtered') {
        const params = new URLSearchParams(filters);
        url += '&amp;' + params.toString();
    }

    // Trigger download
    window.location.href = url;
    showToast('Info', 'Exporting users to CSV...', 'info');
}

// Send Email
async function confirmSendEmail() {
    const userId = document.getElementById('emailUserId').value;
    const subject = document.getElementById('emailSubject').value;
    const message = document.getElementById('emailMessage').value;

    if (!subject || !message) {
        showToast('Error', 'Subject and message are required', 'warning');
        return;
    }

    try {
        const response = await fetch(`/admin/api/users/${userId}/send-email`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ subject, message })
        });

        const data = await response.json();

        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('sendEmailModal')).hide();
            showToast('Success', data.message, 'success');
        } else {
            showToast('Error', data.message, 'danger');
        }
    } catch (error) {
        showToast('Error', 'Network error', 'danger');
    }
}

// ========== Utilities ==========

function showError(message) {
    document.getElementById('usersTableBody').innerHTML = `
        <tr>
            <td colspan="8" class="text-center py-5 text-danger">
                <i class="ri-error-warning-line" style="font-size: 48px;"></i>
                <p class="mt-3 mb-0">${message}</p>
                <button class="btn btn-sm btn-primary mt-2" onclick="loadUsers()">
                    <i class="ri-refresh-line me-1"></i> Retry
                </button>
            </td>
        </tr>
    `;
}

function showToast(title, message, type = 'info') {
    // Simple alert for now - can be replaced with toast library
    const icon = {
        success: '✓',
        danger: '✗',
        warning: '⚠',
        info: 'ℹ'
    }[type] || 'ℹ';

    alert(`${icon} ${title}\n${message}`);
}

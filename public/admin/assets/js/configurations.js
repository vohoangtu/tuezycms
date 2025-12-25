/**
 * Configurations Management JavaScript
 * Similar to modules.js - handles toggle and configure
 */

document.addEventListener('DOMContentLoaded', function () {
    loadConfigurations();

    // Tab click handlers
    document.querySelectorAll('[data-bs-toggle="tab"]').forEach(tab => {
        tab.addEventListener('shown.bs.tab', function (e) {
            const target = e.target.getAttribute('href');
            const category = target.replace('#', '').replace('-configs', '').replace('all', '');
            loadConfigurations(category || null);
        });
    });
});

/**
 * Load configurations
 */
function loadConfigurations(category = null) {
    const container = category ?
        document.getElementById(`config-list-${category}`) :
        document.getElementById('config-list-all');

    if (!container) return;

    container.innerHTML = '<div class="col-12 text-center py-5"><div class="spinner-border text-primary"></div></div>';

    const url = category ? `/admin/api/configurations?category=${category}` : '/admin/api/configurations';

    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderConfigurations(data.data, container);
            } else {
                showError(data.message || 'Không thể tải configurations');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showError('Lỗi khi tải configurations');
        });
}

/**
 * Render configurations
 */
function renderConfigurations(configs, container) {
    if (!configs || configs.length === 0) {
        container.innerHTML = `
            <div class="col-12">
                <div class="alert alert-info">
                    <i class="ri-information-line me-2"></i>
                    Không có configuration nào.
                </div>
            </div>
        `;
        return;
    }

    container.innerHTML = configs.map(config => `
        <div class="col-xl-3 col-sm-6">
            <div class="card card-height-100 ${config.is_enabled ? 'border-success' : ''}">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h5 class="card-title mb-1">${escapeHtml(config.display_name)}</h5>
                            <p class="text-muted mb-0">${escapeHtml(config.description || '')}</p>
                        </div>
                        <div class="flex-shrink-0">
                            <span class="badge ${config.is_enabled ? 'bg-success' : 'bg-secondary'}">
                                ${config.is_enabled ? 'Enabled' : 'Disabled'}
                            </span>
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <span class="badge bg-info-subtle text-info">${escapeHtml(config.category)}</span>
                    </div>
                    
                    <div class="mt-3 d-flex gap-2">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" 
                                   id="config-${config.id}" 
                                   ${config.is_enabled ? 'checked' : ''}
                                   onchange="toggleConfiguration(${config.id}, this.checked)">
                            <label class="form-check-label" for="config-${config.id}">
                                ${config.is_enabled ? 'Bật' : 'Tắt'}
                            </label>
                        </div>
                        ${config.config ? `
                        <button class="btn btn-sm btn-soft-primary ms-auto" 
                                onclick="openConfigModal(${config.id})">
                            <i class="ri-settings-3-line"></i> Cấu hình
                        </button>
                        ` : ''}
                    </div>
                </div>
            </div>
        </div>
    `).join('');
}

/**
 * Toggle configuration
 */
function toggleConfiguration(id, enabled) {
    fetch(`/admin/api/configurations/${id}/toggle`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ is_enabled: enabled })
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('success', enabled ? 'Configuration đã được bật' : 'Configuration đã được tắt');
                // Reload current tab
                const activeTab = document.querySelector('[data-bs-toggle="tab"].active');
                if (activeTab) {
                    activeTab.click();
                }
            } else {
                showToast('error', data.message || 'Không thể cập nhật configuration');
                // Revert checkbox
                const checkbox = document.getElementById(`config-${id}`);
                if (checkbox) checkbox.checked = !enabled;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('error', 'Lỗi khi cập nhật configuration');
            // Revert checkbox
            const checkbox = document.getElementById(`config-${id}`);
            if (checkbox) checkbox.checked = !enabled;
        });
}

/**
 * Open config modal
 */
function openConfigModal(id) {
    fetch(`/admin/api/configurations/${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const config = data.data;
                const modalBody = document.getElementById('configModalBody');

                // Build form from config JSON
                let formHtml = `<form id="configForm" data-config-id="${id}">`;

                if (config.config) {
                    Object.entries(config.config).forEach(([key, value]) => {
                        formHtml += `
                            <div class="mb-3">
                                <label class="form-label">${key}</label>
                                <input type="text" class="form-control" 
                                       name="${key}" value="${escapeHtml(value)}">
                            </div>
                        `;
                    });
                } else {
                    formHtml += '<p class="text-muted">Configuration này không có tùy chọn.</p>';
                }

                formHtml += '</form>';
                modalBody.innerHTML = formHtml;

                const modal = new bootstrap.Modal(document.getElementById('configModal'));
                modal.show();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('error', 'Không thể tải configuration');
        });
}

/**
 * Save configuration
 */
document.getElementById('saveConfigBtn')?.addEventListener('click', function () {
    const form = document.getElementById('configForm');
    if (!form) return;

    const configId = form.dataset.configId;
    const formData = new FormData(form);
    const config = {};

    formData.forEach((value, key) => {
        config[key] = value;
    });

    fetch(`/admin/api/configurations/${configId}/config`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ config })
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('success', 'Cấu hình đã được lưu');
                bootstrap.Modal.getInstance(document.getElementById('configModal')).hide();
                // Reload current tab
                const activeTab = document.querySelector('[data-bs-toggle="tab"].active');
                if (activeTab) activeTab.click();
            } else {
                showToast('error', data.message || 'Không thể lưu cấu hình');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('error', 'Lỗi khi lưu cấu hình');
        });
});

/**
 * Utility functions
 */
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function showToast(type, message) {
    // Simple toast notification
    const toast = document.createElement('div');
    toast.className = `alert alert-${type === 'success' ? 'success' : 'danger'} position-fixed top-0 end-0 m-3`;
    toast.style.zIndex = '9999';
    toast.innerHTML = `
        <i class="ri-${type === 'success' ? 'check' : 'error'}-warning-line me-2"></i>
        ${message}
    `;
    document.body.appendChild(toast);

    setTimeout(() => {
        toast.remove();
    }, 3000);
}

function showError(message) {
    showToast('error', message);
}

// Clear Cache button handler
document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('clearCacheBtn')?.addEventListener('click', function () {
        if (!confirm('Bạn có chắc muốn xóa tất cả cache? Hệ thống có thể chậm hơn trong lần truy cập đầu tiên.')) {
            return;
        }

        const btn = this;
        const originalHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Đang xóa...';

        fetch('/admin/api/cache/clear', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            }
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('success', '✅ ' + (data.message || 'Cache đã được xóa thành công!'));
                } else {
                    showToast('error', data.message || 'Không thể xóa cache');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('error', 'Lỗi khi xóa cache');
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = originalHtml;
            });
    });

    // Cache button (disabled - coming soon)
    document.getElementById('cacheBtn')?.addEventListener('click', function () {
        showToast('info', 'Tính năng đang được phát triển');
    });
});

/**
 * Module Management JavaScript
 * Handles module toggle, configuration, and category filtering
 */

let allModules = [];
let currentCategory = 'all';

// Load modules on page load
document.addEventListener('DOMContentLoaded', function () {
    loadModules();
});

/**
 * Load all modules from API
 */
async function loadModules() {
    try {
        const response = await fetch('/admin/api/modules');
        const result = await response.json();

        if (result.success) {
            allModules = result.data;
            renderModulesByCategory('all', allModules);

            // Also populate category-specific tabs
            renderModulesByCategory('user', allModules.filter(m => m.category === 'user'));
            renderModulesByCategory('product', allModules.filter(m => m.category === 'product'));
            renderModulesByCategory('content', allModules.filter(m => m.category === 'content'));
            renderModulesByCategory('system', allModules.filter(m => m.category === 'system'));
        } else {
            showToast('Error loading modules: ' + result.message, 'error');
        }
    } catch (error) {
        console.error('Error loading modules:', error);
        showToast('Failed to load modules', 'error');
    }
}

/**
 * Render modules for a specific category
 */
function renderModulesByCategory(category, modules) {
    const containerId = category === 'all' ? 'all-modules-container' : `${category}-modules-container`;
    const container = document.getElementById(containerId);

    if (!container) return;

    if (modules.length === 0) {
        container.innerHTML = `
            <div class="col-12">
                <div class="text-center py-5">
                    <i class="ri-inbox-line" style="font-size: 48px; color: #ccc;"></i>
                    <p class="text-muted mt-3">No modules in this category</p>
                </div>
            </div>
        `;
        return;
    }

    container.innerHTML = modules.map(module => createModuleCard(module)).join('');
}

/**
 * Create HTML for a module card
 */
function createModuleCard(module) {
    const isEnabled = module.is_enabled;
    const isSystem = module.is_system;
    const iconClass = module.icon || 'ri-puzzle-line';

    return `
        <div class="col-xl-3 col-lg-4 col-md-6">
            <div class="card module-card ${isEnabled ? 'border-success' : ''}" data-module-id="${module.id}">
                <div class="card-body">
                    <div class="d-flex align-items-start mb-3">
                        <div class="avatar-sm flex-shrink-0 me-3">
                            <div class="avatar-title bg-${isEnabled ? 'success' : 'secondary'}-subtle rounded fs-3">
                                <i class="${iconClass} text-${isEnabled ? 'success' : 'secondary'}"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h5 class="card-title mb-1">${module.display_name}</h5>
                            <p class="text-muted mb-0">
                                ${isSystem ? '<span class="badge bg-info-subtle text-info"><i class="ri-lock-line"></i> System</span>' : '<span class="badge bg-success-subtle text-success">Optional</span>'}
                            </p>
                        </div>
                    </div>
                    
                    <p class="text-muted mb-3" style="min-height: 60px;">${module.description || 'No description'}</p>
                    
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="form-check form-switch form-switch-lg">
                            <input class="form-check-input module-toggle" type="checkbox" 
                                id="module-${module.id}" 
                                ${isEnabled ? 'checked' : ''} 
                                ${isSystem ? 'disabled' : ''}
                                onchange="toggleModule(${module.id}, this.checked)">
                            <label class="form-check-label" for="module-${module.id}">
                                ${isEnabled ? '<span class="text-success">Enabled</span>' : '<span class="text-muted">Disabled</span>'}
                            </label>
                        </div>
                        
                        ${!isSystem && Object.keys(module.config || {}).length > 0 ? `
                            <button class="btn btn-sm btn-light" onclick="openConfigModal(${module.id})" 
                                ${!isEnabled ? 'disabled' : ''}>
                                <i class="ri-settings-3-line"></i>
                            </button>
                        ` : ''}
                    </div>
                    
                    <div class="mt-2">
                        <small class="text-muted">
                            <i class="ri-information-line"></i> 
                            Version ${module.version}
                        </small>
                    </div>
                </div>
            </div>
        </div>
    `;
}

/**
 * Toggle module enabled/disabled
 */
async function toggleModule(moduleId, enabled) {
    try {
        const response = await fetch(`/admin/api/modules/${moduleId}/toggle`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ enabled: enabled })
        });

        const result = await response.json();

        if (result.success) {
            showToast(result.message, 'success');

            // Update the module in our local array
            const moduleIndex = allModules.findIndex(m => m.id === moduleId);
            if (moduleIndex !== -1) {
                allModules[moduleIndex].is_enabled = enabled;
            }

            // Reload to refresh all tabs
            loadModules();
        } else {
            showToast(result.message, 'error');
            // Revert the toggle
            const toggle = document.getElementById(`module-${moduleId}`);
            if (toggle) {
                toggle.checked = !enabled;
            }
        }
    } catch (error) {
        console.error('Error toggling module:', error);
        showToast('Failed to toggle module', 'error');

        // Revert the toggle
        const toggle = document.getElementById(`module-${moduleId}`);
        if (toggle) {
            toggle.checked = !enabled;
        }
    }
}

/**
 * Open configuration modal for a module
 */
async function openConfigModal(moduleId) {
    try {
        const response = await fetch(`/admin/api/modules/${moduleId}`);
        const result = await response.json();

        if (result.success) {
            const module = result.data;

            document.getElementById('moduleConfigId').value = moduleId;
            document.getElementById('moduleConfigModalLabel').textContent = `Configure ${module.display_name}`;

            // Render config fields
            const configFields = document.getElementById('configFields');
            const config = module.config || {};

            if (Object.keys(config).length === 0) {
                configFields.innerHTML = '<p class="text-muted">No configuration options available for this module.</p>';
            } else {
                configFields.innerHTML = Object.keys(config).map(key => `
                    <div class="mb-3">
                        <label for="config-${key}" class="form-label">${formatConfigKey(key)}</label>
                        <input type="text" class="form-control" id="config-${key}" 
                            value="${config[key]}" data-config-key="${key}">
                    </div>
                `).join('');
            }

            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('moduleConfigModal'));
            modal.show();
        } else {
            showToast('Error loading module config', 'error');
        }
    } catch (error) {
        console.error('Error opening config modal:', error);
        showToast('Failed to load module configuration', 'error');
    }
}

/**
 * Save module configuration
 */
async function saveModuleConfig() {
    const moduleId = document.getElementById('moduleConfigId').value;
    const configInputs = document.querySelectorAll('#configFields input[data-config-key]');

    const config = {};
    configInputs.forEach(input => {
        config[input.dataset.configKey] = input.value;
    });

    try {
        const response = await fetch(`/admin/api/modules/${moduleId}/config`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ config: config })
        });

        const result = await response.json();

        if (result.success) {
            showToast(result.message, 'success');

            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('moduleConfigModal'));
            modal.hide();

            // Reload modules
            loadModules();
        } else {
            showToast(result.message, 'error');
        }
    } catch (error) {
        console.error('Error saving config:', error);
        showToast('Failed to save configuration', 'error');
    }
}

/**
 * Format config key for display
 */
function formatConfigKey(key) {
    return key
        .replace(/_/g, ' ')
        .replace(/\b\w/g, l => l.toUpperCase());
}

/**
 * Show toast notification
 */
function showToast(message, type = 'info') {
    // Create toast container if it doesn't exist
    let toastContainer = document.querySelector('.toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
        toastContainer.style.zIndex = '9999';
        document.body.appendChild(toastContainer);
    }

    // Create toast
    const toastId = 'toast-' + Date.now();
    const iconClass = type === 'success' ? 'ri-check-line' : type === 'error' ? 'ri-error-warning-line' : 'ri-information-line';
    const bgClass = type === 'success' ? 'bg-success' : type === 'error' ? 'bg-danger' : 'bg-primary';

    const toastHtml = `
        <div id="${toastId}" class="toast align-items-center text-white ${bgClass} border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="${iconClass} me-2"></i>${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    `;

    toastContainer.insertAdjacentHTML('beforeend', toastHtml);

    const toastElement = document.getElementById(toastId);
    const toast = new bootstrap.Toast(toastElement, { autohide: true, delay: 3000 });
    toast.show();

    // Remove toast element after it's hidden
    toastElement.addEventListener('hidden.bs.toast', function () {
        toastElement.remove();
    });
}

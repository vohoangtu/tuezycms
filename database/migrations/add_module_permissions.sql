-- Add Module and Settings Permissions
-- This migration adds permissions for module management and settings

-- Insert permissions for Modules
INSERT INTO permissions (name, display_name, description, resource, action) VALUES
('modules.view', 'View Modules', 'View module management page and list modules', 'modules', 'view'),
('modules.toggle', 'Toggle Modules', 'Enable or disable modules', 'modules', 'toggle'),
('modules.configure', 'Configure Modules', 'Update module configuration', 'modules', 'configure')
ON DUPLICATE KEY UPDATE display_name=VALUES(display_name);

-- Update Settings permissions (already exist, just ensure they're there)
INSERT INTO permissions (name, display_name, description, resource, action) VALUES
('settings.view', 'View Settings', 'View system settings', 'settings', 'view'),
('settings.edit', 'Edit Settings', 'Edit system settings', 'settings', 'edit')
ON DUPLICATE KEY UPDATE display_name=VALUES(display_name);

-- Assign all module and settings permissions to Super Admin role
INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r
CROSS JOIN permissions p
WHERE r.name = 'super_admin'
AND p.resource IN ('modules', 'settings')
ON DUPLICATE KEY UPDATE role_id=VALUES(role_id);

-- Seed RBAC Data
-- This file seeds default roles and permissions

-- Insert default roles
INSERT INTO roles (name, display_name, description, is_system) VALUES
('super_admin', 'Super Admin', 'Full system access with all permissions', 1),
('admin', 'Admin', 'Administrative access to manage content and products', 1),
('editor', 'Editor', 'Can create and edit content', 1),
('viewer', 'Viewer', 'Read-only access to view content', 1)
ON DUPLICATE KEY UPDATE display_name=VALUES(display_name);

-- Insert permissions for Articles
INSERT INTO permissions (name, display_name, description, resource, action) VALUES
('articles.view', 'View Articles', 'View articles list and details', 'articles', 'view'),
('articles.create', 'Create Articles', 'Create new articles', 'articles', 'create'),
('articles.edit', 'Edit Articles', 'Edit existing articles', 'articles', 'edit'),
('articles.delete', 'Delete Articles', 'Delete articles', 'articles', 'delete'),
('articles.publish', 'Publish Articles', 'Publish/unpublish articles', 'articles', 'publish')
ON DUPLICATE KEY UPDATE display_name=VALUES(display_name);

-- Insert permissions for Products
INSERT INTO permissions (name, display_name, description, resource, action) VALUES
('products.view', 'View Products', 'View products list and details', 'products', 'view'),
('products.create', 'Create Products', 'Create new products', 'products', 'create'),
('products.edit', 'Edit Products', 'Edit existing products', 'products', 'edit'),
('products.delete', 'Delete Products', 'Delete products', 'products', 'delete'),
('products.publish', 'Publish Products', 'Publish/unpublish products', 'products', 'publish')
ON DUPLICATE KEY UPDATE display_name=VALUES(display_name);

-- Insert permissions for Orders
INSERT INTO permissions (name, display_name, description, resource, action) VALUES
('orders.view', 'View Orders', 'View orders list and details', 'orders', 'view'),
('orders.create', 'Create Orders', 'Create new orders', 'orders', 'create'),
('orders.edit', 'Edit Orders', 'Edit existing orders', 'orders', 'edit'),
('orders.delete', 'Delete Orders', 'Delete orders', 'orders', 'delete')
ON DUPLICATE KEY UPDATE display_name=VALUES(display_name);

-- Insert permissions for Promotions
INSERT INTO permissions (name, display_name, description, resource, action) VALUES
('promotions.view', 'View Promotions', 'View promotions list and details', 'promotions', 'view'),
('promotions.create', 'Create Promotions', 'Create new promotions', 'promotions', 'create'),
('promotions.edit', 'Edit Promotions', 'Edit existing promotions', 'promotions', 'edit'),
('promotions.delete', 'Delete Promotions', 'Delete promotions', 'promotions', 'delete')
ON DUPLICATE KEY UPDATE display_name=VALUES(display_name);

-- Insert permissions for Media
INSERT INTO permissions (name, display_name, description, resource, action) VALUES
('media.view', 'View Media', 'View media files', 'media', 'view'),
('media.upload', 'Upload Media', 'Upload new media files', 'media', 'upload'),
('media.delete', 'Delete Media', 'Delete media files', 'media', 'delete')
ON DUPLICATE KEY UPDATE display_name=VALUES(display_name);

-- Insert permissions for Settings
INSERT INTO permissions (name, display_name, description, resource, action) VALUES
('settings.view', 'View Settings', 'View system settings', 'settings', 'view'),
('settings.edit', 'Edit Settings', 'Edit system settings', 'settings', 'edit')
ON DUPLICATE KEY UPDATE display_name=VALUES(display_name);

-- Insert permissions for Users
INSERT INTO permissions (name, display_name, description, resource, action) VALUES
('users.view', 'View Users', 'View users list and details', 'users', 'view'),
('users.create', 'Create Users', 'Create new users', 'users', 'create'),
('users.edit', 'Edit Users', 'Edit existing users', 'users', 'edit'),
('users.delete', 'Delete Users', 'Delete users', 'users', 'delete')
ON DUPLICATE KEY UPDATE display_name=VALUES(display_name);

-- Insert permissions for Roles
INSERT INTO permissions (name, display_name, description, resource, action) VALUES
('roles.view', 'View Roles', 'View roles and permissions', 'roles', 'view'),
('roles.create', 'Create Roles', 'Create new roles', 'roles', 'create'),
('roles.edit', 'Edit Roles', 'Edit existing roles', 'roles', 'edit'),
('roles.delete', 'Delete Roles', 'Delete roles', 'roles', 'delete')
ON DUPLICATE KEY UPDATE display_name=VALUES(display_name);

-- Assign all permissions to Super Admin role
INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r
CROSS JOIN permissions p
WHERE r.name = 'super_admin'
ON DUPLICATE KEY UPDATE role_id=VALUES(role_id);

-- Assign permissions to Admin role (all except users and roles management)
INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r
CROSS JOIN permissions p
WHERE r.name = 'admin'
AND p.resource IN ('articles', 'products', 'orders', 'promotions', 'media', 'settings')
ON DUPLICATE KEY UPDATE role_id=VALUES(role_id);

-- Assign permissions to Editor role (articles and products only, no delete)
INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r
CROSS JOIN permissions p
WHERE r.name = 'editor'
AND p.resource IN ('articles', 'products', 'media')
AND p.action IN ('view', 'create', 'edit', 'upload')
ON DUPLICATE KEY UPDATE role_id=VALUES(role_id);

-- Assign permissions to Viewer role (view only)
INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r
CROSS JOIN permissions p
WHERE r.name = 'viewer'
AND p.action = 'view'
ON DUPLICATE KEY UPDATE role_id=VALUES(role_id);

-- Assign super_admin role to existing admin user
INSERT INTO user_roles (user_id, role_id)
SELECT u.id, r.id
FROM users u
CROSS JOIN roles r
WHERE u.email = 'admin@tuzycms.com'
AND r.name = 'super_admin'
ON DUPLICATE KEY UPDATE user_id=VALUES(user_id);

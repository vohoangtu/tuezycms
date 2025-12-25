-- Module Management System - Enhanced Modules Table
-- This migration updates the existing modules table to support comprehensive module management

-- Drop existing modules table if it exists
DROP TABLE IF EXISTS modules;

-- Create enhanced modules table
CREATE TABLE modules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE COMMENT 'Module identifier, e.g., branch_management',
    display_name VARCHAR(255) NOT NULL COMMENT 'Human-readable name',
    description TEXT COMMENT 'Module description',
    icon VARCHAR(50) COMMENT 'Icon class for UI, e.g., ri-git-branch-line',
    category VARCHAR(50) DEFAULT 'general' COMMENT 'Module category: user, product, content, system',
    is_enabled TINYINT(1) DEFAULT 0 COMMENT 'Whether module is currently enabled',
    is_system TINYINT(1) DEFAULT 0 COMMENT 'System modules cannot be disabled',
    config JSON COMMENT 'Module-specific configuration',
    version VARCHAR(20) DEFAULT '1.0.0' COMMENT 'Module version',
    sort_order INT DEFAULT 0 COMMENT 'Display order in UI',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_name (name),
    INDEX idx_enabled (is_enabled),
    INDEX idx_category (category),
    INDEX idx_system (is_system)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert system modules (cannot be disabled)
INSERT INTO modules (name, display_name, description, icon, category, is_enabled, is_system, sort_order) VALUES
('user_management', 'User Management', 'Quản lý người dùng hệ thống', 'ri-user-line', 'user', 1, 1, 1),
('role_management', 'Role & Permission Management', 'Quản lý vai trò và quyền hạn', 'ri-shield-user-line', 'user', 1, 1, 2),
('article_management', 'Article Management', 'Quản lý bài viết', 'ri-article-line', 'content', 1, 1, 10),
('product_management', 'Product Management', 'Quản lý sản phẩm', 'ri-shopping-bag-line', 'product', 1, 1, 20),
('order_management', 'Order Management', 'Quản lý đơn hàng', 'ri-shopping-cart-line', 'product', 1, 1, 21),
('media_management', 'Media Management', 'Quản lý file và hình ảnh', 'ri-image-line', 'system', 1, 1, 30);

-- Insert optional modules (can be enabled/disabled)
INSERT INTO modules (name, display_name, description, icon, category, is_enabled, is_system, sort_order) VALUES
('branch_management', 'Branch Management', 'Quản lý chi nhánh', 'ri-git-branch-line', 'user', 0, 0, 3),
('department_management', 'Department Management', 'Quản lý phòng ban', 'ri-building-line', 'user', 0, 0, 4),
('position_management', 'Position Management', 'Quản lý chức vụ', 'ri-user-star-line', 'user', 0, 0, 5),
('promotion_management', 'Promotion Management', 'Quản lý khuyến mãi', 'ri-percent-line', 'product', 0, 0, 22),
('seo_tools', 'SEO Tools', 'Công cụ tối ưu SEO', 'ri-seo-line', 'system', 0, 0, 31);

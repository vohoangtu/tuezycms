-- Media Upload Management System Migration

-- Media Files Table
CREATE TABLE IF NOT EXISTS media_files (
    id INT AUTO_INCREMENT PRIMARY KEY,
    filename VARCHAR(255) NOT NULL,
    original_filename VARCHAR(255) NOT NULL,
    path VARCHAR(500) NOT NULL,
    type ENUM('image', 'video', 'document', 'other') NOT NULL,
    mime_type VARCHAR(100) NOT NULL,
    size BIGINT NOT NULL,
    width INT NULL,
    height INT NULL,
    thumbnail_path VARCHAR(500) NULL,
    alt_text VARCHAR(255) NULL,
    description TEXT NULL,
    created_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_type (type),
    INDEX idx_created (created_at),
    INDEX idx_filename (filename)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Media Usage Tracking Table
CREATE TABLE IF NOT EXISTS media_usage (
    id INT AUTO_INCREMENT PRIMARY KEY,
    media_id INT NOT NULL,
    entity_type VARCHAR(50) NOT NULL, -- 'product', 'article', 'page', 'site_setting'
    entity_id INT NULL, -- NULL for site_setting (use key instead)
    entity_key VARCHAR(100) NULL, -- For site_setting keys like 'logo', 'banner'
    usage_type VARCHAR(50) NOT NULL, -- 'featured_image', 'gallery', 'content', 'logo', 'banner', 'slider'
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (media_id) REFERENCES media_files(id) ON DELETE CASCADE,
    INDEX idx_media (media_id),
    INDEX idx_entity (entity_type, entity_id),
    INDEX idx_usage (usage_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Site Settings Table (if not exists)
CREATE TABLE IF NOT EXISTS site_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT NULL,
    setting_type VARCHAR(50) NOT NULL, -- 'text', 'image', 'json', 'array'
    description VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_key (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default site settings
INSERT INTO site_settings (setting_key, setting_value, setting_type, description) VALUES
('site_logo', NULL, 'image', 'Site Logo'),
('site_banner', NULL, 'image', 'Site Banner'),
('site_slider', '[]', 'json', 'Site Slider Images (JSON array)')
ON DUPLICATE KEY UPDATE setting_type = VALUES(setting_type);


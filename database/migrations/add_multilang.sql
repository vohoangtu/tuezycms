-- Add multilingual support to database

-- Add locale column to articles
ALTER TABLE articles ADD COLUMN locale VARCHAR(10) DEFAULT 'vi' AFTER id;
ALTER TABLE articles ADD INDEX idx_locale (locale);
ALTER TABLE articles ADD INDEX idx_slug_locale (slug, locale);

-- Add locale column to products
ALTER TABLE products ADD COLUMN locale VARCHAR(10) DEFAULT 'vi' AFTER id;
ALTER TABLE products ADD INDEX idx_locale (locale);
ALTER TABLE products ADD INDEX idx_slug_locale (slug, locale);

-- Add locale column to product_categories
ALTER TABLE product_categories ADD COLUMN locale VARCHAR(10) DEFAULT 'vi' AFTER id;
ALTER TABLE product_categories ADD INDEX idx_locale (locale);
ALTER TABLE product_categories ADD INDEX idx_slug_locale (slug, locale);

-- Add locale column to article_types
ALTER TABLE article_types ADD COLUMN locale VARCHAR(10) DEFAULT 'vi' AFTER id;
ALTER TABLE article_types ADD INDEX idx_locale (locale);
ALTER TABLE article_types ADD INDEX idx_slug_locale (slug, locale);

-- Create pages table for static pages (contact, about, etc.)
CREATE TABLE IF NOT EXISTS pages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    locale VARCHAR(10) DEFAULT 'vi',
    slug VARCHAR(255) NOT NULL,
    title VARCHAR(500) NOT NULL,
    content TEXT NOT NULL, -- Encrypted
    meta_title VARCHAR(255),
    meta_description TEXT,
    meta_keywords VARCHAR(500),
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_slug_locale (slug, locale),
    INDEX idx_locale (locale),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default pages
INSERT INTO pages (locale, slug, title, content, is_active) VALUES
('vi', 'lien-he', 'Liên hệ', 'Nội dung trang liên hệ', 1),
('en', 'contact', 'Contact', 'Contact page content', 1);


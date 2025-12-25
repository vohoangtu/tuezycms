-- Create configurations table
CREATE TABLE IF NOT EXISTS `configurations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL UNIQUE,
  `display_name` varchar(255) NOT NULL,
  `description` text,
  `category` varchar(50) NOT NULL DEFAULT 'general',
  `is_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `config` json DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_category` (`category`),
  KEY `idx_enabled` (`is_enabled`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default configurations
INSERT INTO `configurations` (`name`, `display_name`, `description`, `category`, `is_enabled`, `config`) VALUES
('email_notifications', 'Email Notifications', 'Gửi thông báo qua email', 'notification', 1, '{"smtp_host": "smtp.gmail.com", "smtp_port": 587}'),
('sms_notifications', 'SMS Notifications', 'Gửi thông báo qua SMS', 'notification', 0, '{"provider": "twilio"}'),
('maintenance_mode', 'Maintenance Mode', 'Chế độ bảo trì website', 'system', 0, '{"message": "Website đang bảo trì"}'),
('cache_enabled', 'Cache System', 'Bật/tắt hệ thống cache', 'performance', 1, '{"driver": "file", "ttl": 3600}'),
('debug_mode', 'Debug Mode', 'Chế độ debug cho developers', 'system', 0, NULL),
('auto_backup', 'Auto Backup', 'Tự động backup database', 'system', 1, '{"frequency": "daily", "time": "02:00"}'),
('two_factor_auth', 'Two-Factor Authentication', 'Xác thực 2 lớp', 'security', 0, NULL),
('api_rate_limit', 'API Rate Limiting', 'Giới hạn số request API', 'security', 1, '{"limit": 100, "window": 60}'),
('file_upload_limit', 'File Upload Limit', 'Giới hạn kích thước file upload', 'system', 1, '{"max_size": "10MB"}'),
('session_timeout', 'Session Timeout', 'Thời gian timeout session', 'security', 1, '{"timeout": 3600}')
ON DUPLICATE KEY UPDATE display_name=VALUES(display_name);

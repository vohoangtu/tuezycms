-- Add Configurations module to modules table

INSERT INTO `modules` (`name`, `display_name`, `description`, `category`, `is_enabled`, `is_system`, `config`) VALUES
('configuration_management', 'Configuration Management', 'Quản lý các cấu hình hệ thống có thể bật/tắt', 'system', 1, 0, '{"items_per_page": 20}')
ON DUPLICATE KEY UPDATE 
    display_name = VALUES(display_name),
    description = VALUES(description);

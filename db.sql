-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               8.4.3 - MySQL Community Server - GPL
-- Server OS:                    Win64
-- HeidiSQL Version:             12.8.0.6908
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

-- Dumping data for table tuzycms.articles: ~0 rows (approximately)

-- Dumping data for table tuzycms.article_types: ~3 rows (approximately)
INSERT INTO `article_types` (`id`, `locale`, `name`, `slug`, `description`, `is_active`, `created_at`, `updated_at`) VALUES
	(1, 'vi', 'Dịch Vụ', 'dich-vu', 'Các bài viết về dịch vụ', 1, '2025-12-24 06:31:39', '2025-12-24 06:31:39'),
	(2, 'vi', 'Tin Tức', 'tin-tuc', 'Các tin tức mới nhất', 1, '2025-12-24 06:31:39', '2025-12-24 06:31:39'),
	(3, 'vi', 'Kiến Thức', 'kien-thuc', 'Các bài viết kiến thức', 1, '2025-12-24 06:31:39', '2025-12-24 06:31:39');

-- Dumping data for table tuzycms.customers: ~0 rows (approximately)

-- Dumping data for table tuzycms.media_files: ~0 rows (approximately)

-- Dumping data for table tuzycms.media_usage: ~0 rows (approximately)

-- Dumping data for table tuzycms.modules: ~5 rows (approximately)
INSERT INTO `modules` (`id`, `name`, `description`, `is_enabled`, `settings`, `created_at`, `updated_at`) VALUES
	('articles', 'Quản lý bài viết', 'Module quản lý bài viết', 1, NULL, '2025-12-24 06:31:39', '2025-12-24 06:31:39'),
	('orders', 'Quản lý đơn hàng', 'Module quản lý đơn hàng', 1, NULL, '2025-12-24 06:31:39', '2025-12-24 06:31:39'),
	('products', 'Quản lý sản phẩm', 'Module quản lý sản phẩm', 1, NULL, '2025-12-24 06:31:39', '2025-12-24 06:31:39'),
	('promotions', 'Quản lý khuyến mãi', 'Module quản lý khuyến mãi', 1, NULL, '2025-12-24 06:31:39', '2025-12-24 06:31:39'),
	('seo', 'Công cụ SEO', 'Module hỗ trợ SEO', 1, NULL, '2025-12-24 06:31:39', '2025-12-24 06:31:39');

-- Dumping data for table tuzycms.orders: ~0 rows (approximately)

-- Dumping data for table tuzycms.order_items: ~0 rows (approximately)

-- Dumping data for table tuzycms.pages: ~2 rows (approximately)
INSERT INTO `pages` (`id`, `locale`, `slug`, `title`, `content`, `meta_title`, `meta_description`, `meta_keywords`, `is_active`, `created_at`, `updated_at`) VALUES
	(1, 'vi', 'lien-he', 'Liên hệ', 'Nội dung trang liên hệ', NULL, NULL, NULL, 1, '2025-12-24 06:32:07', '2025-12-24 06:32:07'),
	(2, 'en', 'contact', 'Contact', 'Contact page content', NULL, NULL, NULL, 1, '2025-12-24 06:32:07', '2025-12-24 06:32:07');

-- Dumping data for table tuzycms.permissions: ~0 rows (approximately)
INSERT INTO `permissions` (`id`, `name`, `display_name`, `description`, `resource`, `action`, `created_at`) VALUES
	(1, 'articles.view', 'View Articles', 'View articles list and details', 'articles', 'view', '2025-12-24 06:34:32'),
	(2, 'articles.create', 'Create Articles', 'Create new articles', 'articles', 'create', '2025-12-24 06:34:32'),
	(3, 'articles.edit', 'Edit Articles', 'Edit existing articles', 'articles', 'edit', '2025-12-24 06:34:32'),
	(4, 'articles.delete', 'Delete Articles', 'Delete articles', 'articles', 'delete', '2025-12-24 06:34:32'),
	(5, 'articles.publish', 'Publish Articles', 'Publish/unpublish articles', 'articles', 'publish', '2025-12-24 06:34:32'),
	(6, 'products.view', 'View Products', 'View products list and details', 'products', 'view', '2025-12-24 06:34:32'),
	(7, 'products.create', 'Create Products', 'Create new products', 'products', 'create', '2025-12-24 06:34:32'),
	(8, 'products.edit', 'Edit Products', 'Edit existing products', 'products', 'edit', '2025-12-24 06:34:32'),
	(9, 'products.delete', 'Delete Products', 'Delete products', 'products', 'delete', '2025-12-24 06:34:32'),
	(10, 'products.publish', 'Publish Products', 'Publish/unpublish products', 'products', 'publish', '2025-12-24 06:34:32'),
	(11, 'orders.view', 'View Orders', 'View orders list and details', 'orders', 'view', '2025-12-24 06:34:32'),
	(12, 'orders.create', 'Create Orders', 'Create new orders', 'orders', 'create', '2025-12-24 06:34:32'),
	(13, 'orders.edit', 'Edit Orders', 'Edit existing orders', 'orders', 'edit', '2025-12-24 06:34:32'),
	(14, 'orders.delete', 'Delete Orders', 'Delete orders', 'orders', 'delete', '2025-12-24 06:34:32'),
	(15, 'promotions.view', 'View Promotions', 'View promotions list and details', 'promotions', 'view', '2025-12-24 06:34:32'),
	(16, 'promotions.create', 'Create Promotions', 'Create new promotions', 'promotions', 'create', '2025-12-24 06:34:32'),
	(17, 'promotions.edit', 'Edit Promotions', 'Edit existing promotions', 'promotions', 'edit', '2025-12-24 06:34:32'),
	(18, 'promotions.delete', 'Delete Promotions', 'Delete promotions', 'promotions', 'delete', '2025-12-24 06:34:32'),
	(19, 'media.view', 'View Media', 'View media files', 'media', 'view', '2025-12-24 06:34:32'),
	(20, 'media.upload', 'Upload Media', 'Upload new media files', 'media', 'upload', '2025-12-24 06:34:32'),
	(21, 'media.delete', 'Delete Media', 'Delete media files', 'media', 'delete', '2025-12-24 06:34:32'),
	(22, 'settings.view', 'View Settings', 'View system settings', 'settings', 'view', '2025-12-24 06:34:32'),
	(23, 'settings.edit', 'Edit Settings', 'Edit system settings', 'settings', 'edit', '2025-12-24 06:34:32'),
	(24, 'users.view', 'View Users', 'View users list and details', 'users', 'view', '2025-12-24 06:34:32'),
	(25, 'users.create', 'Create Users', 'Create new users', 'users', 'create', '2025-12-24 06:34:32'),
	(26, 'users.edit', 'Edit Users', 'Edit existing users', 'users', 'edit', '2025-12-24 06:34:32'),
	(27, 'users.delete', 'Delete Users', 'Delete users', 'users', 'delete', '2025-12-24 06:34:32'),
	(28, 'roles.view', 'View Roles', 'View roles and permissions', 'roles', 'view', '2025-12-24 06:34:32'),
	(29, 'roles.create', 'Create Roles', 'Create new roles', 'roles', 'create', '2025-12-24 06:34:32'),
	(30, 'roles.edit', 'Edit Roles', 'Edit existing roles', 'roles', 'edit', '2025-12-24 06:34:32'),
	(31, 'roles.delete', 'Delete Roles', 'Delete roles', 'roles', 'delete', '2025-12-24 06:34:32');

-- Dumping data for table tuzycms.products: ~0 rows (approximately)

-- Dumping data for table tuzycms.product_categories: ~0 rows (approximately)

-- Dumping data for table tuzycms.promotions: ~0 rows (approximately)

-- Dumping data for table tuzycms.roles: ~0 rows (approximately)
INSERT INTO `roles` (`id`, `name`, `display_name`, `description`, `is_system`, `created_at`, `updated_at`) VALUES
	(1, 'super_admin', 'Super Admin', 'Full system access with all permissions', 1, '2025-12-24 06:34:32', '2025-12-24 06:34:32'),
	(2, 'admin', 'Admin', 'Administrative access to manage content and products', 1, '2025-12-24 06:34:32', '2025-12-24 06:34:32'),
	(3, 'editor', 'Editor', 'Can create and edit content', 1, '2025-12-24 06:34:32', '2025-12-24 06:34:32'),
	(4, 'viewer', 'Viewer', 'Read-only access to view content', 1, '2025-12-24 06:34:32', '2025-12-24 06:34:32');

-- Dumping data for table tuzycms.role_permissions: ~0 rows (approximately)
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES
	(1, 1),
	(2, 1),
	(3, 1),
	(4, 1),
	(1, 2),
	(2, 2),
	(3, 2),
	(1, 3),
	(2, 3),
	(3, 3),
	(1, 4),
	(2, 4),
	(1, 5),
	(2, 5),
	(1, 6),
	(2, 6),
	(3, 6),
	(4, 6),
	(1, 7),
	(2, 7),
	(3, 7),
	(1, 8),
	(2, 8),
	(3, 8),
	(1, 9),
	(2, 9),
	(1, 10),
	(2, 10),
	(1, 11),
	(2, 11),
	(4, 11),
	(1, 12),
	(2, 12),
	(1, 13),
	(2, 13),
	(1, 14),
	(2, 14),
	(1, 15),
	(2, 15),
	(4, 15),
	(1, 16),
	(2, 16),
	(1, 17),
	(2, 17),
	(1, 18),
	(2, 18),
	(1, 19),
	(2, 19),
	(3, 19),
	(4, 19),
	(1, 20),
	(2, 20),
	(3, 20),
	(1, 21),
	(2, 21),
	(1, 22),
	(2, 22),
	(4, 22),
	(1, 23),
	(2, 23),
	(1, 24),
	(4, 24),
	(1, 25),
	(1, 26),
	(1, 27),
	(1, 28),
	(4, 28),
	(1, 29),
	(1, 30),
	(1, 31);

-- Dumping data for table tuzycms.seo_settings: ~0 rows (approximately)

-- Dumping data for table tuzycms.shipping: ~0 rows (approximately)

-- Dumping data for table tuzycms.site_settings: ~3 rows (approximately)
INSERT INTO `site_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `description`, `created_at`, `updated_at`) VALUES
	(1, 'site_logo', NULL, 'image', 'Site Logo', '2025-12-24 06:32:00', '2025-12-24 06:32:00'),
	(2, 'site_banner', NULL, 'image', 'Site Banner', '2025-12-24 06:32:00', '2025-12-24 06:32:00'),
	(3, 'site_slider', '[]', 'json', 'Site Slider Images (JSON array)', '2025-12-24 06:32:00', '2025-12-24 06:32:00');

-- Dumping data for table tuzycms.users: ~1 rows (approximately)
INSERT INTO `users` (`id`, `email`, `password_hash`, `full_name`, `role`, `role_id`, `is_active`, `last_login_at`, `created_at`, `updated_at`) VALUES
	(1, 'admin@tuzycms.com', '$2y$10$.AnHQ7.OWib6/gU8PSlsC.Tap9GSlT1Fv9IyEpquOBcTFVO.Cf.IW', 'Administrator', 'admin', NULL, 1, '2025-12-24 13:27:29', '2025-12-24 06:31:52', '2025-12-24 05:32:41');

-- Dumping data for table tuzycms.user_roles: ~0 rows (approximately)
INSERT INTO `user_roles` (`user_id`, `role_id`) VALUES
	(1, 1);

-- Dumping data for table tuzycms.warehouse: ~0 rows (approximately)

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;

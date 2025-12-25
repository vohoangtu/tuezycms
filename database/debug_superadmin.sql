-- Kiểm tra Super Admin Setup
-- Chạy các query này để debug menu không hiển thị

-- 1. Kiểm tra role super_admin có tồn tại không
SELECT * FROM roles WHERE name = 'super_admin';

-- 2. Kiểm tra user hiện tại có role super_admin không
-- Thay 'your-email@example.com' bằng email của bạn
SELECT u.id, u.email, u.full_name, r.name as role_name
FROM users u
LEFT JOIN user_roles ur ON u.id = ur.user_id
LEFT JOIN roles r ON ur.role_id = r.id
WHERE u.email = 'admin@tuzycms.com';  -- Thay email của bạn vào đây

-- 3. Nếu chưa có role super_admin, gán role cho user
-- Uncomment và chạy nếu cần
-- INSERT INTO user_roles (user_id, role_id)
-- SELECT u.id, r.id
-- FROM users u
-- CROSS JOIN roles r
-- WHERE u.email = 'admin@tuzycms.com'  -- Thay email của bạn
-- AND r.name = 'super_admin'
-- ON DUPLICATE KEY UPDATE user_id=VALUES(user_id);

-- 4. Kiểm tra permissions đã được thêm chưa
SELECT * FROM permissions WHERE resource IN ('modules', 'settings');

-- 5. Kiểm tra super_admin có permissions không
SELECT r.name as role, p.name as permission
FROM roles r
JOIN role_permissions rp ON r.id = rp.role_id
JOIN permissions p ON rp.permission_id = p.id
WHERE r.name = 'super_admin' AND p.resource IN ('modules', 'settings');

# Hướng Dẫn Khắc Phục Menu Super Admin Không Hiển Thị

## Vấn Đề
Menu **Modules** và **Settings** không hiển thị mặc dù đang đăng nhập với tài khoản Super Admin.

## Nguyên Nhân Có Thể

1. ❌ **Migration chưa chạy** - Permissions chưa được thêm vào database
2. ❌ **User chưa có role super_admin** - Tài khoản chưa được gán role
3. ❌ **Session cache** - Cần logout và login lại

## Giải Pháp Nhanh (Khuyến Nghị)

### Cách 1: Sử Dụng Script Tự Động

Chạy file `setup_superadmin.bat` trong thư mục gốc dự án:

```bash
setup_superadmin.bat
```

Script này sẽ tự động:
- ✅ Chạy migration để thêm permissions
- ✅ Gán role super_admin cho user của bạn
- ✅ Hiển thị kết quả

Sau khi chạy xong:
1. **Logout** khỏi admin panel
2. **Login** lại
3. Menu Modules và Settings sẽ xuất hiện

### Cách 2: Thực Hiện Thủ Công

#### Bước 1: Chạy Migration

```bash
# Kết nối MySQL
mysql -u root -p tuzycms

# Chạy migration
source database/migrations/add_module_permissions.sql
```

#### Bước 2: Kiểm Tra Role Super Admin

```sql
-- Kiểm tra role super_admin có tồn tại
SELECT * FROM roles WHERE name = 'super_admin';
```

Nếu không có, chạy seed:
```bash
source database/seeds/seed_rbac.sql
```

#### Bước 3: Gán Role Cho User

```sql
-- Thay 'your-email@example.com' bằng email của bạn
INSERT INTO user_roles (user_id, role_id)
SELECT u.id, r.id
FROM users u
CROSS JOIN roles r
WHERE u.email = 'admin@tuzycms.com'  -- ⚠️ THAY EMAIL CỦA BẠN
AND r.name = 'super_admin'
ON DUPLICATE KEY UPDATE user_id=VALUES(user_id);
```

#### Bước 4: Kiểm Tra Kết Quả

```sql
-- Xác nhận user đã có role super_admin
SELECT u.email, r.name as role
FROM users u
JOIN user_roles ur ON u.id = ur.user_id
JOIN roles r ON ur.role_id = r.id
WHERE u.email = 'admin@tuzycms.com';  -- ⚠️ THAY EMAIL CỦA BẠN
```

Kết quả mong đợi:
```
+----------------------+-------------+
| email                | role        |
+----------------------+-------------+
| admin@tuzycms.com    | super_admin |
+----------------------+-------------+
```

#### Bước 5: Logout và Login Lại

1. Truy cập `/admin/logout`
2. Login lại với tài khoản vừa gán role
3. Kiểm tra menu sidebar

## Kiểm Tra Menu Hiển Thị

Sau khi login lại, bạn sẽ thấy:

### Menu Hiển Thị Cho Tất Cả Users:
- 📊 Dashboard
- 📝 Bài viết
- 🛍️ Sản phẩm
- 🛒 Đơn hàng
- 🎫 Khuyến mãi
- 🖼️ Media Library

### Menu Chỉ Cho Super Admin (có badge đỏ):
- 👥 Người dùng
- 🛡️ Vai trò & Quyền
- 🧩 **Modules** 🔴 Super Admin
- ⚙️ **Cài đặt** 🔴 Super Admin

## Troubleshooting

### Vấn đề 1: Script báo lỗi "mysql command not found"

**Giải pháp**: Thêm MySQL vào PATH hoặc dùng đường dẫn đầy đủ:
```bash
"C:\Program Files\MySQL\MySQL Server 8.0\bin\mysql.exe" -u root -p tuzycms
```

### Vấn đề 2: Menu vẫn không hiển thị sau khi gán role

**Nguyên nhân**: Session cache

**Giải pháp**:
1. Xóa cookies của browser
2. Hoặc dùng Incognito/Private mode
3. Login lại

### Vấn đề 3: Lỗi "Table 'permissions' doesn't exist"

**Nguyên nhân**: Chưa chạy migration RBAC

**Giải pháp**:
```bash
mysql -u root -p tuzycms < database/migrations/add_rbac_tables.sql
mysql -u root -p tuzycms < database/seeds/seed_rbac.sql
mysql -u root -p tuzycms < database/migrations/add_module_permissions.sql
```

## Xác Nhận Thành Công

Khi setup thành công, bạn sẽ thấy:

1. ✅ Menu "Modules" với badge đỏ "Super Admin"
2. ✅ Menu "Settings" với badge đỏ "Super Admin"
3. ✅ Có thể truy cập `/admin/modules` và `/admin/settings`
4. ✅ Các user khác (không phải Super Admin) KHÔNG thấy 2 menu này

## Liên Hệ Hỗ Trợ

Nếu vẫn gặp vấn đề, vui lòng cung cấp:
- Screenshot menu sidebar hiện tại
- Kết quả query kiểm tra role
- Log lỗi (nếu có)

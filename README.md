# TuzyCMS

Hệ thống CMS quản lý bài viết, sản phẩm, đơn hàng với kiến trúc DDD (Domain-Driven Design).

## Tính năng

- ✅ Quản lý bài viết với nhiều loại (Dịch Vụ, Tin Tức, Kiến Thức, ...)
- ✅ Quản lý sản phẩm với danh mục, giá cũ/mới/khuyến mãi
- ✅ Quản lý đơn hàng, thanh toán online, vận chuyển, kho bãi
- ✅ Quản lý khuyến mãi (theo phần trăm, đơn giá, sự kiện)
- ✅ Công cụ SEO
- ✅ Bật/tắt module dễ dàng
- ✅ Mã hóa nội dung trong database
- ✅ Bảo vệ tính toàn vẹn source code

## Yêu cầu

- PHP >= 8.4
- MySQL >= 5.7 hoặc MariaDB >= 10.3
- Composer
- Extensions: PDO, OpenSSL, JSON

## Cài đặt

1. Clone repository:
```bash
git clone <repository-url>
cd tuzycms
```

2. Cài đặt dependencies:
```bash
composer install
```

3. Cấu hình database:
```bash
# Windows
copy env.example .env

# Linux/Mac
cp env.example .env
# Chỉnh sửa .env với thông tin database của bạn
```

4. Tạo database:
```bash
mysql -u root -p < database/schema.sql
```

5. Tạo encryption key:
- Truy cập `/tools` trong trình duyệt
- Nhập mật khẩu (mặc định: `admin123`, có thể thay đổi trong `.env`)
- Click "Tạo Key Mới"
- **Lưu key ở nơi an toàn** - nếu mất key, dữ liệu mã hóa sẽ không thể đọc được

6. Cấu hình web server:
- Apache: Đảm bảo mod_rewrite được bật
- Nginx: Cấu hình rewrite rules

## Cấu trúc thư mục

```
tuzycms/
├── src/
│   ├── Domain/          # Domain layer (Entities, Value Objects)
│   ├── Application/     # Application layer (Services, Use Cases)
│   └── Infrastructure/  # Infrastructure layer (Database, Security)
├── public/
│   ├── admin/          # Admin panel
│   ├── tools/          # Tools page (key generation)
│   └── index.php       # Public site
├── database/           # Database schema
└── storage/           # Storage (keys, uploads)
```

## Bảo mật

### Mã hóa nội dung
- Nội dung chính (articles, products) được mã hóa khi lưu vào database
- Mỗi site có key riêng để giải mã
- Key được lưu trong `storage/keys/site.key`

### Bảo vệ source code
- Hệ thống kiểm tra tính toàn vẹn source code khi khởi động
- Nếu source code bị chỉnh sửa, hệ thống sẽ không hoạt động
- Hash của source code được lưu trong `storage/keys/source.hash`

## Sử dụng

### Admin Panel
Truy cập `/admin` để quản lý:
- Bài viết
- Sản phẩm
- Đơn hàng
- Khuyến mãi
- Cài đặt

### Public Site
Truy cập `/` để xem trang công khai

## License

Proprietary - All rights reserved


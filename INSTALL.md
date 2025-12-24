# Hướng dẫn cài đặt TuzyCMS

## Yêu cầu hệ thống

- PHP >= 8.4
- MySQL >= 5.7 hoặc MariaDB >= 10.3
- Composer
- Extensions PHP: PDO, OpenSSL, JSON

## Các bước cài đặt

### 1. Cài đặt dependencies

```bash
composer install
```

### 2. Cấu hình môi trường

Tạo file `.env` từ `env.example`:

**Windows:**
```bash
copy env.example .env
```

**Linux/Mac:**
```bash
cp env.example .env
```

Chỉnh sửa file `.env` với thông tin database của bạn:

```env
DB_HOST=localhost
DB_NAME=tuzycms
DB_USER=root
DB_PASS=your_password

TOOLS_PASSWORD=your_secure_password
```

### 3. Tạo database

Chạy file SQL để tạo database và các bảng:

```bash
mysql -u root -p < database/schema.sql
```

Hoặc import trực tiếp trong phpMyAdmin hoặc MySQL client.

### 4. Tạo thư mục storage

```bash
mkdir -p storage/keys
chmod 755 storage/keys
```

### 5. Tạo encryption key

**QUAN TRỌNG**: Bước này bắt buộc phải thực hiện trước khi sử dụng hệ thống.

1. Truy cập: `http://your-domain/tools`
2. Nhập mật khẩu (từ file `.env`, mặc định là `admin123`)
3. Click "Tạo Key Mới"
4. **LƯU KEY Ở NƠI AN TOÀN** - Nếu mất key, toàn bộ dữ liệu mã hóa sẽ không thể đọc được

### 6. Cấu hình web server

#### Apache

Đảm bảo mod_rewrite được bật:

```apache
<Directory /path/to/tuzycms>
    AllowOverride All
</Directory>
```

#### Nginx

```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```

### 7. Kiểm tra cài đặt

1. Truy cập trang chủ: `http://your-domain/`
2. Truy cập admin: `http://your-domain/admin`
3. Kiểm tra tools: `http://your-domain/tools`

## Bảo mật

### Encryption Key

- Key được lưu trong `storage/keys/site.key`
- File này có quyền 600 (chỉ owner đọc/ghi)
- **KHÔNG BAO GIỜ** commit file này lên git

### Source Code Integrity

- Hash của source code được lưu trong `storage/keys/source.hash`
- Hệ thống tự động kiểm tra khi khởi động
- Nếu source code bị chỉnh sửa, hệ thống sẽ không hoạt động

### Tools Password

- Đổi mật khẩu mặc định trong file `.env`
- Sử dụng mật khẩu mạnh

## Troubleshooting

### Lỗi "Encryption key file not found"

- Chạy lại bước 5 (Tạo encryption key)

### Lỗi "Source code integrity check failed"

- Đảm bảo file `storage/keys/source.hash` tồn tại
- Chạy lại validation trong tools page

### Lỗi kết nối database

- Kiểm tra thông tin trong file `.env`
- Đảm bảo database đã được tạo
- Kiểm tra quyền truy cập của user database

## Cấu trúc thư mục

```
tuzycms/
├── src/                    # Source code
│   ├── Domain/            # Domain layer
│   ├── Application/       # Application layer
│   └── Infrastructure/     # Infrastructure layer
├── public/                # Public files
│   ├── admin/             # Admin panel
│   ├── tools/             # Tools page
│   └── templates/         # Public templates
├── database/              # Database schema
├── storage/               # Storage (keys, uploads)
└── vendor/                # Composer dependencies
```

## Hỗ trợ

Nếu gặp vấn đề, vui lòng kiểm tra:
1. Logs của web server
2. Logs của PHP
3. Quyền truy cập file/folder
4. Cấu hình database


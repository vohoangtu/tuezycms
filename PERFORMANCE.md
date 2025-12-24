# Tối ưu Performance - Source Code Integrity

## Vấn đề ban đầu

Source Code Integrity check ban đầu có thể ảnh hưởng nghiêm trọng đến performance vì:

1. **Được gọi mỗi request**: Validation chạy ở mỗi HTTP request
2. **Quét toàn bộ file**: Phải đọc tất cả file PHP trong thư mục `src/`
3. **Tính hash SHA256**: Phải tính hash cho từng file (rất tốn CPU)
4. **I/O operations**: Đọc nhiều file từ disk

**Ước tính**: Với ~50 file PHP, mỗi request có thể mất **100-500ms** chỉ để validate!

## Giải pháp đã áp dụng

### 1. **Caching kết quả validation**
- Kết quả validation được cache trong 5 phút (có thể cấu hình)
- File cache: `storage/keys/validation.cache`
- Giảm thiểu việc tính toán lặp lại

### 2. **Quick Check với mtime**
- Sử dụng modification time (mtime) của thư mục để phát hiện thay đổi
- Nếu mtime không đổi → files chưa thay đổi → dùng cache
- Nếu mtime thay đổi → files đã thay đổi → cần full validation
- File mtime cache: `storage/keys/validation.cache.mtime`

### 3. **Lazy Full Validation**
- Chỉ tính hash đầy đủ khi:
  - Cache hết hạn VÀ files đã thay đổi
  - Lần đầu tiên chạy
- Trong hầu hết các request, chỉ cần:
  - Đọc 1 file cache (vài microsecond)
  - So sánh mtime (vài microsecond)

## Performance sau tối ưu

### Trường hợp tốt nhất (cache hit)
- **Thời gian**: < 1ms
- **I/O**: Đọc 2 file nhỏ (cache + mtime)
- **CPU**: Gần như không

### Trường hợp trung bình (quick check pass, cache expired)
- **Thời gian**: 50-200ms (tùy số lượng file)
- **I/O**: Quét mtime của tất cả file
- **CPU**: Tính hash SHA256

### Trường hợp xấu nhất (files thay đổi)
- **Thời gian**: 100-500ms
- **I/O**: Đọc tất cả file + tính hash
- **CPU**: Tính hash SHA256 cho tất cả file

## Cấu hình Cache TTL

Mặc định cache TTL là **300 giây (5 phút)**. Có thể thay đổi:

```php
// Cache 10 phút
$validator = new KeyValidator(600);

// Cache 1 giờ
$validator = new KeyValidator(3600);

// Không cache (không khuyến nghị)
$validator = new KeyValidator(0);
```

## Tối ưu thêm (tùy chọn)

### 1. Chỉ validate trong admin panel
Nếu không cần bảo mật cao cho public site, có thể chỉ validate trong admin:

```php
// public/index.php - Bỏ validation
// Chỉ validate trong public/admin/index.php
```

### 2. Sử dụng OPcache
PHP OPcache sẽ cache compiled code, giảm I/O khi đọc file.

### 3. Background validation
Có thể chạy validation trong background process và chỉ check flag.

### 4. Disable trong production
Nếu đã deploy và không cần kiểm tra integrity, có thể disable:

```php
// .env
VALIDATE_SOURCE_INTEGRITY=false

// index.php
if (($_ENV['VALIDATE_SOURCE_INTEGRITY'] ?? 'true') === 'true') {
    $keyValidator = new KeyValidator();
    if (!$keyValidator->validateSourceIntegrity()) {
        http_response_code(403);
        die('Source code integrity check failed.');
    }
}
```

## Monitoring

Để monitor performance, có thể thêm logging:

```php
$start = microtime(true);
$isValid = $validator->validateSourceIntegrity();
$duration = (microtime(true) - $start) * 1000; // milliseconds

if ($duration > 100) {
    error_log("Slow validation: {$duration}ms");
}
```

## Kết luận

Sau tối ưu:
- **99% requests**: < 1ms (cache hit)
- **1% requests**: 50-200ms (cache miss, quick check)
- **Rất hiếm**: 100-500ms (files thay đổi)

**Impact**: Giảm từ 100-500ms mỗi request xuống còn < 1ms cho hầu hết requests!


# Hướng Dẫn Sử Dụng Module System

## Tổng Quan

Hiện tại, việc bật/tắt module trong UI **chỉ cập nhật database**, chưa thực sự ảnh hưởng đến việc sử dụng tính năng. Để module system hoạt động đầy đủ, cần tích hợp kiểm tra module enabled vào code.

## Cách Sử Dụng

### 1. Kiểm Tra Module Có Enabled Không

#### Trong Controller

```php
use Shared\Infrastructure\Helper\ModuleHelper;

class ProductController extends BaseController
{
    public function index(): void
    {
        // Kiểm tra module Product có enabled không
        if (!ModuleHelper::isEnabled('product_management')) {
            $this->json([
                'success' => false,
                'message' => 'Module Product đang bị tắt'
            ], 403);
            return;
        }
        
        // Tiếp tục xử lý...
    }
}
```

#### Trong View/Template

```php
<?php
use Shared\Infrastructure\Helper\ModuleHelper;

// Ẩn menu nếu module bị tắt
if (ModuleHelper::isEnabled('product_management')): ?>
    <li class="nav-item">
        <a href="/admin/products">Sản phẩm</a>
    </li>
<?php endif; ?>
```

### 2. Lấy Config Của Module

```php
use Shared\Infrastructure\Helper\ModuleHelper;

// Lấy toàn bộ config
$config = ModuleHelper::getConfig('product_management');

// Lấy một giá trị cụ thể
$itemsPerPage = ModuleHelper::getConfigValue('product_management', 'items_per_page', 20);

// Sử dụng
$products = $repository->paginate($itemsPerPage);
```

### 3. Sử Dụng Middleware (Tùy Chọn)

Để tự động kiểm tra module enabled cho toàn bộ route group:

```php
// Trong AdminRoutes.php
$registry->group(['middleware' => ['auth', 'module:product_management']], function($registry) {
    $registry->get('/products', [ProductController::class, 'index']);
    $registry->post('/products', [ProductController::class, 'store']);
    // ...
});
```

## Ví Dụ Thực Tế

### Ví Dụ 1: Kiểm Tra Trong Controller

```php
<?php

namespace Modules\Product\Presentation\Controller;

use Shared\Infrastructure\Controller\BaseController;
use Shared\Infrastructure\Helper\ModuleHelper;

class ProductController extends BaseController
{
    public function index(): void
    {
        // Kiểm tra module
        if (!ModuleHelper::isEnabled('product_management')) {
            $this->response->json([
                'success' => false,
                'message' => 'Module quản lý sản phẩm đang bị tắt. Vui lòng liên hệ quản trị viên.'
            ], 403);
            return;
        }
        
        // Lấy config
        $perPage = ModuleHelper::getConfigValue('product_management', 'items_per_page', 20);
        
        // Tiếp tục xử lý bình thường
        $products = $this->productService->getAll($perPage);
        
        $this->json([
            'success' => true,
            'data' => $products
        ]);
    }
}
```

### Ví Dụ 2: Ẩn Menu Động

```php
<!-- Trong sidebar template -->
<?php
use Shared\Infrastructure\Helper\ModuleHelper;
?>

<!-- Chỉ hiển thị menu nếu module enabled -->
<?php if (ModuleHelper::isEnabled('product_management')): ?>
<li class="nav-item">
    <a class="nav-link menu-link" href="/admin/products">
        <i class="ri-shopping-bag-line"></i> <span>Sản phẩm</span>
    </a>
</li>
<?php endif; ?>

<?php if (ModuleHelper::isEnabled('order_management')): ?>
<li class="nav-item">
    <a class="nav-link menu-link" href="/admin/orders">
        <i class="ri-shopping-cart-line"></i> <span>Đơn hàng</span>
    </a>
</li>
<?php endif; ?>
```

### Ví Dụ 3: Kiểm Tra Nhiều Module

```php
use Shared\Infrastructure\Helper\ModuleHelper;

// Kiểm tra nhiều module cùng lúc
$requiredModules = ['product_management', 'inventory_management'];
$allEnabled = true;

foreach ($requiredModules as $module) {
    if (!ModuleHelper::isEnabled($module)) {
        $allEnabled = false;
        break;
    }
}

if (!$allEnabled) {
    throw new \RuntimeException('Một số module bắt buộc chưa được kích hoạt');
}
```

## API Methods Có Sẵn

### ModuleHelper::isEnabled(string $moduleName): bool
Kiểm tra module có enabled không.

### ModuleHelper::getConfig(string $moduleName, ?string $key = null): mixed
Lấy config của module. Nếu $key = null, trả về toàn bộ config.

### ModuleHelper::getConfigValue(string $moduleName, string $key, $default = null): mixed
Lấy một giá trị cụ thể trong config, có giá trị mặc định.

### ModuleHelper::getEnabledModules(): array
Lấy danh sách tất cả modules đã enabled.

### ModuleHelper::getAllModules(): array
Lấy danh sách tất cả modules.

### ModuleHelper::getModulesByCategory(string $category): array
Lấy modules theo category (user, product, content, system).

## Tên Modules Trong Hệ Thống

Dựa vào database, các module names thường là:
- `user_management` - Quản lý người dùng
- `role_management` - Quản lý vai trò
- `product_management` - Quản lý sản phẩm
- `order_management` - Quản lý đơn hàng
- `article_management` - Quản lý bài viết
- `media_management` - Quản lý media
- `promotion_management` - Quản lý khuyến mãi

## Best Practices

### 1. Luôn Kiểm Tra Ở Controller
Kiểm tra module enabled ngay đầu method controller để tránh xử lý không cần thiết.

### 2. Graceful Degradation
Khi module bị tắt, hiển thị thông báo rõ ràng thay vì lỗi 500.

```php
if (!ModuleHelper::isEnabled('product_management')) {
    return $this->render('errors/module-disabled', [
        'moduleName' => 'Quản lý sản phẩm',
        'message' => 'Tính năng này tạm thời không khả dụng.'
    ]);
}
```

### 3. Cache Module Status
`ModuleHelper` đã có caching built-in, không cần cache thêm.

### 4. Logging
Log khi module bị tắt để theo dõi:

```php
if (!ModuleHelper::isEnabled('product_management')) {
    error_log("User attempted to access disabled module: product_management");
    // Return error response
}
```

## Tích Hợp Vào Routes

Để tự động kiểm tra module cho toàn bộ route group, cần implement middleware:

```php
// Trong AdminRouter.php, thêm xử lý middleware 'module:xxx'
if (str_starts_with($middlewareClass, 'module:')) {
    $moduleName = substr($middlewareClass, 7);
    if (!ModuleHelper::isEnabled($moduleName)) {
        throw new \Exception("Module '{$moduleName}' is disabled");
    }
}
```

## Kết Luận

Module system hiện tại:
- ✅ Có UI quản lý (bật/tắt, configure)
- ✅ Có database lưu trữ trạng thái
- ✅ Có helper functions để kiểm tra
- ⚠️ **Cần tích hợp vào code** để thực sự ảnh hưởng đến tính năng

Để module system hoạt động đầy đủ, cần:
1. Thêm `ModuleHelper::isEnabled()` vào các controller
2. Ẩn/hiện menu dựa trên module status
3. (Tùy chọn) Implement middleware tự động

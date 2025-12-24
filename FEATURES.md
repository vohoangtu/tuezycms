# TuzyCMS - Tính năng đã xây dựng

## Tổng quan

Hệ thống CMS quản lý bài viết, sản phẩm, đơn hàng với kiến trúc DDD (Domain-Driven Design), sử dụng PHP 8.4 thuần.

## Các tính năng chính

### 1. Quản lý Bài viết ✅
- Tạo nhiều loại bài viết: Dịch Vụ, Tin Tức, Kiến Thức, ...
- Quản lý bài viết với trạng thái: draft, published, archived
- Hỗ trợ SEO: meta title, meta description, meta keywords
- Featured image cho bài viết
- Nội dung được mã hóa khi lưu vào database

**Files:**
- `src/Domain/Article/Article.php`
- `src/Domain/Article/ArticleType.php`
- `src/Application/Service/ArticleService.php`
- `src/Infrastructure/Repository/ArticleRepository.php`
- `public/admin/api/articles.php`

### 2. Quản lý Sản phẩm ✅
- Danh mục sản phẩm (có thể phân cấp)
- Quản lý sản phẩm với:
  - Giá cũ (old_price)
  - Giá mới (new_price)
  - Giá khuyến mãi (promotional_price)
- SKU, số lượng tồn kho
- Hình ảnh sản phẩm (featured image + gallery)
- SEO settings
- Nội dung mô tả được mã hóa

**Files:**
- `src/Domain/Product/Product.php`
- `src/Domain/Product/ProductCategory.php`
- `src/Application/Service/ProductService.php`
- `src/Infrastructure/Repository/ProductRepository.php`
- `public/admin/api/products.php`

### 3. Giỏ hàng và Đặt hàng ✅
- Giỏ hàng lưu trên localStorage
- Validate giỏ hàng trước khi đặt hàng
- Tạo đơn hàng với thông tin giao hàng và thanh toán
- Quản lý trạng thái đơn hàng: pending, processing, shipped, delivered, cancelled
- Quản lý trạng thái thanh toán: unpaid, paid, refunded
- Tự động cập nhật tồn kho khi đặt hàng

**Files:**
- `src/Application/Service/CartService.php`
- `src/Application/Service/OrderService.php`
- `src/Domain/Order/Order.php`
- `src/Domain/Order/OrderItem.php`
- `src/Domain/Order/OrderAddress.php`
- `public/admin/api/cart.php`
- `public/admin/api/orders.php`
- `public/templates/cart.php`
- `public/templates/checkout.php`

### 4. Thanh toán Online ✅
- Tích hợp VNPay Gateway
- Hỗ trợ nhiều phương thức thanh toán:
  - VNPay (online)
  - COD (thanh toán khi nhận hàng)
  - Chuyển khoản ngân hàng
- Payment callback handler
- Xác thực giao dịch

**Files:**
- `src/Infrastructure/Payment/PaymentGateway.php` (Interface)
- `src/Infrastructure/Payment/VNPayGateway.php`
- `public/payment/vnpay.php`
- `public/payment/callback.php`

### 5. Quản lý Khuyến mãi ✅
- Nhiều loại khuyến mãi:
  - **Percentage**: Giảm theo phần trăm
  - **Fixed**: Giảm theo đơn giá cố định
  - **Event**: Khuyến mãi theo sự kiện
- Áp dụng theo sản phẩm hoặc danh mục
- Giới hạn số lần sử dụng
- Giới hạn giá trị đơn hàng tối thiểu
- Giới hạn số tiền giảm tối đa
- Thời gian hiệu lực (start_date, end_date)

**Files:**
- `src/Domain/Promotion/Promotion.php`
- `src/Domain/Promotion/PromotionType.php`
- `src/Application/Service/PromotionService.php`
- `src/Infrastructure/Repository/PromotionRepository.php`
- `public/admin/api/promotions.php`

### 6. Quản lý Kho bãi ✅
- Ghi nhận nhập kho (in)
- Ghi nhận xuất kho (out)
- Điều chỉnh tồn kho (adjustment)
- Lịch sử giao dịch kho
- Tự động cập nhật tồn kho khi đặt hàng

**Files:**
- `src/Application/Service/WarehouseService.php`
- `src/Infrastructure/Repository/WarehouseRepository.php`

### 7. Quản lý Vận chuyển ✅
- Tracking number
- Carrier (đơn vị vận chuyển)
- Trạng thái vận chuyển: pending, in_transit, delivered, failed
- Thời gian giao hàng

**Database Schema:**
- Table `shipping` với các trường cần thiết

### 8. Công cụ SEO ✅
- Meta title, meta description, meta keywords
- Open Graph image
- Canonical URL
- Robots meta tag
- SEO settings theo từng trang/loại

**Files:**
- `src/Application/Service/SeoService.php`
- `src/Infrastructure/Repository/SeoRepository.php`

### 9. Module System ✅
- Bật/tắt modules dễ dàng
- Quản lý settings cho từng module
- Modules mặc định:
  - articles (Quản lý bài viết)
  - products (Quản lý sản phẩm)
  - orders (Quản lý đơn hàng)
  - promotions (Quản lý khuyến mãi)
  - seo (Công cụ SEO)

**Files:**
- `src/Domain/Module/Module.php`
- `src/Application/Service/ModuleService.php`
- `src/Infrastructure/Repository/ModuleRepository.php`
- `public/admin/api/modules.php`

### 10. Bảo mật và Mã hóa ✅
- **Mã hóa nội dung**: Nội dung chính (articles, products) được mã hóa bằng AES-256-GCM khi lưu vào database
- **Key riêng cho mỗi site**: Mỗi site có encryption key riêng, lưu trong `storage/keys/site.key`
- **Bảo vệ source code**: 
  - Kiểm tra tính toàn vẹn source code khi khởi động
  - Hash của source code được lưu trong `storage/keys/source.hash`
  - Nếu source code bị chỉnh sửa, hệ thống sẽ không hoạt động
  - Có caching để tối ưu performance

**Files:**
- `src/Infrastructure/Security/ContentEncryption.php`
- `src/Infrastructure/Security/KeyValidator.php`
- `public/tools/index.php` (Trang tạo key)

## Cấu trúc thư mục

```
tuzycms/
├── src/
│   ├── Domain/              # Domain layer (Entities, Value Objects)
│   │   ├── Article/
│   │   ├── Product/
│   │   ├── Order/
│   │   ├── Promotion/
│   │   └── Module/
│   ├── Application/         # Application layer (Services, Use Cases)
│   │   └── Service/
│   │       ├── ArticleService.php
│   │       ├── ProductService.php
│   │       ├── CartService.php
│   │       ├── OrderService.php
│   │       ├── PromotionService.php
│   │       ├── WarehouseService.php
│   │       ├── SeoService.php
│   │       └── ModuleService.php
│   └── Infrastructure/      # Infrastructure layer
│       ├── Repository/       # Data access
│       ├── Security/         # Encryption, Validation
│       ├── Payment/          # Payment gateways
│       └── Config/           # Configuration
├── public/
│   ├── admin/               # Admin panel
│   │   ├── api/             # API endpoints
│   │   └── pages/           # Admin pages
│   ├── templates/           # Public templates
│   │   ├── cart.php
│   │   ├── checkout.php
│   │   └── ...
│   └── payment/             # Payment handlers
│       ├── vnpay.php
│       └── callback.php
├── database/
│   └── schema.sql           # Database schema
└── storage/
    └── keys/                # Encryption keys, source hash
```

## API Endpoints

### Cart
- `POST /admin/api/cart.php` - Validate và tính toán giỏ hàng

### Orders
- `GET /admin/api/orders.php` - Lấy danh sách/chi tiết đơn hàng
- `POST /admin/api/orders.php` - Tạo/cập nhật đơn hàng

### Promotions
- `GET /admin/api/promotions.php` - Lấy danh sách/chi tiết khuyến mãi
- `POST /admin/api/promotions.php` - Tạo/cập nhật khuyến mãi
- `DELETE /admin/api/promotions.php` - Xóa khuyến mãi

### Products
- `GET /admin/api/products.php` - Lấy danh sách/chi tiết sản phẩm
- `POST /admin/api/products.php` - Tạo/cập nhật sản phẩm

### Articles
- `GET /admin/api/articles.php` - Lấy danh sách/chi tiết bài viết
- `POST /admin/api/articles.php` - Tạo/cập nhật bài viết

## Cài đặt và Sử dụng

### 1. Cài đặt dependencies
```bash
composer install
```

### 2. Cấu hình database
- Copy `env.example` thành `.env`
- Cập nhật thông tin database trong `.env`
- Chạy `database/schema.sql` để tạo database

### 3. Tạo encryption key
- Truy cập `/tools` trong trình duyệt
- Nhập mật khẩu (mặc định: `admin123`)
- Click "Tạo Key Mới"
- **Lưu key ở nơi an toàn** - nếu mất key, dữ liệu mã hóa sẽ không thể đọc được

### 4. Cấu hình Payment Gateway (VNPay)
Thêm vào file `.env`:
```
VNPAY_TMN_CODE=your_tmn_code
VNPAY_SECRET_KEY=your_secret_key
VNPAY_URL=https://sandbox.vnpayment.vn/paymentv2/vpcpay.html
VNPAY_RETURN_URL=http://your-domain/payment/callback
```

## Lưu ý quan trọng

1. **Encryption Key**: Mỗi site cần có key riêng. Key được tạo từ trang `/tools` và lưu trong `storage/keys/site.key`. Nếu mất key, toàn bộ dữ liệu mã hóa sẽ không thể đọc được.

2. **Source Code Integrity**: Hệ thống kiểm tra tính toàn vẹn source code khi khởi động. Nếu source code bị chỉnh sửa, cả admin và homepage sẽ không hoạt động.

3. **PHP Version**: Yêu cầu PHP >= 8.4

4. **Database**: Yêu cầu MySQL >= 5.7 hoặc MariaDB >= 10.3

## Tính năng nâng cao

- **Caching**: Source validation có caching để tối ưu performance
- **Transaction**: Đơn hàng được tạo trong transaction để đảm bảo tính nhất quán
- **Warehouse Tracking**: Tự động ghi nhận mọi thay đổi tồn kho
- **Promotion Validation**: Kiểm tra điều kiện khuyến mãi trước khi áp dụng

## Mở rộng

Hệ thống được thiết kế theo DDD, dễ dàng mở rộng:
- Thêm payment gateway mới: Implement interface `PaymentGateway`
- Thêm loại khuyến mãi mới: Thêm case vào `PromotionType` enum
- Thêm module mới: Thêm vào database và sử dụng `ModuleService`


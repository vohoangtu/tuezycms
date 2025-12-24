# Hệ thống Routing và Đa ngôn ngữ

## URL Structure

### Tiếng Việt (mặc định)
- `domain.com` → Trang chủ
- `domain.com/lien-he` → Trang liên hệ
- `domain.com/tin-tuc` → Danh sách tin tức
- `domain.com/dich-vu` → Danh sách dịch vụ
- `domain.com/kien-thuc` → Danh sách kiến thức
- `domain.com/san-pham` → Danh sách sản phẩm
- `domain.com/san-pham-tivi-2k` → Chi tiết sản phẩm có slug "san-pham-tivi-2k" (tên: "Sản phẩm ti vi 2k")
- `domain.com/tin-tuc/bai-viet-slug` → Chi tiết bài viết
- `domain.com/dich-vu/dich-vu-slug` → Chi tiết dịch vụ

### Tiếng Anh
- `domain.com/en` → Trang chủ (English)
- `domain.com/en/contact` → Trang liên hệ (English)
- `domain.com/en/news` → Danh sách tin tức (English)
- `domain.com/en/services` → Danh sách dịch vụ (English)
- `domain.com/en/products` → Danh sách sản phẩm (English)
- `domain.com/en/products-tv-2k` → Chi tiết sản phẩm (English)

## Routing Rules

### Exact Routes
- `''` → Homepage
- `'lien-he'` / `'contact'` → Contact page
- `'tin-tuc'` / `'news'` → News listing
- `'dich-vu'` / `'services'` → Services listing
- `'kien-thuc'` / `'knowledge'` → Knowledge listing
- `'san-pham'` / `'products'` → Products listing

### Dynamic Routes
- `{type-slug}/{article-slug}` → Article detail với type prefix
  - Ví dụ: `tin-tuc/bai-viet-1`, `dich-vu/dich-vu-abc`
- `{slug}` → Content detail (Product hoặc Article)
  - Ưu tiên: Tìm Product trước, nếu không có thì tìm Article
  - Ví dụ: `san-pham-tivi-2k` (có thể là product hoặc article)
  - **Lưu ý**: Slug của Product và Article có thể trùng nhau, hệ thống sẽ ưu tiên Product

## Đa ngôn ngữ

### Locale Detection
- Tự động detect từ URL prefix (`/en/`, `/vi/`)
- Mặc định: `vi` (Tiếng Việt)
- Hỗ trợ: `vi`, `en`

### Translation
- Sử dụng `Translator` class để dịch text
- File translation: `storage/translations/{locale}.php`
- Default translations được định nghĩa trong code

### URL Generation
```php
$router->url('san-pham', 'vi'); // /san-pham
$router->url('san-pham', 'en'); // /en/products
$router->url('lien-he', 'vi');  // /lien-he
$router->url('lien-he', 'en');  // /en/contact
$router->url('san-pham-tivi-2k', 'vi'); // /san-pham-tivi-2k (product slug)
$router->url('bai-viet-abc', 'vi'); // /bai-viet-abc (article hoặc product)
```

## Database Schema

### Locale Support
Tất cả các bảng đã được thêm cột `locale`:
- `articles.locale`
- `products.locale`
- `product_categories.locale`
- `article_types.locale`
- `pages.locale`

### Migration
Chạy migration để thêm locale support:
```bash
mysql -u root -p < database/migrations/add_multilang.sql
```

## Sử dụng trong Template

```php
// Get current locale
$locale = $router->getLocale(); // 'vi' or 'en'

// Generate URL
$url = $router->url('san-pham', 'vi'); // /san-pham
$url = $router->url('san-pham', 'en'); // /en/products

// Translate
$text = $translator->trans('products'); // 'Sản phẩm' or 'Products'

// Switch language link
$switchUrl = $router->getLocale() === 'vi' 
    ? $router->url('current-path', 'en') 
    : $router->url('current-path', 'vi');
```

## Thêm Route Mới

1. Thêm vào `Router::match()` method:
```php
'exact-route' => ['type' => 'page', 'handler' => 'handler_name'],
```

2. Thêm handler vào `public/index.php`:
```php
'handler_name' => (function() use (...) {
    // Handler logic
})(),
```

3. Tạo template nếu cần: `public/templates/{handler_name}.php`


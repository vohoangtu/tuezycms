# Kiến trúc MVC cho TuzyCMS

## Tổng quan

Hệ thống đã được refactor để áp dụng **MVC (Model-View-Controller)** pattern kết hợp với **DDD (Domain-Driven Design)**.

## Cấu trúc

```
src/
├── Domain/                    # Domain Layer (Entities, Value Objects)
│   ├── User/
│   ├── Article/
│   ├── Product/
│   └── ...
├── Application/               # Application Layer (Services, Use Cases)
│   └── Service/
│       ├── AuthService.php
│       ├── ArticleService.php
│       └── ...
├── Infrastructure/            # Infrastructure Layer
│   ├── Repository/            # Data Access (Model trong MVC)
│   ├── Middleware/            # Middleware (Auth, etc.)
│   ├── Routing/               # Routing
│   └── Security/
└── Presentation/              # Presentation Layer (MVC)
    ├── Controller/            # Controllers
    │   ├── BaseController.php
    │   ├── AuthController.php
    │   ├── AdminController.php
    │   ├── ProductController.php
    │   ├── ArticleController.php
    │   ├── OrderController.php
    │   └── PromotionController.php
    └── View/                   # Views
        └── admin/
            ├── auth/
            │   └── login.php
            └── dashboard.php
```

## Authentication System

### 1. User Domain
- **File**: `src/Domain/User/User.php`
- Entity đại diện cho user trong hệ thống
- Có các role: `admin`, `editor`, `viewer`

### 2. AuthService
- **File**: `src/Application/Service/AuthService.php`
- Xử lý authentication logic:
  - `authenticate()` - Xác thực email/password
  - `login()` - Đăng nhập (set session)
  - `logout()` - Đăng xuất
  - `getCurrentUser()` - Lấy user hiện tại
  - `isAuthenticated()` - Kiểm tra đã đăng nhập
  - `requireAuth()` - Yêu cầu authentication

### 3. AuthMiddleware
- **File**: `src/Infrastructure/Middleware/AuthMiddleware.php`
- Bảo vệ routes:
  - `requireAuth()` - Yêu cầu đăng nhập
  - `requireAdmin()` - Yêu cầu quyền admin
  - `redirectIfAuthenticated()` - Redirect nếu đã đăng nhập

## Controllers

### BaseController
- **File**: `src/Presentation/Controller/BaseController.php`
- Base class cho tất cả controllers
- Cung cấp:
  - `render()` - Render view
  - `json()` - Return JSON response
  - `redirect()` - Redirect
  - Source integrity validation

### AuthController
- **File**: `src/Presentation/Controller/AuthController.php`
- Xử lý authentication:
  - `login()` - Hiển thị/login form
  - `logout()` - Đăng xuất

### AdminController
- **File**: `src/Presentation/Controller/AdminController.php`
- Xử lý admin dashboard
- Yêu cầu authentication

### ProductController, ArticleController, OrderController, PromotionController
- Xử lý CRUD operations cho các resources
- Tất cả đều yêu cầu authentication
- Trả về JSON cho API calls

## Views

### Layout
- **File**: `public/admin/layout.php`
- Layout chung cho admin panel
- Hiển thị sidebar, menu, user info

### Login View
- **File**: `src/Presentation/View/admin/auth/login.php`
- Form đăng nhập

### Dashboard View
- **File**: `src/Presentation/View/admin/dashboard.php`
- Trang dashboard

## Routing

### AdminRouter
- **File**: `src/Infrastructure/Routing/AdminRouter.php`
- Route admin requests:
  - `/admin/login` → AuthController::login()
  - `/admin/logout` → AuthController::logout()
  - `/admin/api/*` → API controllers
  - `/admin/*` → Admin pages (require auth)

## Database

### Users Table
- **Migration**: `database/migrations/add_users_table.sql`
- Lưu thông tin users:
  - email, password_hash, full_name
  - role (admin, editor, viewer)
  - is_active, last_login_at

### Default Admin User
- Email: `admin@tuzycms.com`
- Password: `admin123` (đổi ngay sau khi đăng nhập lần đầu!)

## Bảo vệ Admin Routes

Tất cả admin routes (trừ `/admin/login`) đều được bảo vệ bởi `AuthMiddleware`:

```php
// Trong mỗi Controller
public function __construct()
{
    parent::__construct();
    $this->authMiddleware = new AuthMiddleware();
    $this->authMiddleware->requireAuth(); // Yêu cầu đăng nhập
}
```

## Flow Authentication

1. User truy cập `/admin` → Redirect đến `/admin/login` nếu chưa đăng nhập
2. User nhập email/password → `AuthController::login()`
3. `AuthService::authenticate()` kiểm tra credentials
4. Nếu đúng → `AuthService::login()` set session
5. Redirect đến trang đã yêu cầu hoặc dashboard
6. Các request tiếp theo kiểm tra session

## Session Management

- Session được start tự động trong `AdminRouter`
- Session lưu:
  - `user_id` - ID của user
  - `user_email` - Email của user
  - `user_role` - Role của user
- Session được destroy khi logout

## API Endpoints (Protected)

Tất cả API endpoints trong `/admin/api/*` đều yêu cầu authentication:

- `/admin/api/products` → ProductController
- `/admin/api/articles` → ArticleController
- `/admin/api/orders` → OrderController
- `/admin/api/promotions` → PromotionController
- `/admin/api/cart` → CartController (public, không cần auth)

## Cách sử dụng

### 1. Tạo user mới (qua code)
```php
$authService = new AuthService();
$user = $authService->createUser(
    'admin@example.com',
    'password123',
    'Admin User',
    'admin'
);
```

### 2. Đăng nhập
```php
$authService = new AuthService();
$user = $authService->authenticate('admin@example.com', 'password123');
if ($user) {
    $authService->login($user);
}
```

### 3. Kiểm tra authentication trong code
```php
$authService = new AuthService();
if ($authService->isAuthenticated()) {
    $user = $authService->getCurrentUser();
    // Do something
}
```

### 4. Bảo vệ route
```php
$authMiddleware = new AuthMiddleware();
$authMiddleware->requireAuth(); // Redirect nếu chưa đăng nhập
$authMiddleware->requireAdmin(); // Yêu cầu quyền admin
```

## Lưu ý

1. **Đổi mật khẩu mặc định**: Đổi ngay sau khi đăng nhập lần đầu
2. **Session security**: Đảm bảo session được bảo mật (HTTPS trong production)
3. **Password hashing**: Sử dụng `password_hash()` với PASSWORD_DEFAULT
4. **Source integrity**: Vẫn được kiểm tra trong mỗi request


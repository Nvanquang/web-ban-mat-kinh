# 00 — Project Context

> File này giúp AI hiểu toàn bộ bức tranh của project trước khi sinh code.  
> Đọc file này **trước tiên** trước bất kỳ file nào khác.

---

## 1. Mục tiêu dự án

**Tên project:** EyeGlass Shop — Cửa hàng kính mắt online  
**Loại ứng dụng:** Web app thương mại điện tử thuần PHP, render phía server (SSR)  
**Đối tượng người dùng:**
- **Customer:** Duyệt sản phẩm, đặt hàng, theo dõi đơn, gửi yêu cầu tư vấn
- **Admin:** Quản lý sản phẩm, danh mục, đơn hàng, khách hàng, tư vấn

**Tính năng cốt lõi:**
| Module | Mô tả |
|---|---|
| Auth | Đăng ký, đăng nhập, đăng xuất, phân quyền |
| Products | CRUD sản phẩm, danh mục, tìm kiếm, lọc, phân trang |
| Cart | Thêm/xóa/sửa số lượng, lưu trong session |
| Orders | Đặt hàng, xem lịch sử, admin cập nhật trạng thái |
| Consultations | Khách gửi câu hỏi, admin phản hồi |
| Admin panel | Dashboard, quản lý toàn bộ dữ liệu |

---

## 2. Tech Stack

```
Backend  : PHP 8.1+ (thuần, không dùng framework)
Database : MySQL 8.0+ (PDO, prepared statements)
Frontend : HTML5, CSS3, Bootstrap 5, JavaScript thuần
Template : PHP include/require (không dùng Twig, Blade)
Session  : PHP native session ($_SESSION)
Upload   : PHP move_uploaded_file()
Server   : Apache / Nginx + PHP-FPM
```

> ❌ Không dùng Composer, không dùng framework (Laravel, Symfony, CodeIgniter)  
> ❌ Không dùng REST API / JSON response (trừ AJAX nhỏ trong admin)  
> ✅ Mọi request đều trả về HTML (SSR thuần)

---

## 3. Cấu trúc thư mục

```
eyeglass/
├── index.php                  # Entry point duy nhất (Front Controller)
├── .htaccess                  # Rewrite tất cả về index.php
│
├── app/
│   ├── core/
│   │   ├── Database.php       # PDO singleton
│   │   ├── Router.php         # Parse URL → dispatch controller
│   │   ├── Controller.php     # Base controller (render, redirect, auth check)
│   │   ├── Model.php          # Base model (db, find, findAll)
│   │   └── Session.php        # Helper session (flash message, auth)
│   │
│   ├── controllers/
│   │   ├── HomeController.php
│   │   ├── AuthController.php
│   │   ├── ProductController.php
│   │   ├── CartController.php
│   │   ├── OrderController.php
│   │   ├── ConsultationController.php
│   │   └── admin/
│   │       ├── AdminDashboardController.php
│   │       ├── AdminProductController.php
│   │       ├── AdminOrderController.php
│   │       ├── AdminCustomerController.php
│   │       └── AdminConsultationController.php
│   │
│   ├── models/
│   │   ├── CustomerModel.php
│   │   ├── ProductModel.php
│   │   ├── GlassesCategoryModel.php
│   │   ├── OrderModel.php
│   │   ├── OrderDetailModel.php
│   │   └── ConsultationModel.php
│   │
│   └── views/
│       ├── layouts/
│       │   ├── main.php       # Layout chính cho khách hàng
│       │   └── admin.php      # Layout cho admin panel
│       ├── home/
│       ├── auth/
│       ├── products/
│       ├── cart/
│       ├── orders/
│       ├── consultations/
│       └── admin/
│           ├── dashboard/
│           ├── products/
│           ├── orders/
│           ├── customers/
│           └── consultations/
│
├── public/
│   ├── css/
│   ├── js/
│   └── uploads/               # Ảnh sản phẩm upload
│
└── config/
    └── config.php             # DB credentials, base URL, constants
```

---

## 4. Routing

**Cơ chế:** URL đẹp qua `.htaccess` → tất cả về `index.php` → `Router` parse.

### .htaccess
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

### URL Pattern
```
/                                 → HomeController@index
/products                         → ProductController@index
/products/{id}                    → ProductController@show
/auth/login                       → AuthController@login        (GET: form, POST: xử lý)
/auth/register                    → AuthController@register
/auth/logout                      → AuthController@logout
/cart                             → CartController@index
/cart/add                         → CartController@add          (POST)
/orders                           → OrderController@index
/orders/checkout                  → OrderController@checkout
/consultations                    → ConsultationController@index
/admin                            → AdminDashboardController@index
/admin/products                   → AdminProductController@index
/admin/products/create            → AdminProductController@create
/admin/products/{id}/edit         → AdminProductController@edit
/admin/orders                     → AdminOrderController@index
/admin/customers                  → AdminCustomerController@index
/admin/consultations              → AdminConsultationController@index
```

---

## 5. Session

Dùng **PHP native session** — không dùng database session, không dùng JWT.

```php
// Cấu trúc $_SESSION chuẩn của project
$_SESSION['user'] = [
    'id'        => 1,
    'username'  => 'admin',
    'full_name' => 'Admin User',
    'role'      => 'admin',    // 'admin' | 'customer'
    'status'    => 'active'
];

// Giỏ hàng lưu trong session
$_SESSION['cart'] = [
    // product_id => ['id', 'product_name', 'price', 'quantity', 'image_url']
    5 => [
        'id'           => 5,
        'product_name' => 'Rayban Classic',
        'price'        => 1200000,
        'quantity'     => 2,
        'image_url'    => 'products/rayban.jpg'
    ],
];

// Flash message (hiển thị 1 lần rồi xóa)
$_SESSION['flash'] = [
    'type'    => 'success',  // 'success' | 'error' | 'warning' | 'info'
    'message' => 'Order placed successfully!'
];
```

**Quy tắc session:**
- `session_start()` chỉ gọi **một lần** trong `index.php`
- Helper `Session::get()`, `Session::set()`, `Session::flash()` để truy cập
- Kiểm tra auth trong `Controller::requireAuth()` và `Controller::requireAdmin()`

---

## 6. Layout Render

Project dùng **PHP include** để ghép layout. Không dùng template engine.

```php
// Controller gọi:
$this->render('products/index', [
    'products' => $products,
    'title'    => 'Product List'
]);

// Base Controller::render() làm:
// 1. Extract $data vào biến PHP
// 2. ob_start() để capture output
// 3. include view file
// 4. Lấy $content = ob_get_clean()
// 5. include layout file (layouts/main.php hoặc layouts/admin.php)
// Layout dùng <?= $content ?> để in ra
```

**Hai layout chính:**
- `layouts/main.php` → dành cho trang khách hàng (header, nav, footer)
- `layouts/admin.php` → dành cho admin panel (sidebar, topbar)

---

## 7. Config

```php
// config/config.php
define('DB_HOST',    'localhost');
define('DB_NAME',    'eyeglass_db');
define('DB_USER',    'root');
define('DB_PASS',    '');
define('DB_CHARSET', 'utf8mb4');

define('BASE_URL',    'http://localhost/eyeglass');
define('UPLOAD_PATH', __DIR__ . '/../public/uploads/');
define('UPLOAD_URL',  BASE_URL . '/public/uploads/');

define('APP_NAME',    'EyeGlass Shop');
define('APP_VERSION', '1.0.0');
```

---

## 8. Quy ước đặt tên

| Thành phần | Convention | Ví dụ |
|---|---|---|
| Controller class | PascalCase + Controller | `ProductController` |
| Model class | PascalCase + Model | `ProductModel` |
| View file | snake_case.php | `product_detail.php` |
| Method trong controller | camelCase | `showDetail()` |
| Biến PHP | snake_case | `$product_list` |
| Table DB | snake_case tiếng Anh | `order_details` |
| Column DB | snake_case tiếng Anh | `stock_quantity` |
| URL segment | kebab-case | `/admin/products` |

---

## 9. Luồng xử lý tổng quát

```
1. Trình duyệt gửi request HTTP
2. .htaccess rewrite → index.php
3. index.php: require config, autoload, session_start()
4. Router::dispatch() → parse URL → khởi tạo Controller
5. Controller gọi Model (nếu cần)
6. Model query DB qua PDO → trả về array/object
7. Controller truyền data → render View
8. View (PHP template) tạo HTML
9. Layout bọc ngoài → gửi HTML về trình duyệt
```

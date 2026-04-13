# 03 — Render Flow (SSR Thuần PHP)

> Project này render **100% phía server (SSR)**.  
> Không có SPA, không có API JSON (trừ AJAX nhỏ trong admin).  
> AI phải theo flow này cho mọi trang.

---

## 1. Flow tổng quát

```
Browser
  │
  │  HTTP Request (GET /products?category=1&page=2)
  ▼
.htaccess
  │  RewriteRule → index.php (nếu không phải file tĩnh)
  ▼
index.php  ← Entry point DUY NHẤT
  │  1. require config/config.php
  │  2. spl_autoload_register()
  │  3. session_start()
  │  4. Router::dispatch()
  ▼
Router
  │  Parse REQUEST_URI
  │  Map → Controller class + method
  ▼
Controller (VD: ProductController::index())
  │  1. Validate input (GET params)
  │  2. new ProductModel()
  │  3. $products = $model->getByCategory(...)
  ▼
Model (ProductModel::getByCategory())
  │  1. Build SQL với PDO prepared statement
  │  2. Execute query
  │  3. return array
  ▼
Controller (tiếp tục)
  │  $this->render('products/index', ['products' => $products])
  ▼
Base Controller::render()
  │  1. extract($data) → biến PHP
  │  2. ob_start()
  │  3. include "app/views/products/index.php"
  │  4. $content = ob_get_clean()
  │  5. include "app/views/layouts/main.php"
  ▼
Layout (main.php)
  │  Header, Nav, <?= $content ?>, Footer
  ▼
HTML hoàn chỉnh
  │
  ▼
Browser (render HTML)
```

---

## 2. index.php — Entry Point

```php
<?php
// index.php — KHÔNG thêm gì khác vào đây

define('ROOT_PATH', __DIR__);
require_once ROOT_PATH . '/config/config.php';

// Autoload tất cả class trong app/
spl_autoload_register(function ($class) {
    $paths = [
        ROOT_PATH . '/app/core/',
        ROOT_PATH . '/app/controllers/',
        ROOT_PATH . '/app/controllers/admin/',
        ROOT_PATH . '/app/models/',
    ];
    foreach ($paths as $path) {
        $file = $path . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// Helpers (function, không phải class)
require_once ROOT_PATH . '/app/helpers/format.php';
require_once ROOT_PATH . '/app/helpers/validate.php';

// Session một lần duy nhất
session_start();

// Dispatch
Router::dispatch();
```

---

## 3. Router::dispatch()

```php
// app/core/Router.php
class Router {
    public static function dispatch(): void {
        $uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $uri    = rtrim(str_replace(BASE_PATH, '', $uri), '/') ?: '/';
        $method = $_SERVER['REQUEST_METHOD']; // GET | POST

        $routes = [
            'GET' => [
                '/'                               => ['HomeController',               'index'],
                '/products'                       => ['ProductController',             'index'],
                '/products/{id}'                  => ['ProductController',             'show'],
                '/auth/login'                     => ['AuthController',                'loginForm'],
                '/auth/register'                  => ['AuthController',                'registerForm'],
                '/auth/logout'                    => ['AuthController',                'logout'],
                '/cart'                           => ['CartController',                'index'],
                '/orders'                         => ['OrderController',               'index'],
                '/orders/{id}'                    => ['OrderController',               'show'],
                '/orders/checkout'                => ['OrderController',               'checkoutForm'],
                '/consultations'                  => ['ConsultationController',        'index'],
                '/admin'                          => ['AdminDashboardController',      'index'],
                '/admin/products'                 => ['AdminProductController',        'index'],
                '/admin/products/create'          => ['AdminProductController',        'createForm'],
                '/admin/products/{id}/edit'       => ['AdminProductController',        'editForm'],
                '/admin/orders'                   => ['AdminOrderController',          'index'],
                '/admin/orders/{id}'              => ['AdminOrderController',          'show'],
                '/admin/customers'                => ['AdminCustomerController',       'index'],
                '/admin/consultations'            => ['AdminConsultationController',   'index'],
                '/admin/consultations/{id}'       => ['AdminConsultationController',   'show'],
            ],
            'POST' => [
                '/auth/login'                     => ['AuthController',                'login'],
                '/auth/register'                  => ['AuthController',                'register'],
                '/cart/add'                       => ['CartController',                'add'],
                '/cart/update'                    => ['CartController',                'update'],
                '/cart/remove'                    => ['CartController',                'remove'],
                '/orders/checkout'                => ['OrderController',               'checkout'],
                '/consultations/send'             => ['ConsultationController',        'send'],
                '/admin/products/create'          => ['AdminProductController',        'create'],
                '/admin/products/{id}/edit'       => ['AdminProductController',        'update'],
                '/admin/products/{id}/delete'     => ['AdminProductController',        'delete'],
                '/admin/orders/{id}/status'       => ['AdminOrderController',          'updateStatus'],
                '/admin/customers/{id}/ban'       => ['AdminCustomerController',       'ban'],
                '/admin/consultations/{id}/reply' => ['AdminConsultationController',   'reply'],
            ],
        ];

        // Match route → dispatch (logic match với dynamic segments {id})
    }
}
```

---

## 4. Base Controller::render()

```php
// app/core/Controller.php
protected function render(string $view, array $data = [], string $layout = 'main'): void {
    // Merge với data chung
    $data['currentUser'] = Session::getUser();
    $data['flash']       = Session::getFlash();
    $data['title']       = $data['title'] ?? APP_NAME;

    extract($data);

    // Capture view output
    ob_start();
    $viewFile = ROOT_PATH . '/app/views/' . str_replace('.', '/', $view) . '.php';
    if (!file_exists($viewFile)) {
        throw new RuntimeException("View not found: $view");
    }
    include $viewFile;
    $content = ob_get_clean();

    // Render layout
    $layoutFile = ROOT_PATH . '/app/views/layouts/' . $layout . '.php';
    include $layoutFile;
}

protected function redirect(string $path, string $type = '', string $message = ''): void {
    if ($type && $message) {
        Session::setFlash($type, $message);
    }
    header('Location: ' . BASE_URL . $path);
    exit;
}
```

---

## 5. Layout Structure

### layouts/main.php (Frontend)
```php
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?> — <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/style.css">
</head>
<body>
    <?php include ROOT_PATH . '/app/views/partials/navbar.php'; ?>

    <?php if ($flash): ?>
    <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : $flash['type'] ?> alert-dismissible">
        <?= htmlspecialchars($flash['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <main>
        <?= $content ?>
    </main>

    <?php include ROOT_PATH . '/app/views/partials/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= BASE_URL ?>/public/js/main.js"></script>
</body>
</html>
```

### layouts/admin.php (Backend)
```php
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($title) ?> — Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/admin.css">
</head>
<body class="admin-layout">
    <?php include ROOT_PATH . '/app/views/admin/partials/topbar.php'; ?>
    <div class="d-flex">
        <?php include ROOT_PATH . '/app/views/admin/partials/sidebar.php'; ?>
        <main class="flex-grow-1 p-4">
            <?php if ($flash): ?>
            <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : $flash['type'] ?> alert-dismissible">
                <?= htmlspecialchars($flash['message']) ?>
            </div>
            <?php endif; ?>
            <?= $content ?>
        </main>
    </div>
</body>
</html>
```

---

## 6. Quy tắc SSR — KHÔNG được vi phạm

```
✅ Mọi trang đều render HTML hoàn chỉnh từ server
✅ Link dùng <a href="<?= BASE_URL ?>/products">
✅ Form submit dùng method="POST" action="<?= BASE_URL ?>/..."
✅ Redirect sau POST để tránh form resubmit (PRG Pattern)
✅ Flash message set trước redirect, đọc sau redirect

❌ Không fetch() / axios để load nội dung trang chính
❌ Không trả về JSON cho page navigation
❌ Không dùng history.pushState() để fake route
❌ Không render nội dung trang bằng innerHTML =

⚠️  AJAX được phép CHỈ trong các trường hợp sau:
    - Kiểm tra username/email đã tồn tại (live validation)
    - Cập nhật số lượng giỏ hàng (mini cart badge)
    - Admin: cập nhật status đơn hàng không cần reload toàn trang
    - Tất cả AJAX response phải trả về JSON: { success, message, data }
```

---

## 7. Xử lý HTTP Status Code

```php
// 404
http_response_code(404);
$this->render('errors/404', ['title' => 'Page Not Found']);

// 403 Forbidden
http_response_code(403);
$this->render('errors/403', ['title' => 'Access Denied']);

// 500
error_log($e->getMessage());
http_response_code(500);
$this->render('errors/500', ['title' => 'Server Error']);
```

---

## 8. Biến luôn có sẵn trong mọi View

| Biến | Nguồn | Mô tả |
|---|---|---|
| `$title` | controller | Tiêu đề trang |
| `$currentUser` | session | Array thông tin user hoặc `null` |
| `$flash` | session | Array `[type, message]` hoặc `null` |
| `$content` | ob_get_clean | Chỉ có trong layout |

> Ngoài ra controller có thể truyền thêm bất kỳ biến nào qua `$data`.

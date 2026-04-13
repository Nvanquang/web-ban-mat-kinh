# 06 — Security Rules

> Các quy tắc bảo mật **bắt buộc** cho project PHP + MySQL.  
> Không có ngoại lệ. Áp dụng từ ngày đầu, không để sau.

---

## 1. SQL Injection Prevention

**Rule: Luôn dùng PDO Prepared Statements**

```php
// ✅ ĐÚNG — Positional placeholder
$stmt = $this->db->prepare("SELECT * FROM customers WHERE username = ?");
$stmt->execute([$username]);

// ✅ ĐÚNG — Named placeholder
$stmt = $this->db->prepare("SELECT * FROM customers WHERE username = :username");
$stmt->execute([':username' => $username]);

// ❌ SAI — String concatenation
$stmt = $this->db->query("SELECT * FROM customers WHERE username = '$username'");

// ❌ SAI — sprintf vào SQL
$sql = sprintf("SELECT * FROM products WHERE id = %d", $id);
```

**Dynamic ORDER BY** (column name không thể dùng placeholder):
```php
// ✅ ĐÚNG — Whitelist
$allowedSorts = ['product_name', 'price', 'created_at', 'view_count'];
$sortBy = in_array($_GET['sort'] ?? '', $allowedSorts) ? $_GET['sort'] : 'created_at';
$sql    = "SELECT * FROM products ORDER BY $sortBy DESC"; // An toàn vì đã whitelist
```

---

## 2. XSS Prevention

**Rule: Escape mọi output trong view**

```php
// ✅ ĐÚNG — htmlspecialchars() cho mọi biến user-controlled
<h3><?= htmlspecialchars($product['product_name']) ?></h3>
<input value="<?= htmlspecialchars($oldInput['username'] ?? '') ?>">
<p><?= htmlspecialchars($customer['full_name']) ?></p>

// Shorthand helper (thêm vào app/helpers/format.php)
function e(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}
// Dùng: <?= e($product['product_name']) ?>

// ❌ SAI — Echo trực tiếp không escape
<h3><?= $product['product_name'] ?></h3>
<h3><?php echo $product['product_name']; ?></h3>
```

---

## 3. Password Security

**Rule: Luôn hash bằng bcrypt**

```php
// ✅ Khi đăng ký / đổi mật khẩu
$hashedPassword = password_hash($rawPassword, PASSWORD_BCRYPT, ['cost' => 12]);
// Lưu $hashedPassword vào customers.password

// ✅ Khi đăng nhập
$customer = $model->findByUsername($username);
if ($customer && password_verify($rawPassword, $customer['password'])) {
    // Đăng nhập thành công
}

// ❌ KHÔNG BAO GIỜ
// MD5, SHA1, SHA256 thuần
// Lưu plain text vào DB
// Tự viết hàm hash
```

**Validate password khi đăng ký:**
```php
function validatePassword(string $password): array {
    $errors = [];
    if (strlen($password) < 8)             $errors[] = 'Password must be at least 8 characters.';
    if (!preg_match('/[A-Z]/', $password)) $errors[] = 'Password needs at least 1 uppercase letter.';
    if (!preg_match('/[0-9]/', $password)) $errors[] = 'Password needs at least 1 number.';
    return $errors;
}
```

---

## 4. Session Security

**Rule: Session an toàn**

```php
// index.php — Cấu hình session TRƯỚC session_start()
ini_set('session.cookie_httponly', 1);   // Không cho JavaScript đọc cookie
ini_set('session.cookie_secure',   0);   // Set 1 nếu dùng HTTPS
ini_set('session.use_strict_mode', 1);
ini_set('session.gc_maxlifetime', 7200); // 2 giờ

session_start();

// Regenerate session ID sau khi đăng nhập (chống Session Fixation)
session_regenerate_id(true);
$_SESSION['user'] = [
    'id'        => $customer['id'],
    'username'  => $customer['username'],
    'full_name' => $customer['full_name'],
    'role'      => $customer['role'],
    'status'    => $customer['status'],
];
```

**Session helper:**
```php
class Session {
    public static function getUser(): array|null {
        return $_SESSION['user'] ?? null;
    }

    public static function isLoggedIn(): bool {
        return isset($_SESSION['user']);
    }

    public static function isAdmin(): bool {
        return ($_SESSION['user']['role'] ?? '') === 'admin';
    }

    public static function setUser(array $customer): void {
        $_SESSION['user'] = [
            'id'        => $customer['id'],
            'username'  => $customer['username'],
            'full_name' => $customer['full_name'],
            'role'      => $customer['role'],
            'status'    => $customer['status'],
        ];
    }

    public static function destroy(): void {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']
            );
        }
        session_destroy();
    }
}
```

**Auth guard trong Controller:**
```php
protected function requireAuth(): void {
    if (!Session::isLoggedIn()) {
        Session::setFlash('warning', 'Please login to continue.');
        $this->redirect('/auth/login');
    }
}

protected function requireAdmin(): void {
    if (!Session::isLoggedIn()) {
        $this->redirect('/auth/login');
    }
    if (!Session::isAdmin()) {
        http_response_code(403);
        $this->render('errors/403', ['title' => 'Access Denied']);
        exit;
    }
    // Kiểm tra tài khoản có bị ban không
    if (Session::getUser()['status'] === 'banned') {
        Session::destroy();
        Session::setFlash('error', 'Your account has been banned.');
        $this->redirect('/auth/login');
    }
}
```

---

## 5. File Upload Security

**Rule: Validate kỹ trước khi lưu**

```php
// app/helpers/upload.php
function handleImageUpload(array $file, string $subfolder = 'products'): string|false {
    // 1. Kiểm tra lỗi upload
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }

    // 2. Kiểm tra kích thước (max 2MB)
    if ($file['size'] > 2 * 1024 * 1024) {
        return false;
    }

    // 3. Kiểm tra MIME type thực sự (KHÔNG tin $_FILES['type'])
    $finfo    = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    if (!in_array($mimeType, $allowedMimes)) {
        return false;
    }

    // 4. Kiểm tra extension
    $allowedExts = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    $ext         = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExts)) {
        return false;
    }

    // 5. Generate tên file an toàn — KHÔNG dùng tên gốc từ user
    $newFilename = uniqid('img_', true) . '.' . $ext;

    // 6. Đảm bảo thư mục tồn tại
    $uploadDir = UPLOAD_PATH . $subfolder . '/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // 7. Move file
    if (!move_uploaded_file($file['tmp_name'], $uploadDir . $newFilename)) {
        return false;
    }

    // Trả về đường dẫn tương đối để lưu vào products.image_url
    return $subfolder . '/' . $newFilename;
}
```

**Quy tắc upload:**
```
✅ Kiểm tra MIME type bằng finfo (không tin $_FILES['type'])
✅ Giới hạn kích thước 2MB
✅ Chỉ cho phép extension ảnh
✅ Đổi tên file bằng uniqid() — không dùng tên gốc
✅ Lưu vào public/uploads/ — cấu hình Nginx/Apache không cho execute PHP

❌ Không cho upload PHP, HTML, JS, SVG (có thể chứa script)
❌ Không dùng tên file gốc từ user (path traversal risk)
❌ Không tin $_FILES['type'] — browser có thể fake
```

---

## 6. CSRF Protection

```php
class Session {
    public static function getCsrfToken(): string {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function verifyCsrfToken(string $token): bool {
        return hash_equals($_SESSION['csrf_token'] ?? '', $token);
    }
}

// Trong mọi form POST — thêm hidden field
<input type="hidden" name="csrf_token" value="<?= Session::getCsrfToken() ?>">

// Trong mọi POST handler — verify đầu tiên
$token = $_POST['csrf_token'] ?? '';
if (!Session::verifyCsrfToken($token)) {
    http_response_code(403);
    die('Invalid CSRF token.');
}
```

---

## 7. Input Sanitization

```php
// Cast và trim input trước khi dùng
$productName   = trim($_POST['product_name']   ?? '');
$email         = trim(strtolower($_POST['email'] ?? ''));
$phone         = preg_replace('/[^0-9+]/', '', $_POST['phone'] ?? '');
$id            = (int)($_GET['id']             ?? 0);
$price         = (float)($_POST['price']       ?? 0);
$stockQuantity = max(0, (int)($_POST['stock_quantity'] ?? 0));
$page          = max(1, (int)($_GET['page']    ?? 1));
$categoryId    = (int)($_GET['category']       ?? 0);

// ❌ KHÔNG dùng addslashes() — dùng prepared statements thay thế
// ❌ KHÔNG strip_tags() khi lưu DB — chỉ khi hiển thị nếu cần
// ❌ KHÔNG tin $_FILES['type'] — verify bằng finfo
```

---

## 8. Error Handling & Information Disclosure

```php
// config/config.php
define('APP_ENV', 'production'); // 'development' | 'production'

// index.php
if (APP_ENV === 'production') {
    ini_set('display_errors', 0);
    ini_set('log_errors',     1);
    ini_set('error_log', ROOT_PATH . '/logs/error.log');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
}

// Global exception handler
set_exception_handler(function (Throwable $e) {
    error_log($e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    if (APP_ENV === 'production') {
        http_response_code(500);
        include ROOT_PATH . '/app/views/errors/500.php';
        exit;
    }
    throw $e; // Development: hiện lỗi đầy đủ
});
```

**Quy tắc:**
```
✅ Log đầy đủ lỗi vào file (logs/error.log)
✅ Hiển thị trang lỗi thân thiện cho user (production)
✅ HTTP status code đúng (404, 403, 500)
❌ Không lộ stack trace, tên file, SQL query ra ngoài
❌ Không die($e->getMessage()) trong production
❌ Không echo thông tin DB khi kết nối lỗi
```

---

## 9. Security Checklist

Trước mỗi tính năng kiểm tra:

**Input:**
- [ ] Trim và cast input trước khi dùng
- [ ] Validate phía server (không tin client-side validation)
- [ ] Cast `$_GET['id']` sang `(int)` trước khi dùng

**Database:**
- [ ] 100% prepared statements — zero string concat vào SQL
- [ ] ORDER BY dùng whitelist

**Output:**
- [ ] `htmlspecialchars()` hoặc `e()` cho mọi biến trong view
- [ ] Đặc biệt: `$product['product_name']`, `$customer['full_name']`, `$order['note']`...

**Auth:**
- [ ] Route cần login có `$this->requireAuth()`
- [ ] Route admin có `$this->requireAdmin()`
- [ ] Password dùng `password_hash()` / `password_verify()`
- [ ] `session_regenerate_id(true)` sau login
- [ ] `Session::destroy()` khi logout

**File Upload:**
- [ ] Validate MIME type bằng finfo (không tin $_FILES['type'])
- [ ] Kiểm tra extension nằm trong whitelist
- [ ] Đổi tên file bằng `uniqid()`
- [ ] Lưu vào `public/uploads/` với đúng subfolder

**Session:**
- [ ] `httponly` = 1 cho session cookie
- [ ] Session chỉ lưu: id, username, full_name, role, status

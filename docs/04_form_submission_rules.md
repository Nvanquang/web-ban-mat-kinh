# 04 — Form Submission Rules

> File này định nghĩa flow xử lý form chuẩn cho toàn bộ project.  
> Vì project dùng `<form method="POST">` thuần, AI **phải theo đúng pattern** này.

---

## 1. PRG Pattern — Quy tắc bắt buộc

**POST → Redirect → GET** (PRG Pattern)

```
GET  /auth/login     → Hiển thị form đăng nhập
POST /auth/login     → Xử lý login
  ├── Thành công → redirect("/")           + flash success
  └── Thất bại  → redirect("/auth/login")  + flash error + giữ old input
```

> ✅ Luôn redirect sau POST thành công  
> ✅ Luôn redirect sau POST thất bại (kèm flash + old input)  
> ❌ Không render HTML trực tiếp sau POST (gây double-submit khi F5)

---

## 2. Flow chuẩn — Mọi form đều theo đây

```
Bước 1: GET /path/to/form
    └── Controller render view với form HTML (rỗng hoặc có old input)

Bước 2: User điền form → Submit
    └── Browser gửi POST /path/to/form

Bước 3: Controller::method() xử lý POST
    ├── 3a. Đọc và sanitize $_POST
    ├── 3b. Validate (server-side)
    │     ├── Lỗi → set flash error + set old input → redirect về form
    │     └── OK → tiếp tục
    ├── 3c. Gọi Model để lưu/xử lý data
    │     ├── Lỗi DB → set flash error → redirect về form
    │     └── OK → tiếp tục
    └── 3d. Set flash success → redirect về trang đích

Bước 4: GET (sau redirect)
    └── Controller render trang đích + hiển thị flash message
```

---

## 3. Controller Pattern

### Form đăng nhập
```php
class AuthController extends Controller {

    // GET /auth/login
    public function loginForm(): void {
        if (Session::getUser()) {
            $this->redirect('/');
        }
        $this->render('auth/login', [
            'title'    => 'Login',
            'oldInput' => Session::getOldInput(),
        ]);
    }

    // POST /auth/login
    public function login(): void {
        // 3a. Đọc input
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        // 3b. Validate
        $errors = [];
        if (empty($username)) $errors[] = 'Username is required.';
        if (empty($password)) $errors[] = 'Password is required.';

        if ($errors) {
            Session::setFlash('error', implode(' ', $errors));
            Session::setOldInput(['username' => $username]);
            $this->redirect('/auth/login');
        }

        // 3c. Xác thực qua Model
        $model    = new CustomerModel();
        $customer = $model->findByUsername($username);

        if (!$customer || !password_verify($password, $customer['password'])) {
            Session::setFlash('error', 'Invalid username or password.');
            Session::setOldInput(['username' => $username]);
            $this->redirect('/auth/login');
        }

        if ($customer['status'] === 'banned') {
            Session::setFlash('error', 'Your account has been banned.');
            $this->redirect('/auth/login');
        }

        // 3d. Login thành công
        Session::setUser($customer);
        Session::setFlash('success', 'Welcome back, ' . $customer['full_name'] . '!');
        $this->redirect($customer['role'] === 'admin' ? '/admin' : '/');
    }
}
```

### Form có upload file (Tạo sản phẩm)
```php
class AdminProductController extends Controller {

    public function __construct() {
        $this->requireAdmin();
    }

    // GET /admin/products/create
    public function createForm(): void {
        $categoryModel = new GlassesCategoryModel();
        $this->render('admin/products/create', [
            'title'      => 'Add Product',
            'categories' => $categoryModel->getAllVisible(),
            'oldInput'   => Session::getOldInput(),
        ], 'admin');
    }

    // POST /admin/products/create
    public function create(): void {
        // 3a. Đọc input
        $data = [
            'category_id'    => (int)($_POST['category_id']    ?? 0),
            'product_name'   => trim($_POST['product_name']    ?? ''),
            'price'          => (float)($_POST['price']        ?? 0),
            'old_price'      => !empty($_POST['old_price']) ? (float)$_POST['old_price'] : null,
            'stock_quantity' => (int)($_POST['stock_quantity'] ?? 0),
            'description'    => trim($_POST['description']     ?? ''),
            'status'         => (int)($_POST['status']         ?? 1),
        ];

        // 3b. Validate
        $errors = $this->validateProduct($data);
        if ($errors) {
            Session::setFlash('error', implode('<br>', $errors));
            Session::setOldInput($data);
            $this->redirect('/admin/products/create');
        }

        // Xử lý upload ảnh
        $imageUrl = null;
        if (!empty($_FILES['image_url']['name'])) {
            $imageUrl = handleImageUpload($_FILES['image_url'], 'products');
            if (!$imageUrl) {
                Session::setFlash('error', 'Image upload failed. Please try again.');
                Session::setOldInput($data);
                $this->redirect('/admin/products/create');
            }
        }
        $data['image_url'] = $imageUrl;

        // 3c. Lưu DB
        $model = new ProductModel();
        $id    = $model->create($data);

        if (!$id) {
            Session::setFlash('error', 'Failed to add product. Please try again.');
            Session::setOldInput($data);
            $this->redirect('/admin/products/create');
        }

        // 3d. Thành công
        Session::setFlash('success', 'Product added successfully!');
        $this->redirect('/admin/products');
    }

    private function validateProduct(array $data): array {
        $errors = [];
        if (empty($data['product_name']))  $errors[] = 'Product name is required.';
        if ($data['price'] <= 0)           $errors[] = 'Price must be greater than 0.';
        if ($data['stock_quantity'] < 0)   $errors[] = 'Stock quantity cannot be negative.';
        if ($data['category_id'] <= 0)     $errors[] = 'Please select a category.';
        return $errors;
    }
}
```

---

## 4. HTML Form Template

### Form chuẩn
```html
<form method="POST" action="<?= BASE_URL ?>/auth/login">

    <div class="mb-3">
        <label for="username" class="form-label">Username</label>
        <input
            type="text"
            class="form-control"
            id="username"
            name="username"
            value="<?= htmlspecialchars($oldInput['username'] ?? '') ?>"
            required
        >
    </div>

    <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <input type="password" class="form-control" id="password" name="password" required>
    </div>

    <button type="submit" class="btn btn-primary">Login</button>
    <a href="<?= BASE_URL ?>/auth/register" class="btn btn-link">Don't have an account?</a>
</form>
```

### Form upload file
```html
<!-- enctype="multipart/form-data" bắt buộc khi có file upload -->
<form method="POST" action="<?= BASE_URL ?>/admin/products/create" enctype="multipart/form-data">

    <div class="mb-3">
        <label class="form-label">Product Name <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="product_name"
               value="<?= htmlspecialchars($oldInput['product_name'] ?? '') ?>" required>
    </div>

    <div class="mb-3">
        <label class="form-label">Price <span class="text-danger">*</span></label>
        <input type="number" class="form-control" name="price" min="0" step="1000"
               value="<?= htmlspecialchars($oldInput['price'] ?? '') ?>" required>
    </div>

    <div class="mb-3">
        <label class="form-label">Old Price (optional)</label>
        <input type="number" class="form-control" name="old_price" min="0" step="1000"
               value="<?= htmlspecialchars($oldInput['old_price'] ?? '') ?>">
    </div>

    <div class="mb-3">
        <label class="form-label">Stock Quantity</label>
        <input type="number" class="form-control" name="stock_quantity" min="0"
               value="<?= htmlspecialchars($oldInput['stock_quantity'] ?? 0) ?>">
    </div>

    <div class="mb-3">
        <label class="form-label">Category <span class="text-danger">*</span></label>
        <select class="form-select" name="category_id" required>
            <option value="">-- Select Category --</option>
            <?php foreach ($categories as $cat): ?>
            <option value="<?= $cat['id'] ?>"
                <?= ($oldInput['category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($cat['category_name']) ?>
            </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="mb-3">
        <label class="form-label">Product Image</label>
        <input type="file" class="form-control" name="image_url" accept="image/jpeg,image/png,image/webp">
        <div class="form-text">Formats: JPG, PNG, WEBP. Max 2MB.</div>
    </div>

    <button type="submit" class="btn btn-success">Add Product</button>
    <a href="<?= BASE_URL ?>/admin/products" class="btn btn-secondary">Cancel</a>
</form>
```

---

## 5. Old Input Pattern

```php
// Session helper
class Session {
    public static function setOldInput(array $data): void {
        $_SESSION['old_input'] = $data;
    }

    public static function getOldInput(): array {
        $data = $_SESSION['old_input'] ?? [];
        unset($_SESSION['old_input']); // Xóa sau khi đọc (dùng 1 lần)
        return $data;
    }
}

// Trong view — dùng null coalescing
<input name="email"       value="<?= htmlspecialchars($oldInput['email']       ?? '') ?>">
<input name="full_name"   value="<?= htmlspecialchars($oldInput['full_name']   ?? '') ?>">
<input name="phone"       value="<?= htmlspecialchars($oldInput['phone']       ?? '') ?>">
<textarea name="address"><?= htmlspecialchars($oldInput['address'] ?? '') ?></textarea>

// Select — giữ lại option đã chọn
<option value="1" <?= ($oldInput['category_id'] ?? '') == 1 ? 'selected' : '' ?>>
    Prescription Glasses
</option>
```

---

## 6. Flash Message Display

```php
// Trong layout — đặt đầu content area
<?php if ($flash): ?>
    <?php
    $alertClass = match($flash['type']) {
        'success' => 'alert-success',
        'error'   => 'alert-danger',
        'warning' => 'alert-warning',
        default   => 'alert-info',
    };
    ?>
    <div class="alert <?= $alertClass ?> alert-dismissible fade show" role="alert">
        <?= $flash['message'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>
```

---

## 7. Validate Rules chuẩn

```php
// app/helpers/validate.php

function validateRequired(array $fields, array $data): array {
    $errors = [];
    foreach ($fields as $field => $label) {
        if (empty($data[$field])) {
            $errors[$field] = "$label is required.";
        }
    }
    return $errors;
}

function validateEmail(string $email): bool {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function validateLength(string $value, int $min, int $max): bool {
    $len = mb_strlen($value);
    return $len >= $min && $len <= $max;
}

function validatePhone(string $phone): bool {
    return preg_match('/^(0[3|5|7|8|9])[0-9]{8}$/', $phone);
}

function validateImageUpload(array $file): array {
    $errors   = [];
    $allowed  = ['image/jpeg', 'image/png', 'image/webp'];
    $maxSize  = 2 * 1024 * 1024; // 2MB

    if ($file['error'] !== UPLOAD_ERR_OK)   $errors[] = 'File upload error.';
    if ($file['size'] > $maxSize)           $errors[] = 'Image must be under 2MB.';
    if (!in_array($file['type'], $allowed)) $errors[] = 'Only JPG, PNG, WEBP allowed.';

    return $errors;
}
```

---

## 8. Checklist Form — Mọi form phải có

- [ ] `method="POST"` với `action` đầy đủ URL
- [ ] File upload: có `enctype="multipart/form-data"`
- [ ] Tất cả field hiển thị lại `$oldInput` khi có lỗi
- [ ] Controller đọc `$_POST` → sanitize → validate trước khi dùng
- [ ] Sau POST luôn `redirect()` (không `render()` trực tiếp)
- [ ] Flash message set trước `redirect()`
- [ ] Mọi output trong view qua `htmlspecialchars()` để tránh XSS

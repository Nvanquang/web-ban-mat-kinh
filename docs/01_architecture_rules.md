# 01 — Architecture Rules (MVC Thuần PHP)

> File này khóa AI theo đúng MVC pattern.  
> Mọi code sinh ra **phải tuân thủ** các quy tắc dưới đây.

---

## 1. Nguyên tắc cốt lõi MVC

### Controller — Chỉ điều phối, không làm gì khác

```
Controller được phép:
✅ Nhận input từ $_GET, $_POST, $_SESSION
✅ Gọi Model để lấy / lưu dữ liệu
✅ Validate dữ liệu đầu vào (hoặc gọi helper validate)
✅ Gọi $this->render() để render view
✅ Gọi $this->redirect() để chuyển hướng
✅ Set flash message

Controller KHÔNG được phép:
❌ Viết SQL trực tiếp (dù chỉ 1 dòng)
❌ Chứa business logic phức tạp (tính giá, kiểm tra tồn kho...)
❌ Echo HTML ra trực tiếp
❌ Kết nối database trực tiếp (new PDO(...))
❌ Xử lý file upload (phải gọi helper)
```

**Ví dụ ĐÚNG:**
```php
class ProductController extends Controller {
    public function show(int $id): void {
        $model   = new ProductModel();
        $product = $model->findById($id);

        if (!$product) {
            $this->redirect('/404');
        }

        // Tăng lượt xem — business logic nhỏ, để trong model
        $model->incrementViewCount($id);

        $this->render('products/detail', [
            'product' => $product,
            'title'   => $product['product_name']
        ]);
    }
}
```

**Ví dụ SAI:**
```php
// ❌ SQL trong controller
class ProductController extends Controller {
    public function show(int $id): void {
        $db   = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM products WHERE id = ?"); // SAI!
        ...
    }
}
```

---

### Model — Business logic và data access

```
Model được phép:
✅ Tất cả các câu SQL (SELECT, INSERT, UPDATE, DELETE)
✅ Business logic: tính tổng tiền, kiểm tra tồn kho, soft delete
✅ Validation ở tầng data (check unique, check FK)
✅ Join nhiều bảng
✅ Trả về array hoặc false/null

Model KHÔNG được phép:
❌ Truy cập $_GET, $_POST, $_SESSION
❌ Redirect hoặc header()
❌ Echo / print HTML
❌ Gọi controller khác
```

**Ví dụ ĐÚNG:**
```php
class OrderModel extends Model {
    public function createOrder(int $customerId, array $shipping, array $cartItems): int|false {
        try {
            $this->db->beginTransaction();

            // Tính tổng tiền — business logic ở đây là đúng
            $totalAmount = 0;
            foreach ($cartItems as $item) {
                $totalAmount += $item['price'] * $item['quantity'];
            }

            // Insert đơn hàng
            $stmt = $this->db->prepare("INSERT INTO orders ...");
            $stmt->execute([...]);
            $orderId = (int)$this->db->lastInsertId();

            // Insert chi tiết + trừ tồn kho
            foreach ($cartItems as $item) {
                $stmt2 = $this->db->prepare("INSERT INTO order_details ...");
                $stmt2->execute([...]);
            }

            $this->db->commit();
            return $orderId;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
}
```

---

### View — Chỉ hiển thị, không logic

```
View được phép:
✅ Echo biến PHP đã được truyền từ controller
✅ Vòng lặp foreach để render list
✅ Điều kiện if đơn giản để ẩn/hiện UI
✅ include partial view (header, sidebar...)
✅ Dùng helper format (số tiền, ngày tháng)

View KHÔNG được phép:
❌ Gọi Model trực tiếp (new ProductModel())
❌ Truy cập $_GET, $_POST, $_SESSION trực tiếp (trừ flash message)
❌ Thực hiện query database
❌ Chứa business logic
❌ Redirect
```

**Ví dụ ĐÚNG:**
```php
<!-- views/products/index.php -->
<?php foreach ($products as $product): ?>
    <div class="product-card">
        <img src="<?= BASE_URL ?>/public/uploads/<?= htmlspecialchars($product['image_url']) ?>">
        <h3><?= htmlspecialchars($product['product_name']) ?></h3>
        <span><?= number_format($product['price'], 0, ',', '.') ?> ₫</span>
        <?php if ($product['old_price']): ?>
            <s><?= number_format($product['old_price'], 0, ',', '.') ?> ₫</s>
        <?php endif; ?>
    </div>
<?php endforeach; ?>
```

**Ví dụ SAI:**
```php
<!-- ❌ Gọi Model trong view -->
<?php
$model    = new ProductModel(); // SAI!
$products = $model->getAll();
?>
```

---

## 2. Base Classes

### Base Controller
```php
// app/core/Controller.php
abstract class Controller {
    // Render view với layout
    protected function render(string $view, array $data = [], string $layout = 'main'): void;

    // Redirect với flash message tùy chọn
    protected function redirect(string $url, string $flashType = '', string $flashMsg = ''): void;

    // Require đăng nhập — redirect về /auth/login nếu chưa login
    protected function requireAuth(): void;

    // Require quyền admin — redirect về / nếu không phải admin
    protected function requireAdmin(): void;

    // Lấy POST data đã trim
    protected function getPost(string $key, mixed $default = null): mixed;

    // Lấy GET data
    protected function getQuery(string $key, mixed $default = null): mixed;
}
```

### Base Model
```php
// app/core/Model.php
abstract class Model {
    protected PDO    $db;
    protected string $table;                    // Tên bảng chính
    protected string $primaryKey = 'id';

    public function __construct() {
        $this->db = Database::getInstance();
    }

    // Tìm theo primary key
    public function findById(int $id): array|false;

    // Lấy tất cả
    public function findAll(array $conditions = [], string $orderBy = ''): array;

    // Đếm tổng
    public function count(array $conditions = []): int;

    // Xóa theo id
    public function deleteById(int $id): bool;
}
```

---

## 3. File Size & Complexity Rules

### Controller
- Tối đa **150 dòng** mỗi file
- Tối đa **6–8 method** mỗi controller
- Mỗi method tối đa **30 dòng**
- Nếu controller > 150 dòng → tách thêm controller

### Model
- Không giới hạn số dòng (SQL có thể dài)
- Nhóm method theo chức năng: **Read**, **Write**, **Aggregate**
- Method tên phải rõ nghĩa: `getProductsByCategory()` thay vì `getData()`

### View
- Tách partial view nếu đoạn HTML lặp lại > 2 lần
- Partial view đặt trong `views/partials/`
- Dùng `include` để tái sử dụng

---

## 4. Tổ chức Admin vs Frontend

### Tách biệt hoàn toàn

```
app/controllers/
    ProductController.php            → Frontend (public)
    admin/
        AdminProductController.php   → Backend (admin only)

app/views/
    products/                        → Frontend views
    admin/
        products/                    → Admin views

app/models/
    ProductModel.php                 → Dùng chung (cả admin và frontend)
```

### Admin controller bắt buộc:
```php
class AdminProductController extends Controller {
    public function __construct() {
        $this->requireAdmin(); // Gọi ngay trong constructor
    }
}
```

---

## 5. Error Handling

```
✅ Model trả về false khi thất bại, array khi thành công
✅ Controller kiểm tra false → set flash error → redirect
✅ 404: Controller gọi $this->render('errors/404') với HTTP 404
✅ 500: Log lỗi, hiển thị trang lỗi thân thiện
❌ Không die() hoặc exit() trong controller/model
❌ Không hiển thị lỗi PHP raw ra màn hình (production)
```

---

## 6. Helpers & Utilities

Đặt trong `app/helpers/` — **function thuần, không class:**

```php
// app/helpers/format.php
function formatMoney(float $amount): string
function formatDate(string $datetime): string
function truncateText(string $text, int $length): string

// app/helpers/upload.php
function handleImageUpload(array $file, string $folder): string|false

// app/helpers/validate.php
function validateEmail(string $email): bool
function validatePhone(string $phone): bool
function validateRequired(array $fields, array $data): array  // Trả về errors
```

---

## 7. Checklist trước khi commit code

- [ ] Controller không chứa SQL
- [ ] Model không đọc `$_POST` / `$_GET`
- [ ] View không khởi tạo Model
- [ ] Tất cả output trong view đều qua `htmlspecialchars()`
- [ ] Tất cả query đều dùng prepared statement (PDO `?` hoặc `:name`)
- [ ] Admin route đều có `$this->requireAdmin()`
- [ ] Sau POST thành công đều redirect (PRG pattern)
- [ ] Flash message được set trước redirect

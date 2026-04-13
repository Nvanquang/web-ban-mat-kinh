# 05 — SQL Patterns (PDO + MySQL)

> Tất cả SQL trong project đều theo patterns này.  
> Dùng PDO với prepared statements — KHÔNG bao giờ string concat.

---

## 1. Database Singleton

```php
// app/core/Database.php
class Database {
    private static ?PDO $instance = null;

    public static function getInstance(): PDO {
        if (self::$instance === null) {
            $dsn = sprintf(
                'mysql:host=%s;dbname=%s;charset=%s',
                DB_HOST, DB_NAME, DB_CHARSET
            );
            self::$instance = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        }
        return self::$instance;
    }
}
```

---

## 2. CRUD Cơ bản

### SELECT một record
```php
// Tìm product theo id
public function findById(int $id): array|false {
    $stmt = $this->db->prepare("
        SELECT * FROM products WHERE id = ? AND status = 1
    ");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

// Tìm customer theo username
public function findByUsername(string $username): array|false {
    $stmt = $this->db->prepare("
        SELECT * FROM customers WHERE username = ?
    ");
    $stmt->execute([$username]);
    return $stmt->fetch();
}

// Tìm customer theo email
public function findByEmail(string $email): array|false {
    $stmt = $this->db->prepare("
        SELECT * FROM customers WHERE email = ?
    ");
    $stmt->execute([$email]);
    return $stmt->fetch();
}
```

### SELECT nhiều record
```php
// Lấy tất cả sản phẩm đang bán
public function getAllActive(): array {
    $stmt = $this->db->prepare("
        SELECT p.*, gc.category_name
        FROM products p
        LEFT JOIN glasses_categories gc ON p.category_id = gc.id
        WHERE p.status = 1
        ORDER BY p.created_at DESC
    ");
    $stmt->execute();
    return $stmt->fetchAll();
}

// Lấy theo danh mục
public function getByCategory(int $categoryId): array {
    $stmt = $this->db->prepare("
        SELECT p.*, gc.category_name
        FROM products p
        LEFT JOIN glasses_categories gc ON p.category_id = gc.id
        WHERE p.category_id = ? AND p.status = 1
        ORDER BY p.created_at DESC
    ");
    $stmt->execute([$categoryId]);
    return $stmt->fetchAll();
}

// Lấy sản phẩm hot (theo view_count)
public function getHotProducts(int $limit = 8): array {
    $stmt = $this->db->prepare("
        SELECT p.*, gc.category_name
        FROM products p
        LEFT JOIN glasses_categories gc ON p.category_id = gc.id
        WHERE p.status = 1
        ORDER BY p.view_count DESC
        LIMIT ?
    ");
    $stmt->execute([$limit]);
    return $stmt->fetchAll();
}
```

### INSERT
```php
// Thêm sản phẩm mới
public function create(array $data): int|false {
    $stmt = $this->db->prepare("
        INSERT INTO products
            (category_id, product_name, price, old_price,
             stock_quantity, description, image_url, status)
        VALUES
            (:category_id, :product_name, :price, :old_price,
             :stock_quantity, :description, :image_url, :status)
    ");

    $result = $stmt->execute([
        ':category_id'    => $data['category_id'],
        ':product_name'   => $data['product_name'],
        ':price'          => $data['price'],
        ':old_price'      => $data['old_price'],      // null OK với PDO
        ':stock_quantity' => $data['stock_quantity'],
        ':description'    => $data['description'],
        ':image_url'      => $data['image_url'],
        ':status'         => $data['status'] ?? 1,
    ]);

    return $result ? (int)$this->db->lastInsertId() : false;
}

// Đăng ký customer
public function register(array $data): int|false {
    $stmt = $this->db->prepare("
        INSERT INTO customers (username, password, email, full_name, phone, address)
        VALUES (:username, :password, :email, :full_name, :phone, :address)
    ");

    $result = $stmt->execute([
        ':username'  => $data['username'],
        ':password'  => password_hash($data['password'], PASSWORD_BCRYPT),
        ':email'     => $data['email'],
        ':full_name' => $data['full_name'] ?? null,
        ':phone'     => $data['phone']     ?? null,
        ':address'   => $data['address']   ?? null,
    ]);

    return $result ? (int)$this->db->lastInsertId() : false;
}
```

### UPDATE
```php
// Cập nhật sản phẩm
public function update(int $id, array $data): bool {
    $stmt = $this->db->prepare("
        UPDATE products SET
            category_id    = :category_id,
            product_name   = :product_name,
            price          = :price,
            old_price      = :old_price,
            stock_quantity = :stock_quantity,
            description    = :description,
            status         = :status
        WHERE id = :id
    ");
    return $stmt->execute([
        ':category_id'    => $data['category_id'],
        ':product_name'   => $data['product_name'],
        ':price'          => $data['price'],
        ':old_price'      => $data['old_price'],
        ':stock_quantity' => $data['stock_quantity'],
        ':description'    => $data['description'],
        ':status'         => $data['status'],
        ':id'             => $id,
    ]);
}

// Cập nhật trạng thái đơn hàng
public function updateStatus(int $id, string $status): bool {
    $stmt = $this->db->prepare("
        UPDATE orders SET status = ? WHERE id = ?
    ");
    return $stmt->execute([$status, $id]);
}

// Ban/unban customer
public function updateCustomerStatus(int $id, string $status): bool {
    $stmt = $this->db->prepare("
        UPDATE customers SET status = ? WHERE id = ?
    ");
    return $stmt->execute([$status, $id]);
}
```

### Soft Delete (Status = 0)
```php
// Ngừng kinh doanh sản phẩm — KHÔNG xóa khỏi DB
public function softDelete(int $id): bool {
    $stmt = $this->db->prepare("
        UPDATE products SET status = 0 WHERE id = ?
    ");
    return $stmt->execute([$id]);
}

// Khôi phục
public function restore(int $id): bool {
    $stmt = $this->db->prepare("
        UPDATE products SET status = 1 WHERE id = ?
    ");
    return $stmt->execute([$id]);
}
```

### Hard Delete
```php
// Xóa danh mục (chỉ khi không có sản phẩm liên quan)
public function deleteById(int $id): bool {
    $stmt = $this->db->prepare("DELETE FROM glasses_categories WHERE id = ?");
    return $stmt->execute([$id]);
}
```

---

## 3. JOIN Patterns

### Lấy sản phẩm kèm tên danh mục
```php
public function getProductWithCategory(int $id): array|false {
    $stmt = $this->db->prepare("
        SELECT
            p.*,
            gc.category_name,
            gc.id AS category_id
        FROM products p
        LEFT JOIN glasses_categories gc ON p.category_id = gc.id
        WHERE p.id = ? AND p.status = 1
    ");
    $stmt->execute([$id]);
    return $stmt->fetch();
}
```

### Lấy đơn hàng kèm thông tin customer và chi tiết
```php
public function getOrderWithDetails(int $orderId): array|false {
    // Thông tin đơn hàng + customer
    $stmt = $this->db->prepare("
        SELECT
            o.*,
            c.username,
            c.email,
            c.full_name AS account_name
        FROM orders o
        LEFT JOIN customers c ON o.customer_id = c.id
        WHERE o.id = ?
    ");
    $stmt->execute([$orderId]);
    $order = $stmt->fetch();

    if (!$order) return false;

    // Chi tiết sản phẩm trong đơn
    $stmt2 = $this->db->prepare("
        SELECT
            od.*,
            p.product_name,
            p.image_url,
            p.category_id
        FROM order_details od
        LEFT JOIN products p ON od.product_id = p.id
        WHERE od.order_id = ?
    ");
    $stmt2->execute([$orderId]);
    $order['items'] = $stmt2->fetchAll();

    return $order;
}
```

### Lấy lịch sử đơn hàng của customer
```php
public function getOrdersByCustomer(int $customerId): array {
    $stmt = $this->db->prepare("
        SELECT o.*, COUNT(od.id) AS item_count
        FROM orders o
        LEFT JOIN order_details od ON o.id = od.order_id
        WHERE o.customer_id = ?
        GROUP BY o.id
        ORDER BY o.order_date DESC
    ");
    $stmt->execute([$customerId]);
    return $stmt->fetchAll();
}
```

---

## 4. Search & Filter

### Tìm kiếm sản phẩm
```php
public function search(string $keyword, int $categoryId = 0): array {
    $keyword = '%' . $keyword . '%';

    if ($categoryId > 0) {
        $stmt = $this->db->prepare("
            SELECT p.*, gc.category_name
            FROM products p
            LEFT JOIN glasses_categories gc ON p.category_id = gc.id
            WHERE p.status = 1
              AND p.category_id = ?
              AND (p.product_name LIKE ? OR p.description LIKE ?)
            ORDER BY p.view_count DESC
        ");
        $stmt->execute([$categoryId, $keyword, $keyword]);
    } else {
        $stmt = $this->db->prepare("
            SELECT p.*, gc.category_name
            FROM products p
            LEFT JOIN glasses_categories gc ON p.category_id = gc.id
            WHERE p.status = 1
              AND (p.product_name LIKE ? OR p.description LIKE ?)
            ORDER BY p.view_count DESC
        ");
        $stmt->execute([$keyword, $keyword]);
    }

    return $stmt->fetchAll();
}
```

### Filter nhiều điều kiện (dynamic WHERE)
```php
public function getFiltered(array $filters): array {
    $where  = ['p.status = 1'];
    $params = [];

    if (!empty($filters['category_id'])) {
        $where[]  = 'p.category_id = ?';
        $params[] = (int)$filters['category_id'];
    }
    if (!empty($filters['min_price'])) {
        $where[]  = 'p.price >= ?';
        $params[] = (float)$filters['min_price'];
    }
    if (!empty($filters['max_price'])) {
        $where[]  = 'p.price <= ?';
        $params[] = (float)$filters['max_price'];
    }
    if (!empty($filters['keyword'])) {
        $where[]  = '(p.product_name LIKE ? OR p.description LIKE ?)';
        $params[] = '%' . $filters['keyword'] . '%';
        $params[] = '%' . $filters['keyword'] . '%';
    }

    // Whitelist sort để tránh SQL injection
    $orderMap = [
        'newest'     => 'p.created_at DESC',
        'price_asc'  => 'p.price ASC',
        'price_desc' => 'p.price DESC',
        'popular'    => 'p.view_count DESC',
    ];
    $order = $orderMap[$filters['sort'] ?? 'newest'] ?? 'p.created_at DESC';

    $sql = "
        SELECT p.*, gc.category_name
        FROM products p
        LEFT JOIN glasses_categories gc ON p.category_id = gc.id
        WHERE " . implode(' AND ', $where) . "
        ORDER BY $order
    ";

    $stmt = $this->db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}
```

---

## 5. Pagination

```php
// Model
public function getPaginated(int $page, int $perPage = 12, int $categoryId = 0): array {
    $offset = ($page - 1) * $perPage;

    // Điều kiện lọc
    $where  = ['p.status = 1'];
    $params = [];
    if ($categoryId > 0) {
        $where[]  = 'p.category_id = ?';
        $params[] = $categoryId;
    }
    $whereStr = implode(' AND ', $where);

    // Đếm total
    $countStmt = $this->db->prepare("
        SELECT COUNT(*) FROM products p WHERE $whereStr
    ");
    $countStmt->execute($params);
    $total = (int)$countStmt->fetchColumn();

    // Lấy data
    $dataParams   = array_merge($params, [$perPage, $offset]);
    $stmt = $this->db->prepare("
        SELECT p.*, gc.category_name
        FROM products p
        LEFT JOIN glasses_categories gc ON p.category_id = gc.id
        WHERE $whereStr
        ORDER BY p.created_at DESC
        LIMIT ? OFFSET ?
    ");
    $stmt->execute($dataParams);

    return [
        'items'        => $stmt->fetchAll(),
        'total'        => $total,
        'per_page'     => $perPage,
        'current_page' => $page,
        'total_pages'  => (int)ceil($total / $perPage),
        'has_prev'     => $page > 1,
        'has_next'     => $page < ceil($total / $perPage),
    ];
}

// Controller
$page   = max(1, (int)($_GET['page'] ?? 1));
$catId  = (int)($_GET['category'] ?? 0);
$result = $model->getPaginated($page, 12, $catId);

$this->render('products/index', [
    'products'   => $result['items'],
    'pagination' => $result,
]);

// View — Pagination Bootstrap 5
<?php if ($pagination['total_pages'] > 1): ?>
<nav aria-label="Product pagination">
  <ul class="pagination justify-content-center">
    <?php if ($pagination['has_prev']): ?>
    <li class="page-item">
      <a class="page-link" href="?page=<?= $pagination['current_page'] - 1 ?>">«</a>
    </li>
    <?php endif; ?>

    <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
    <li class="page-item <?= $i === $pagination['current_page'] ? 'active' : '' ?>">
      <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
    </li>
    <?php endfor; ?>

    <?php if ($pagination['has_next']): ?>
    <li class="page-item">
      <a class="page-link" href="?page=<?= $pagination['current_page'] + 1 ?>">»</a>
    </li>
    <?php endif; ?>
  </ul>
</nav>
<?php endif; ?>
```

---

## 6. Transaction — Tạo đơn hàng

```php
// OrderModel.php
public function createOrder(int $customerId, array $shipping, array $cartItems): int|false {
    try {
        $this->db->beginTransaction();

        // Tính tổng tiền
        $totalAmount = 0;
        foreach ($cartItems as $item) {
            $totalAmount += $item['price'] * $item['quantity'];
        }

        // 1. Insert đơn hàng
        $stmt = $this->db->prepare("
            INSERT INTO orders
                (customer_id, receiver_name, receiver_phone, shipping_address,
                 note, payment_method, total_amount, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')
        ");
        $stmt->execute([
            $customerId,
            $shipping['receiver_name'],
            $shipping['receiver_phone'],
            $shipping['shipping_address'],
            $shipping['note']           ?? null,
            $shipping['payment_method'] ?? 'COD',
            $totalAmount,
        ]);
        $orderId = (int)$this->db->lastInsertId();

        // 2. Insert order_details + Trừ stock_quantity
        $stmtDetail = $this->db->prepare("
            INSERT INTO order_details (order_id, product_id, quantity, sale_price)
            VALUES (?, ?, ?, ?)
        ");
        $stmtStock = $this->db->prepare("
            UPDATE products SET stock_quantity = stock_quantity - ?
            WHERE id = ? AND stock_quantity >= ?
        ");

        foreach ($cartItems as $item) {
            // Snapshot sale_price = price tại thời điểm mua
            $stmtDetail->execute([
                $orderId,
                $item['id'],
                $item['quantity'],
                $item['price'],
            ]);

            // Trừ kho — nếu rowCount = 0 thì không đủ hàng
            $stmtStock->execute([$item['quantity'], $item['id'], $item['quantity']]);
            if ($stmtStock->rowCount() === 0) {
                $this->db->rollBack();
                return false; // Hết hàng
            }
        }

        $this->db->commit();
        return $orderId;

    } catch (Exception $e) {
        $this->db->rollBack();
        error_log('createOrder error: ' . $e->getMessage());
        return false;
    }
}
```

---

## 7. Aggregate & Stats (Admin Dashboard)

```php
// Doanh thu theo tháng
public function getRevenueByMonth(int $year): array {
    $stmt = $this->db->prepare("
        SELECT
            MONTH(order_date)  AS month,
            COUNT(*)           AS order_count,
            SUM(total_amount)  AS revenue
        FROM orders
        WHERE YEAR(order_date) = ? AND status = 'completed'
        GROUP BY MONTH(order_date)
        ORDER BY month ASC
    ");
    $stmt->execute([$year]);
    return $stmt->fetchAll();
}

// Top sản phẩm bán chạy
public function getTopSellingProducts(int $limit = 5): array {
    $stmt = $this->db->prepare("
        SELECT
            p.id,
            p.product_name,
            p.image_url,
            SUM(od.quantity)              AS total_sold,
            SUM(od.quantity * od.sale_price) AS revenue
        FROM order_details od
        JOIN products p ON od.product_id = p.id
        JOIN orders o   ON od.order_id   = o.id
        WHERE o.status IN ('completed','shipped')
        GROUP BY p.id, p.product_name, p.image_url
        ORDER BY total_sold DESC
        LIMIT ?
    ");
    $stmt->execute([$limit]);
    return $stmt->fetchAll();
}

// Đếm đơn hàng theo status
public function countByStatus(): array {
    $stmt = $this->db->prepare("
        SELECT status, COUNT(*) AS total
        FROM orders
        GROUP BY status
    ");
    $stmt->execute();
    // Trả về ['pending' => 5, 'confirmed' => 12, ...]
    return array_column($stmt->fetchAll(), 'total', 'status');
}

// Đếm customers mới trong tháng
public function countNewCustomersThisMonth(): int {
    $stmt = $this->db->prepare("
        SELECT COUNT(*) FROM customers
        WHERE role = 'customer'
          AND MONTH(created_at) = MONTH(NOW())
          AND YEAR(created_at)  = YEAR(NOW())
    ");
    $stmt->execute();
    return (int)$stmt->fetchColumn();
}

// Admin: lấy danh sách tư vấn chờ xử lý
public function getPendingConsultations(): array {
    $stmt = $this->db->prepare("
        SELECT cn.*, c.full_name, c.email
        FROM consultations cn
        LEFT JOIN customers c ON cn.customer_id = c.id
        WHERE cn.status = 'pending'
        ORDER BY cn.sent_at ASC
    ");
    $stmt->execute();
    return $stmt->fetchAll();
}
```

---

## 8. Increment View Count

```php
// ProductModel.php
public function incrementViewCount(int $id): void {
    $stmt = $this->db->prepare("
        UPDATE products SET view_count = view_count + 1 WHERE id = ?
    ");
    $stmt->execute([$id]);
    // Fire and forget — không cần check kết quả
}
```

---

## 9. Quy tắc SQL bắt buộc

```
✅ Luôn dùng prepared statements (? hoặc :name)
✅ Fetch mode mặc định: PDO::FETCH_ASSOC
✅ Transaction cho mọi operation gồm nhiều bước
✅ fetchColumn() cho COUNT query
✅ rowCount() để kiểm tra UPDATE/DELETE có ảnh hưởng không
✅ Whitelist cho ORDER BY (không dùng user input thẳng vào ORDER BY)
✅ error_log() khi catch exception trong transaction

❌ Không string concat vào SQL
❌ Không SELECT * trong query phức tạp — liệt kê column cần thiết
❌ Không dùng mysql_ functions (đã deprecated)
❌ Không bỏ qua lỗi PDO — luôn để ERRMODE_EXCEPTION
```

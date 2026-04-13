# 02 — Database Schema

> File tham chiếu duy nhất cho mọi thao tác với database.  
> AI **phải bám schema này** khi viết Model, SQL, hoặc bất kỳ thứ gì liên quan DB.

---

## 1. DDL — Toàn bộ Schema

```sql
CREATE DATABASE IF NOT EXISTS eyeglass_db
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE eyeglass_db;

-- ============================================================
-- CUSTOMERS (dùng cho cả admin và khách hàng)
-- ============================================================
CREATE TABLE customers (
    id         INT PRIMARY KEY AUTO_INCREMENT,
    username   VARCHAR(50)  NOT NULL UNIQUE,
    password   VARCHAR(255) NOT NULL,              -- bcrypt hash
    email      VARCHAR(100) NOT NULL UNIQUE,
    full_name  VARCHAR(100),
    phone      VARCHAR(15),
    address    TEXT,
    role       ENUM('customer','admin') DEFAULT 'customer',
    status     ENUM('active','banned')  DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- GLASSES_CATEGORIES (danh mục sản phẩm)
-- ============================================================
CREATE TABLE glasses_categories (
    id            INT PRIMARY KEY AUTO_INCREMENT,
    category_name VARCHAR(50) NOT NULL,
    description   TEXT,
    status        TINYINT(1) DEFAULT 1  -- 1: visible, 0: hidden
);

-- ============================================================
-- PRODUCTS
-- ============================================================
CREATE TABLE products (
    id             INT PRIMARY KEY AUTO_INCREMENT,
    category_id    INT,
    product_name   VARCHAR(100)   NOT NULL,
    price          DECIMAL(10,2)  NOT NULL,
    old_price      DECIMAL(10,2)  DEFAULT NULL,   -- Old price for promotion display
    stock_quantity INT            DEFAULT 0,      -- Inventory quantity
    description    TEXT,
    image_url      VARCHAR(255),
    view_count     INT            DEFAULT 0,      -- Used to filter hot products
    is_custom      TINYINT(1)     DEFAULT 0,
    status         TINYINT(1)     DEFAULT 1,      -- 1: selling, 0: discontinued
    created_at     DATETIME       DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES glasses_categories(id) ON DELETE SET NULL
);

-- ============================================================
-- CART_ITEMS 
-- ============================================================
CREATE TABLE cart_items (
    id           INT PRIMARY KEY AUTO_INCREMENT,
    customer_id  INT NOT NULL,
    product_id   INT NOT NULL,
    quantity     INT NOT NULL DEFAULT 1,
    sale_price   DECIMAL(10,2) NOT NULL, -- snapshot giá lúc thêm giỏ
    created_at   DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at   DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY uk_customer_product (customer_id, product_id)
);

-- ============================================================
-- ORDERS
-- ============================================================
CREATE TABLE orders (
    id              INT PRIMARY KEY AUTO_INCREMENT,
    customer_id     INT,
    receiver_name   VARCHAR(100),
    receiver_phone  VARCHAR(15),
    shipping_address TEXT,
    note            TEXT,
    payment_method  VARCHAR(50) DEFAULT 'COD',    -- COD, Bank Transfer, VNPay
    order_date      DATETIME DEFAULT CURRENT_TIMESTAMP,
    status          ENUM('pending','confirmed','shipped','completed','cancelled') DEFAULT 'pending',
    total_amount    DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
);

-- ============================================================
-- ORDER_DETAILS
-- ============================================================
CREATE TABLE order_details (
    id         INT PRIMARY KEY AUTO_INCREMENT,
    order_id   INT,
    product_id INT,
    quantity   INT           NOT NULL,
    sale_price DECIMAL(10,2) NOT NULL,            -- Snapshot price at time of purchase
    FOREIGN KEY (order_id)   REFERENCES orders(id)   ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
);

-- ============================================================
-- CONSULTATIONS
-- ============================================================
CREATE TABLE consultations (
    id          INT PRIMARY KEY AUTO_INCREMENT,
    customer_id INT,
    content     TEXT NOT NULL,
    reply       TEXT,                             -- Response from admin
    status      ENUM('pending','resolved') DEFAULT 'pending',
    sent_at     DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
);
```

---

## 2. Indexes bổ sung (Performance)

```sql
-- products: tìm kiếm và lọc thường xuyên
ALTER TABLE products ADD INDEX idx_category    (category_id);
ALTER TABLE products ADD INDEX idx_status      (status);
ALTER TABLE products ADD INDEX idx_view_count  (view_count DESC);
ALTER TABLE products ADD INDEX idx_created_at  (created_at DESC);
ALTER TABLE products ADD FULLTEXT INDEX ft_product_search (product_name, description);

-- orders: lọc theo khách hàng và trạng thái
ALTER TABLE orders ADD INDEX idx_customer_id (customer_id);
ALTER TABLE orders ADD INDEX idx_status      (status);
ALTER TABLE orders ADD INDEX idx_order_date  (order_date DESC);

-- consultations
ALTER TABLE consultations ADD INDEX idx_customer_id (customer_id);
ALTER TABLE consultations ADD INDEX idx_status      (status);
```

---

## 3. Mô tả chi tiết từng bảng

### `customers`
| Column | Type | Ghi chú |
|---|---|---|
| id | INT AI PK | |
| username | VARCHAR(50) UNIQUE | Dùng để đăng nhập |
| password | VARCHAR(255) | **bcrypt hash** — KHÔNG lưu plain text |
| email | VARCHAR(100) UNIQUE | |
| full_name | VARCHAR(100) | Có thể NULL |
| phone | VARCHAR(15) | Lưu string, kể cả +84 |
| address | TEXT | Địa chỉ mặc định của tài khoản |
| role | ENUM | `'customer'` \| `'admin'` |
| status | ENUM | `'active'` \| `'banned'` |
| created_at | DATETIME | Auto set |

### `glasses_categories`
| Column | Type | Ghi chú |
|---|---|---|
| id | INT AI PK | |
| category_name | VARCHAR(50) | VD: "Reading Glasses", "Sunglasses" |
| description | TEXT | Mô tả danh mục |
| status | TINYINT(1) | `1` = visible, `0` = hidden |

### `products`
| Column | Type | Ghi chú |
|---|---|---|
| id | INT AI PK | |
| category_id | INT FK | SET NULL khi xóa danh mục |
| product_name | VARCHAR(100) | |
| price | DECIMAL(10,2) | Giá hiện tại (VNĐ) |
| old_price | DECIMAL(10,2) NULL | Nếu có → hiển thị badge khuyến mãi |
| stock_quantity | INT | Tồn kho, `0` = hết hàng |
| description | TEXT | Mô tả chi tiết |
| image_url | VARCHAR(255) | Tên file, lưu trong `public/uploads/` |
| view_count | INT | Tăng mỗi lần xem chi tiết |
| is_custom | TINYINT(1) | `1` = sản phẩm đặt riêng theo yêu cầu |
| status | TINYINT(1) | `1` = đang bán, `0` = ngừng kinh doanh |
| created_at | DATETIME | |

### `orders`
| Column | Type | Ghi chú |
|---|---|---|
| id | INT AI PK | |
| customer_id | INT FK | CASCADE khi xóa khách |
| receiver_name | VARCHAR(100) | Khác tên tài khoản nếu mua tặng |
| receiver_phone | VARCHAR(15) | SĐT giao hàng |
| shipping_address | TEXT | Địa chỉ giao hàng cho đơn này |
| note | TEXT | Ghi chú từ khách |
| payment_method | VARCHAR(50) | `COD` \| `Bank Transfer` \| `VNPay` |
| order_date | DATETIME | |
| status | ENUM | Xem flow trạng thái bên dưới |
| total_amount | DECIMAL(10,2) | Snapshot tổng tiền tại thời điểm đặt |

**Flow trạng thái đơn hàng:**
```
pending → confirmed → shipped → completed
    ↘                               ↗
         cancelled (từ bất kỳ bước nào)
```

### `order_details`
| Column | Type | Ghi chú |
|---|---|---|
| id | INT AI PK | |
| order_id | INT FK | CASCADE khi xóa đơn |
| product_id | INT FK | SET NULL khi xóa sản phẩm |
| quantity | INT | |
| sale_price | DECIMAL(10,2) | **Snapshot giá tại thời điểm mua** |

> ⚠️ `sale_price` phải copy từ `products.price` lúc tạo đơn, KHÔNG join lại sau này.

### `consultations`
| Column | Type | Ghi chú |
|---|---|---|
| id | INT AI PK | |
| customer_id | INT FK | |
| content | TEXT | Câu hỏi của khách |
| reply | TEXT NULL | Admin điền vào khi trả lời |
| status | ENUM | `'pending'` \| `'resolved'` |
| sent_at | DATETIME | |

---

## 4. Sample Data

```sql
-- Glasses categories
INSERT INTO glasses_categories (category_name, description) VALUES
('Prescription Glasses', 'Glasses for nearsighted and farsighted'),
('Sunglasses',           'UV protection and fashion sunglasses'),
('Fashion Glasses',      'Non-prescription fashion frames'),
('Reading Glasses',      'Glasses for farsighted vision');

-- Admin account (password: Admin@123)
INSERT INTO customers (username, password, email, full_name, role) VALUES
('admin',
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
 'admin@eyeglass.vn', 'Administrator', 'admin');

-- Test customer (password: User@123)
INSERT INTO customers (username, password, email, full_name, phone, address) VALUES
('johndoe',
 '$2y$10$TKh8H1.PFGsy1YhKM3K5KOdXN2aYSmDVQb5ZR.kRbMivHNNFyKOx.',
 'john@gmail.com', 'John Doe', '0901234567', '123 Main St, District 1, HCMC');

-- Products
INSERT INTO products (category_id, product_name, price, old_price, stock_quantity, description, image_url) VALUES
(1, 'Lightweight TR90 Prescription Frame', 850000,  1200000, 50,
 'Ultra-light titanium frame, suitable for all face shapes', 'products/tr90_frame.jpg'),
(2, 'Rayban Classic Sunglasses',           2200000, NULL,    30,
 'Fashion sunglasses with UV400 protection',                 'products/rayban_classic.jpg'),
(3, 'Retro Fashion Glasses',               450000,  600000,  100,
 'Vintage retro design, trending style',                     'products/retro_glasses.jpg'),
(1, 'Korean Round Prescription Frame',    650000,  NULL,    45,
 'Korean style, thin metal frame',                           'products/korean_frame.jpg');
```

---

## 5. Relationships Map

```
customers (1) ──────────── (N) orders
                                  │
                                  └─── (N) order_details ──── (1) products
                                                                    │
glasses_categories (1) ─────────────────────────────── (N) products

customers (1) ──────────── (N) consultations
```

---

## 6. Quy tắc khi viết Model

1. **Luôn dùng prepared statements** — không bao giờ string concat vào SQL
2. **Kiểm tra `stock_quantity > 0`** trước khi cho thêm vào giỏ hàng
3. **Giảm `stock_quantity`** trong transaction khi tạo đơn hàng
4. **`sale_price` trong `order_details`** = snapshot, copy từ `products.price` lúc đặt hàng
5. **Soft status** cho products: set `status = 0` thay vì DELETE
6. **`password`** luôn hash bằng `password_hash($pw, PASSWORD_BCRYPT)` trước khi INSERT
7. **JOIN `glasses_categories`** khi cần hiển thị tên danh mục trong danh sách sản phẩm
8. **`sent_at`** trong consultations dùng để sort mới nhất lên đầu

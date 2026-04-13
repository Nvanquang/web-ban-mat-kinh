# Module: Orders (Frontend)

## Objective
- Cho phép customer checkout từ giỏ hàng
- Thu thập thông tin giao hàng (tên, SĐT, địa chỉ, ghi chú, phương thức thanh toán)
- Tạo `orders` + `order_details` trong transaction, đồng thời trừ `stock_quantity`
- Xem danh sách lịch sử đơn hàng của customer
- Xem chi tiết từng đơn hàng

---

## Wireframe thiết kế giao diện

### Checkout Page — `/orders/checkout`
```
┌─────────────────────────────────────────────────────────────────┐
│  [NAVBAR]                                                       │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  Checkout                                                       │
│                                                                 │
│  ┌───────────────────────────────┐ ┌──────────────────────────┐│
│  │  SHIPPING INFORMATION         │ │  ORDER REVIEW            ││
│  │───────────────────────────────│ │──────────────────────────││
│  │  Receiver Name *              │ │  TR90 Frame x1  850,000₫ ││
│  │  ┌─────────────────────────┐  │ │  Rayban x1    2,200,000₫ ││
│  │  └─────────────────────────┘  │ │  Retro x2      900,000₫ ││
│  │  Phone *                      │ │  ────────────────────    ││
│  │  ┌─────────────────────────┐  │ │  Subtotal    3,950,000₫  ││
│  │  └─────────────────────────┘  │ │  Shipping         Free   ││
│  │  Shipping Address *           │ │  ────────────────────    ││
│  │  ┌─────────────────────────┐  │ │  Total       3,950,000₫  ││
│  │  │                         │  │ │                          ││
│  │  └─────────────────────────┘  │ │  Payment Method          ││
│  │  Note (optional)              │ │  ○ COD (Cash on Delivery)││
│  │  ┌─────────────────────────┐  │ │  ○ Bank Transfer         ││
│  │  └─────────────────────────┘  │ │  ○ VNPay                 ││
│  │                               │ │                          ││
│  │  [Use my account address]     │ │  [  Place Order  ]       ││
│  └───────────────────────────────┘ │  (bg: #0ea5e9)           ││
│                                   └──────────────────────────┘│
└─────────────────────────────────────────────────────────────────┘
```

### Order History — `/orders`
```
┌─────────────────────────────────────────────────────────────────┐
│  [NAVBAR]                                                       │
├─────────────────────────────────────────────────────────────────┤
│  My Orders                                                      │
│                                                                 │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │ Order #12   Jan 15, 2025   3,950,000₫  [pending]  [View]│   │
│  ├─────────────────────────────────────────────────────────┤   │
│  │ Order #8    Jan 10, 2025   2,200,000₫  [completed][View]│   │
│  ├─────────────────────────────────────────────────────────┤   │
│  │ Order #3    Dec 28, 2024     850,000₫  [cancelled][View]│   │
│  └─────────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────────┘
```

### Order Detail — `/orders/{id}`
```
┌─────────────────────────────────────────────────────────────────┐
│  [NAVBAR]                                                       │
│  My Orders > Order #12                                          │
├─────────────────────────────────────────────────────────────────┤
│  Order #12                            Status: [pending]         │
│  Placed: January 15, 2025                                       │
│  ─────────────────────────────────────────────────────────────  │
│  ITEMS                                                          │
│  ┌────────────────────────────────────────────────────────┐    │
│  │ [IMG] TR90 Frame     850,000₫ × 1    =    850,000₫     │    │
│  │ [IMG] Rayban Classic  2,200,000₫ × 1  =  2,200,000₫   │    │
│  │ [IMG] Retro Glasses   450,000₫ × 2   =    900,000₫    │    │
│  └────────────────────────────────────────────────────────┘    │
│  ─────────────────────────────────────────────────────────────  │
│  SHIPPING                         PAYMENT                       │
│  John Doe                         Method: COD                   │
│  0901234567                       Total: 3,950,000₫             │
│  123 Main St, District 1                                        │
└─────────────────────────────────────────────────────────────────┘
```

**Style notes:**
- Status badge colors:
  - `pending`: `bg #fef3c7`, text `#d97706`
  - `confirmed`: `bg #dbeafe`, text `#2563eb`
  - `shipped`: `bg #ede9fe`, text `#7c3aed`
  - `completed`: `bg #dcfce7`, text `#16a34a`
  - `cancelled`: `bg #fee2e2`, text `#dc2626`
- Checkout form: 2-col layout (form left, summary right)
- Summary card: sticky, `bg #ffffff`, shadow `--shadow-sm`
- Place Order button: `bg #0ea5e9`, font-size 1.1rem, full width

---

## Routes

| Method | URL | Handler | Auth |
|---|---|---|---|
| GET | `/orders` | `OrderController::index` | Login required |
| GET | `/orders/{id}` | `OrderController::show` | Login required + owns order |
| GET | `/orders/checkout` | `OrderController::checkoutForm` | Login required |
| POST | `/orders/checkout` | `OrderController::checkout` | Login required |

---

## UI Pages

### `views/orders/index.php`
- Flash message
- Bảng orders: #id, order_date, total_amount, status badge, View button
- Empty state nếu chưa có đơn nào

### `views/orders/detail.php`
- Breadcrumb: My Orders > Order #{id}
- Order header: id, order_date, status badge
- Items table: thumbnail, product_name, sale_price, quantity, subtotal/row
- Shipping info: receiver_name, receiver_phone, shipping_address, note
- Payment: payment_method, total_amount

### `views/orders/checkout.php`
- Flash message
- Form shipping: receiver_name, receiver_phone, shipping_address, note
- "Use my address" button (JS pre-fill từ currentUser)
- Order review: items list, subtotal, shipping, total
- Payment method radio: COD, Bank Transfer, VNPay
- Place Order button

---

## Data Processing Flow

### GET `/orders`
```
1. requireAuth()
2. $customerId = Session::getUser()['id']
3. OrderModel::getOrdersByCustomer($customerId)
4. render('orders/index', [orders, title])
```

### GET `/orders/{id}`
```
1. requireAuth()
2. $id = (int)$segments[1]
   - $id <= 0 → redirect('/orders')
3. OrderModel::getOrderWithDetails($id)
   - not found → 404
4. Kiểm tra ownership: order['customer_id'] !== currentUser['id']
   - true → 403 (không được xem đơn của người khác)
5. render('orders/detail', [order, title])
```

### GET `/orders/checkout`
```
1. requireAuth()
2. $cart = $_SESSION['cart'] ?? []
   - empty → flash 'Cart is empty' + redirect('/cart')
3. Tính subtotal, shipping, total
4. $customer = CustomerModel::findById(currentUser['id'])
   (để pre-fill form nếu user có địa chỉ)
5. render('orders/checkout', [cart, subtotal, shipping, total, customer, oldInput])
```

### POST `/orders/checkout`
```
1. requireAuth()
2. $cart = $_SESSION['cart'] ?? []
   - empty → redirect('/cart')
3. Đọc $_POST: receiver_name, receiver_phone, shipping_address, note, payment_method
4. Validate (xem section Validation)
   - fail → flash error + setOldInput + redirect('/orders/checkout')
5. Verify stock (DB check):
   foreach cart item:
     ProductModel::findById(item.id)
     item.quantity > product.stock_quantity
     → flash 'Item "{name}" only has {n} left' + redirect('/cart')
6. OrderModel::createOrder($customerId, $shipping, $cartItems)
   → Trong transaction:
      a. INSERT orders
      b. INSERT order_details (snapshot sale_price = item.price)
      c. UPDATE products SET stock_quantity - quantity (verify >= 0)
   - false → flash 'Order failed, please try again' + redirect('/orders/checkout')
7. $_SESSION['cart'] = []   ← Xóa cart sau khi order thành công
8. flash success 'Order #{id} placed successfully!'
9. redirect('/orders/{id}')
```

---

## Validation

### Checkout form
| Field | Rule | Error |
|---|---|---|
| `receiver_name` | Required | "Receiver name is required." |
| `receiver_name` | Length 2–100 | "Name must be 2–100 characters." |
| `receiver_phone` | Required | "Phone number is required." |
| `receiver_phone` | Regex VN phone | "Invalid phone number." |
| `shipping_address` | Required | "Shipping address is required." |
| `shipping_address` | Length 10–500 | "Address must be 10–500 characters." |
| `note` | Optional, max 500 | Trim, không validate |
| `payment_method` | Whitelist: COD, Bank Transfer, VNPay | Default "COD" |

### Business rules
| Rule | Xử lý |
|---|---|
| Cart rỗng khi checkout | redirect `/cart` |
| Product hết hàng khi checkout | flash error + redirect `/cart` |
| `order.customer_id !== user.id` | HTTP 403 |

---

## Database Interaction

**Bảng:** `orders`, `order_details`, `products`, `customers`

| Action | Method | Notes |
|---|---|---|
| Lấy orders của customer | `getOrdersByCustomer(customerId)` | ORDER BY order_date DESC |
| Lấy order + details + items | `getOrderWithDetails(orderId)` | JOIN order_details + products |
| Tạo order + details + trừ kho | `createOrder(customerId, shipping, cartItems)` | **Transaction bắt buộc** |
| Lấy thông tin customer | `CustomerModel::findById(id)` | Pre-fill checkout form |
| Verify stock trước checkout | `ProductModel::findById(id)` | Kiểm tra stock_quantity |

**Transaction trong `createOrder`:**
```
BEGIN TRANSACTION
  INSERT INTO orders (...)               → lấy order_id
  foreach item:
    INSERT INTO order_details (...)      → snapshot sale_price
    UPDATE products SET stock_quantity = stock_quantity - quantity
    WHERE id = ? AND stock_quantity >= ? → rowCount = 0 thì ROLLBACK
COMMIT
```

---

## Permissions

| Route | Yêu cầu |
|---|---|
| Tất cả `/orders/*` | Login required |
| GET `/orders/{id}` | Login + order phải thuộc về current user |
| Admin xem order | Qua `/admin/orders` (module riêng) |

---

## Error Handling

| Tình huống | Xử lý |
|---|---|
| Cart rỗng khi vào checkout | flash + redirect `/cart` |
| Validate checkout fail | flash + setOldInput + redirect `/orders/checkout` |
| Hết hàng khi POST checkout | flash error nêu tên sp + redirect `/cart` |
| DB transaction thất bại | flash 'Order failed' + redirect `/orders/checkout` |
| Order ID không tồn tại | HTTP 404 |
| Xem order của người khác | HTTP 403 |

---

## Redirect Rules

| Sự kiện | Redirect | Old input |
|---|---|---|
| Checkout thành công | `/orders/{new_id}` | Không |
| Checkout fail (validate) | `/orders/checkout` | Có |
| Checkout fail (stock) | `/cart` | Không |
| Checkout fail (DB) | `/orders/checkout` | Có |
| Cart rỗng | `/cart` | Không |
| Order not found | `errors/404` | Không |
| Order không thuộc về user | `errors/403` | Không |

---

## Done ✅

- [ ] GET `/orders/checkout` render form + order review từ cart session
- [ ] "Use my address" pre-fill shipping form (JS)
- [ ] POST `/orders/checkout` validate đầy đủ
- [ ] Verify stock từ DB trước khi tạo order
- [ ] Transaction: tạo order + details + trừ kho atomic
- [ ] Cart session xóa sau khi order thành công
- [ ] GET `/orders` liệt kê đúng orders của current user
- [ ] GET `/orders/{id}` hiển thị chi tiết đúng
- [ ] Ownership check: không xem được order của người khác (403)
- [ ] Status badge hiển thị đúng màu theo trạng thái
- [ ] Empty state khi chưa có đơn nào

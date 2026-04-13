# Module: Cart

## Objective
- Quản lý giỏ hàng hoàn toàn trong `$_SESSION['cart']` (không lưu DB)
- Thêm sản phẩm, cập nhật số lượng, xóa từng item, xóa toàn bộ
- Kiểm tra `stock_quantity` trước khi thêm/cập nhật
- Hiển thị số lượng item trên navbar (badge)
- Nút "Proceed to Checkout" chuyển sang OrderController

---

## Wireframe thiết kế giao diện

### Cart Page — `/cart`
```
┌─────────────────────────────────────────────────────────────────┐
│  [NAVBAR: Logo | Products | Cart(3) | Hi, John ▼]              │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  Shopping Cart (3 items)                                        │
│                                                                 │
│  ┌──────────────────────────────────┐ ┌──────────────────────┐ │
│  │  CART ITEMS                      │ │  ORDER SUMMARY       │ │
│  │──────────────────────────────────│ │──────────────────────│ │
│  │ [IMG] TR90 Frame    850,000₫     │ │  Subtotal    3,950K  │ │
│  │       Qty: [−][1][+]  [Remove]   │ │  Shipping    Free    │ │
│  │──────────────────────────────────│ │  ──────────────────  │ │
│  │ [IMG] Rayban Classic 2,200,000₫  │ │  Total    3,950,000₫ │ │
│  │       Qty: [−][1][+]  [Remove]   │ │                      │ │
│  │──────────────────────────────────│ │ [Proceed to Checkout]│ │
│  │ [IMG] Retro Fashion  900,000₫    │ │ (bg: #0ea5e9)        │ │
│  │       Qty: [−][2][+]  [Remove]   │ │                      │ │
│  │                                  │ │ [Continue Shopping]  │ │
│  │ [Clear Cart]                     │ │ (link style)         │ │
│  └──────────────────────────────────┘ └──────────────────────┘ │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

### Empty Cart State
```
┌─────────────────────────────────────────────────────────────────┐
│  [NAVBAR]                                                       │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│              🛒                                                  │
│        Your cart is empty                                       │
│     Start shopping to add items                                 │
│                                                                 │
│         [  Browse Products  ]                                   │
│         (bg: #0ea5e9)                                           │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

**Style notes:**
- Cart item row: `border-bottom: 1px solid --border-color`, padding `1rem 0`
- Product thumbnail: `60×60px`, border-radius `--border-radius-sm`
- Quantity control `[−][n][+]`: border `--border-color`, button `bg --light-bg`
- Remove link: `color #ef4444`, no underline, hover underline
- Order summary card: `bg #ffffff`, shadow `--shadow-sm`, sticky top
- Total: `color #0ea5e9`, font-weight 700, font-size 1.5rem
- Checkout button: `bg #0ea5e9`, full width, padding `0.875rem`

---

## Routes

| Method | URL | Handler | Auth |
|---|---|---|---|
| GET | `/cart` | `CartController::index` | Login required |
| POST | `/cart/add` | `CartController::add` | Login required |
| POST | `/cart/update` | `CartController::update` | Login required |
| POST | `/cart/remove` | `CartController::remove` | Login required |
| POST | `/cart/clear` | `CartController::clear` | Login required |

---

## UI Pages

### `views/cart/index.php`
- Flash message
- Nếu cart rỗng: empty state + link Browse Products
- Nếu có items:
  - Bảng items: thumbnail, product_name, đơn giá, qty control, subtotal/row, Remove
  - Button "Clear Cart"
  - Order summary: subtotal, shipping (free nếu > 500K), total
  - Button "Proceed to Checkout" → `/orders/checkout`
  - Link "Continue Shopping" → `/products`

---

## Data Processing Flow

### GET `/cart`
```
1. requireAuth()
2. $cart = $_SESSION['cart'] ?? []
3. Tính $subtotal = sum(item.price * item.quantity)
4. $shipping = $subtotal >= 500000 ? 0 : 30000
5. render('cart/index', [cart, subtotal, shipping, total])
```

### POST `/cart/add`
```
1. requireAuth()
2. $productId = (int)$_POST['product_id']
3. $quantity  = max(1, (int)$_POST['quantity'] ?? 1)
4. ProductModel::findById($productId)
   - not found hoặc status=0 → flash error + redirect('/products')
5. Kiểm tra stock_quantity:
   $currentQty = $_SESSION['cart'][$productId]['quantity'] ?? 0
   $newQty     = $currentQty + $quantity
   $newQty > product['stock_quantity']
   → flash error 'Not enough stock' + redirect back
6. Nếu product đã trong cart → cộng thêm quantity
   Nếu chưa có → thêm entry mới với: id, product_name, price, quantity, image_url
7. flash success '{product_name} added to cart!'
8. redirect('/cart')
```

### POST `/cart/update`
```
1. requireAuth()
2. $productId = (int)$_POST['product_id']
3. $quantity  = max(1, (int)$_POST['quantity'])
4. Sản phẩm có trong cart không?
   - không → flash error + redirect('/cart')
5. ProductModel::findById($productId) → lấy stock_quantity mới nhất
6. $quantity > stock_quantity → flash error 'Not enough stock' + redirect
7. Cập nhật $_SESSION['cart'][$productId]['quantity'] = $quantity
8. flash success 'Cart updated.'
9. redirect('/cart')
```

### POST `/cart/remove`
```
1. requireAuth()
2. $productId = (int)$_POST['product_id']
3. unset($_SESSION['cart'][$productId])
4. flash success 'Item removed.'
5. redirect('/cart')
```

### POST `/cart/clear`
```
1. requireAuth()
2. $_SESSION['cart'] = []
3. flash success 'Cart cleared.'
4. redirect('/cart')
```

---

## Validation

| Field | Rule | Error |
|---|---|---|
| `product_id` | cast int, > 0 | redirect products |
| `product_id` | tồn tại trong DB + status=1 | "Product not available." |
| `quantity` (add) | cast int, min 1 | default 1 |
| `quantity` (update) | cast int, min 1 | default 1 |
| `quantity` tổng | ≤ `stock_quantity` sản phẩm | "Only {n} items available in stock." |

---

## Database Interaction

**Bảng:** `products` (read-only từ Cart module)

| Action | Method | Mục đích |
|---|---|---|
| Kiểm tra product hợp lệ | `ProductModel::findById($id)` | Lấy `product_name, price, image_url, stock_quantity, status` |

> ⚠️ Cart data lưu **100% trong `$_SESSION['cart']`** — không có bảng cart trong DB.  
> Snapshot `price` lấy từ DB lúc add, không re-fetch mỗi request.  
> Khi checkout, Controller sẽ verify lại price và stock từ DB trước khi tạo order.

### Cấu trúc `$_SESSION['cart']`
```php
$_SESSION['cart'] = [
    5 => [
        'id'           => 5,
        'product_name' => 'TR90 Prescription Frame',
        'price'        => 850000.00,    // Snapshot tại thời điểm add
        'quantity'     => 1,
        'image_url'    => 'products/tr90_frame.jpg',
    ],
    3 => [
        'id'           => 3,
        'product_name' => 'Retro Fashion Glasses',
        'price'        => 450000.00,
        'quantity'     => 2,
        'image_url'    => 'products/retro_glasses.jpg',
    ],
];
```

---

## Permissions

| Route | Yêu cầu |
|---|---|
| Tất cả `/cart/*` | Login required |
| Chưa login → add to cart | redirect `/auth/login` + flash 'Please login to add items to cart.' |

---

## Error Handling

| Tình huống | Xử lý |
|---|---|
| `product_id` invalid | redirect `/products` |
| Product không tồn tại / disabled | flash error + redirect `/products` |
| Vượt quá `stock_quantity` | flash error 'Only {n} available' + redirect `/cart` |
| Update product không trong cart | flash error + redirect `/cart` |
| Remove product không trong cart | silent ignore + redirect `/cart` |

---

## Redirect Rules

| Sự kiện | Redirect | Old input |
|---|---|---|
| Add thành công | `/cart` | Không |
| Add thất bại (stock) | `/products/{id}` hoặc `/cart` | Không |
| Update thành công | `/cart` | Không |
| Remove thành công | `/cart` | Không |
| Clear thành công | `/cart` | Không |
| Chưa login | `/auth/login` | Không |

---

## Done ✅

- [ ] GET `/cart` hiển thị items, tính đúng subtotal/total
- [ ] Empty state hiển thị khi cart rỗng
- [ ] POST `/cart/add` thêm sản phẩm mới vào session
- [ ] POST `/cart/add` cộng dồn nếu sản phẩm đã có
- [ ] Kiểm tra `stock_quantity` khi add và update
- [ ] POST `/cart/update` cập nhật số lượng
- [ ] POST `/cart/remove` xóa item khỏi session
- [ ] POST `/cart/clear` xóa toàn bộ cart
- [ ] Cart badge trên navbar hiển thị đúng số lượng items
- [ ] Shipping free khi subtotal ≥ 500,000₫
- [ ] Nút "Proceed to Checkout" dẫn đến `/orders/checkout`
- [ ] Login required guard hoạt động

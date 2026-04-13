# Module: Admin — Orders + Customers + Consultations

## Objective
- **Orders:** Xem danh sách, xem chi tiết, cập nhật trạng thái đơn hàng
- **Customers:** Xem danh sách, xem profile, ban/unban tài khoản
- **Consultations:** Xem câu hỏi pending, trả lời, đánh dấu resolved

---

## Wireframe thiết kế giao diện

### Admin Orders — `/admin/orders`
```
┌───────────────────────────────────────────────────────────────┐
│  Orders Management                                            │
│  Filter: [All ▼]  Date: [____] to [____]  [Search]           │
│                                                               │
│  ┌────┬──────────┬─────────────┬───────────┬────────┬──────┐  │
│  │ #  │ Customer │ Date        │ Total     │ Status │Action│  │
│  ├────┼──────────┼─────────────┼───────────┼────────┼──────┤  │
│  │ 12 │ John Doe │ Jan 15,2025 │ 3,950,000₫│[pend.] │ View │  │
│  │ 11 │ Alice    │ Jan 10,2025 │ 2,200,000₫│[ship.] │ View │  │
│  │ 10 │ Bob      │ Dec 28,2024 │   850,000₫│[done]  │ View │  │
│  └────┴──────────┴─────────────┴───────────┴────────┴──────┘  │
│  Showing 1–10 of 128        « 1 [2] 3 ... 13 »                │
└───────────────────────────────────────────────────────────────┘
```

### Admin Order Detail — `/admin/orders/{id}`
```
┌───────────────────────────────────────────────────────────────┐
│  Order #12                          ← Back to Orders          │
│  ─────────────────────────────────────────────────────────    │
│  ┌──────────────────────────┐ ┌────────────────────────────┐  │
│  │  CUSTOMER INFO           │ │  UPDATE STATUS             │  │
│  │  Name: John Doe          │ │                            │  │
│  │  Email: john@gmail.com   │ │  Current: [pending]        │  │
│  │  Phone: 0901234567       │ │                            │  │
│  ├──────────────────────────┤ │  Change to:                │  │
│  │  SHIPPING INFO           │ │  [confirmed  ▼]            │  │
│  │  To: John Doe            │ │                            │  │
│  │  Phone: 0901234567       │ │  [Update Status]           │  │
│  │  Address: 123 Main St    │ │  (bg: #0ea5e9)             │  │
│  │  Note: Ring the bell     │ └────────────────────────────┘  │
│  └──────────────────────────┘                                 │
│  ─────────────────────────────────────────────────────────    │
│  ITEMS                                                        │
│  ┌────────────────────────────────────────────────────────┐   │
│  │ [IMG] TR90 Frame    850,000₫ × 1  =  850,000₫          │   │
│  │ [IMG] Rayban       2,200,000₫ × 1 = 2,200,000₫         │   │
│  │ [IMG] Retro Glasses  450,000₫ × 2 =   900,000₫         │   │
│  │                          Total:    3,950,000₫           │   │
│  └────────────────────────────────────────────────────────┘   │
└───────────────────────────────────────────────────────────────┘
```

### Admin Customers — `/admin/customers`
```
┌───────────────────────────────────────────────────────────────┐
│  Customers Management                                         │
│  Search: [_____________________]  [Search]                    │
│                                                               │
│  ┌───┬──────────────┬─────────────────┬────────┬──────────┐   │
│  │ # │ Name         │ Email           │ Status │ Action   │   │
│  ├───┼──────────────┼─────────────────┼────────┼──────────┤   │
│  │ 2 │ John Doe     │ john@gmail.com  │[active]│ Ban View │   │
│  │ 3 │ Alice Smith  │ alice@gmail.com │[active]│ Ban View │   │
│  │ 4 │ Bad User     │ bad@example.com │[banned]│Unban View│   │
│  └───┴──────────────┴─────────────────┴────────┴──────────┘   │
└───────────────────────────────────────────────────────────────┘
```

### Admin Consultations — `/admin/consultations`
```
┌───────────────────────────────────────────────────────────────┐
│  Consultations          Filter: [All ▼] [Pending] [Resolved]  │
│                                                               │
│  ┌─────────────────────────────────────────────────────────┐  │
│  │  #5 • Jan 15 • John Doe          [pending]  [Reply]     │  │
│  │  "What's the difference between TR90 and titanium..."    │  │
│  ├─────────────────────────────────────────────────────────┤  │
│  │  #4 • Jan 12 • Alice Smith       [resolved] [View]      │  │
│  │  "Do you offer prescription lens replacement?"          │  │
│  │  ↳ Admin: Yes, we offer lens replacement...             │  │
│  └─────────────────────────────────────────────────────────┘  │
└───────────────────────────────────────────────────────────────┘
```

### Admin Consultation Reply — `/admin/consultations/{id}`
```
┌───────────────────────────────────────────────────────────────┐
│  Consultation #5                    ← Back to Consultations   │
│  ─────────────────────────────────────────────────────────    │
│  From: John Doe (john@gmail.com)    Sent: Jan 15, 2025        │
│  Status: [pending]                                            │
│  ─────────────────────────────────────────────────────────    │
│  Question:                                                    │
│  ┌─────────────────────────────────────────────────────────┐  │
│  │ What's the difference between TR90 and titanium frames? │  │
│  └─────────────────────────────────────────────────────────┘  │
│                                                               │
│  Your Reply *                                                 │
│  ┌─────────────────────────────────────────────────────────┐  │
│  │                                                         │  │
│  │  TR90 is a nylon-based material that is more flexible...│  │
│  │                                                         │  │
│  └─────────────────────────────────────────────────────────┘  │
│                                                               │
│  [Cancel]                    [Send Reply & Mark Resolved]     │
│                              (bg: #0ea5e9)                    │
└───────────────────────────────────────────────────────────────┘
```

**Style notes:**
- Status badges: giống frontend (pending=amber, confirmed=blue, shipped=purple, completed=green, cancelled=red)
- Ban button: `color #ef4444`, border `#ef4444` / Unban: `color #16a34a`
- Admin reply block trong consultations list: `bg #f0f9ff`, border-left `4px solid #0ea5e9`
- "Send Reply" button: `bg #0ea5e9`
- Table row hover: `bg #f0f9ff`

---

## Routes

| Method | URL | Handler | Auth |
|---|---|---|---|
| GET | `/admin/orders` | `AdminOrderController::index` | Admin |
| GET | `/admin/orders/{id}` | `AdminOrderController::show` | Admin |
| POST | `/admin/orders/{id}/status` | `AdminOrderController::updateStatus` | Admin |
| GET | `/admin/customers` | `AdminCustomerController::index` | Admin |
| GET | `/admin/customers/{id}` | `AdminCustomerController::show` | Admin |
| POST | `/admin/customers/{id}/ban` | `AdminCustomerController::toggleBan` | Admin |
| GET | `/admin/consultations` | `AdminConsultationController::index` | Admin |
| GET | `/admin/consultations/{id}` | `AdminConsultationController::show` | Admin |
| POST | `/admin/consultations/{id}/reply` | `AdminConsultationController::reply` | Admin |

---

## UI Pages

### `views/admin/orders/index.php`
- Filter dropdown: All / pending / confirmed / shipped / completed / cancelled
- Table: id, customer full_name, order_date, total_amount, status badge, View link
- Pagination

### `views/admin/orders/detail.php`
- Customer info + Shipping info
- Update status form: dropdown (only valid next states) + Submit
- Items table: product_name, sale_price, quantity, line total
- Order total

### `views/admin/customers/index.php`
- Search by name/email
- Table: id, full_name, email, phone, created_at, status badge, Ban/Unban button, View link

### `views/admin/customers/detail.php`
- Profile: full_name, email, phone, address, created_at, role, status
- Order history table (read-only)
- Ban/Unban button

### `views/admin/consultations/index.php`
- Filter: All / Pending / Resolved
- List: id, sent_at, customer name, content preview, status, Reply/View link

### `views/admin/consultations/detail.php`
- Customer info + sent_at
- Question content (full)
- Reply textarea (nếu chưa resolved) / Hiển thị reply đã có (nếu resolved)
- Submit button

---

## Data Processing Flow

### POST `/admin/orders/{id}/status`
```
1. requireAdmin()
2. $id = (int)$segments[2]
3. OrderModel::findById($id) → not found → redirect('/admin/orders')
4. $newStatus = $_POST['status'] ?? ''
5. Validate: whitelist ['confirmed','shipped','completed','cancelled']
6. Validate transition hợp lệ:
   - pending    → confirmed, cancelled
   - confirmed  → shipped, cancelled
   - shipped    → completed, cancelled
   - completed  → (không đổi được)
   - cancelled  → (không đổi được)
   Invalid → flash error + redirect
7. OrderModel::updateStatus($id, $newStatus)
8. flash success 'Order status updated to {status}.'
9. redirect('/admin/orders/{id}')
```

### POST `/admin/customers/{id}/ban`
```
1. requireAdmin()
2. $id = (int)$segments[2]
3. CustomerModel::findById($id) → not found → redirect
4. Không được ban chính mình (id === currentUser.id) → flash error + redirect
5. Không được ban admin khác → flash error + redirect
6. $newStatus = customer['status'] === 'active' ? 'banned' : 'active'
7. CustomerModel::updateCustomerStatus($id, $newStatus)
8. flash success '{name} has been {banned/unbanned}.'
9. redirect('/admin/customers')
```

### POST `/admin/consultations/{id}/reply`
```
1. requireAdmin()
2. $id = (int)$segments[2]
3. ConsultationModel::findById($id) → not found → redirect
4. $reply = trim($_POST['reply'] ?? '')
5. Validate: empty($reply) → flash error + redirect
6. Validate: mb_strlen($reply) > 3000 → flash error + redirect
7. ConsultationModel::reply($id, $reply)
   → UPDATE consultations SET reply = ?, status = 'resolved' WHERE id = ?
8. flash success 'Reply sent and consultation marked as resolved.'
9. redirect('/admin/consultations')
```

---

## Validation

### Update order status
| Rule | Error |
|---|---|
| `status` phải trong whitelist | "Invalid status." |
| Transition không hợp lệ (VD: completed → anything) | "Cannot change status from {current}." |

### Ban/Unban customer
| Rule | Error |
|---|---|
| `customer_id` tồn tại | redirect |
| Không tự ban mình | "You cannot ban your own account." |
| Không ban admin khác | "Cannot ban admin accounts." |

### Reply consultation
| Field | Rule | Error |
|---|---|---|
| `reply` | Required | "Reply cannot be empty." |
| `reply` | Max 3000 ký tự | "Reply is too long (max 3000 characters)." |

---

## Database Interaction

**AdminOrderController:**
| Action | Method |
|---|---|
| Danh sách orders (có filter, pagination) | `OrderModel::getAdminList($filters, $page)` — JOIN customers |
| Chi tiết order + items | `OrderModel::getOrderWithDetails($id)` |
| Cập nhật status | `OrderModel::updateStatus($id, $status)` |

**AdminCustomerController:**
| Action | Method |
|---|---|
| Danh sách customers (search) | `CustomerModel::getAdminList($keyword, $page)` |
| Xem customer + orders | `CustomerModel::findById($id)` + `OrderModel::getOrdersByCustomer($id)` |
| Ban/Unban | `CustomerModel::updateCustomerStatus($id, $status)` |

**AdminConsultationController:**
| Action | Method |
|---|---|
| Danh sách (có filter) | `ConsultationModel::getAdminList($status, $page)` — JOIN customers |
| Chi tiết | `ConsultationModel::findById($id)` + customer info |
| Gửi reply + resolve | `ConsultationModel::reply($id, $reply)` |

---

## Permissions

- Tất cả routes: Admin only (`requireAdmin()` trong constructor)
- Admin không tự ban mình hoặc ban admin khác
- Không thể đổi status đơn hàng đã `completed` hoặc `cancelled`

---

## Error Handling

| Tình huống | Xử lý |
|---|---|
| Order/Customer/Consultation ID không tồn tại | redirect list page |
| Status transition không hợp lệ | flash error + redirect detail |
| Tự ban mình | flash error + redirect |
| Reply consultation đã resolved | vẫn cho phép cập nhật reply |
| DB error | flash 'Operation failed' + redirect |

---

## Redirect Rules

| Sự kiện | Redirect | Old input |
|---|---|---|
| Update order status thành công | `/admin/orders/{id}` | Không |
| Update order status thất bại | `/admin/orders/{id}` | Không |
| Ban/Unban thành công | `/admin/customers` | Không |
| Reply sent | `/admin/consultations` | Không |
| Reply fail (validate) | `/admin/consultations/{id}` | Có |

---

## Done ✅

**Orders:**
- [ ] List orders với filter theo status + pagination
- [ ] Detail hiển thị đầy đủ: customer, shipping, items, total
- [ ] Update status: dropdown chỉ show valid next states
- [ ] Status transition rules được enforce
- [ ] Status badge đúng màu

**Customers:**
- [ ] List customers với search theo name/email + pagination
- [ ] Customer detail hiển thị profile + order history
- [ ] Ban/Unban hoạt động đúng
- [ ] Không tự ban mình, không ban admin khác

**Consultations:**
- [ ] List với filter All/Pending/Resolved + pagination
- [ ] Detail hiển thị full question + reply (nếu có)
- [ ] Reply form validate + insert + mark resolved
- [ ] Không cho reply rỗng

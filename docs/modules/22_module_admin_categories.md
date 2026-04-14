# Module: Admin — Categories (Glasses Categories CRUD)

## Objective
- Quản lý danh mục kính: Prescription, Sunglasses, Fashion, Reading...
- CRUD đầy đủ: tạo mới, chỉnh sửa tên/mô tả, toggle ẩn/hiện, xóa
- Không cho xóa danh mục đang có sản phẩm (kể cả sản phẩm đã ngừng KD)
- Hiển thị số lượng sản phẩm đang bán theo từng danh mục

---

## Wireframe thiết kế giao diện

### Category List + Inline Create Form — `/admin/categories`
```
┌───────────────────────────────────────────────────────────────┐
│  [TOPBAR]                                                     │
├──────────┬────────────────────────────────────────────────────┤
│ SIDEBAR  │                                                    │
│ ...      │  Categories Management                             │
│►Categories│                                                   │
│          │  ┌──────────────────────────────────────────────┐  │
│          │  │  Add New Category                            │  │
│          │  │  ─────────────────────────────────────────   │  │
│          │  │  Category Name *        Status               │  │
│          │  │  [___________________]  ● Visible ○ Hidden   │  │
│          │  │  Description (optional)                      │  │
│          │  │  [___________________________________]        │  │
│          │  │                       [  Add Category  ]     │  │
│          │  └──────────────────────────────────────────────┘  │
│          │                                                    │
│          │  ┌────┬──────────────────┬──────────┬──────┬────┐  │
│          │  │ #  │ Category Name    │ Products │Status│Act.│  │
│          │  ├────┼──────────────────┼──────────┼──────┼────┤  │
│          │  │  1 │ Prescription     │    24    │[Show]│✏️🗑│  │
│          │  │  2 │ Sunglasses       │    18    │[Show]│✏️🗑│  │
│          │  │  3 │ Fashion Glasses  │    12    │[Hide]│✏️🗑│  │
│          │  │  4 │ Reading Glasses  │     0    │[Show]│✏️🗑│  │
│          │  └────┴──────────────────┴──────────┴──────┴────┘  │
│          │                                                    │
└──────────┴────────────────────────────────────────────────────┘
```

### Edit Category — inline form thay thế row (hoặc trang riêng)
```
┌───────────────────────────────────────────────────────────────┐
│  Edit Category #1                     ← Back to Categories    │
│  ─────────────────────────────────────────────────────────    │
│                                                               │
│  Category Name *                                              │
│  ┌─────────────────────────────────────────────────────────┐  │
│  │ Prescription Glasses                                    │  │
│  └─────────────────────────────────────────────────────────┘  │
│                                                               │
│  Description                                                  │
│  ┌─────────────────────────────────────────────────────────┐  │
│  │ Glasses for nearsighted and farsighted                  │  │
│  └─────────────────────────────────────────────────────────┘  │
│                                                               │
│  Status                                                       │
│  ● Visible (shown in product filter)                          │
│  ○ Hidden  (hidden from customers)                            │
│                                                               │
│  [  Cancel  ]                         [  Save Changes  ]     │
│                                       (bg: #0ea5e9)          │
└───────────────────────────────────────────────────────────────┘
```

**Style notes:**
- Add form card: `bg #ffffff`, shadow `--shadow-sm`, border-radius `--border-radius-md`
- Table: `bg #ffffff`, row hover `bg #f0f9ff`
- [Show] badge: `bg #dcfce7`, text `#16a34a` / [Hide] badge: `bg #f1f5f9`, text `#64748b`
- Product count: `font-weight 600`, `color #0ea5e9` nếu > 0, `color --text-muted` nếu = 0
- Delete icon disabled (grayed): khi category có products
- Tooltip on disabled delete: "Cannot delete: has {n} products"

---

## Routes

| Method | URL | Handler | Auth |
|---|---|---|---|
| GET | `/admin/categories` | `AdminCategoryController::index` | Admin |
| POST | `/admin/categories/create` | `AdminCategoryController::create` | Admin |
| GET | `/admin/categories/{id}/edit` | `AdminCategoryController::editForm` | Admin |
| POST | `/admin/categories/{id}/edit` | `AdminCategoryController::update` | Admin |
| POST | `/admin/categories/{id}/delete` | `AdminCategoryController::delete` | Admin |
| POST | `/admin/categories/{id}/toggle` | `AdminCategoryController::toggleStatus` | Admin |

---

## UI Pages

### `views/admin/categories/index.php`
- Flash message
- Inline create form: `category_name` (text), `description` (textarea), `status` (radio), Submit
- Old input restore khi create fail
- Table columns: id, category_name, product_count, status badge, Edit link, Delete button
- Delete button disabled (greyed out + tooltip) nếu `product_count > 0`

### `views/admin/categories/edit.php`
- Flash message
- Form pre-filled: `category_name`, `description`, `status`
- Cancel → `/admin/categories`
- Save → POST `/admin/categories/{id}/edit`

---

## Data Processing Flow

### GET `/admin/categories`
```
1. requireAdmin()
2. GlassesCategoryModel::getAllWithProductCount()
   → SELECT gc.*, COUNT(p.id) AS product_count
     FROM glasses_categories gc
     LEFT JOIN products p ON gc.id = p.category_id
     GROUP BY gc.id
     ORDER BY gc.id ASC
3. Session::getOldInput() → $oldInput (cho inline create form)
4. render('admin/categories/index', [categories, oldInput], 'admin')
```

### POST `/admin/categories/create`
```
1. requireAdmin()
2. $name   = trim($_POST['category_name'] ?? '')
3. $desc   = trim($_POST['description']  ?? '')
4. $status = (int)($_POST['status'] ?? 1)
5. Validate:
   - empty($name) → flash error + setOldInput + redirect('/admin/categories')
   - mb_strlen($name) < 2 || > 50 → flash error + setOldInput + redirect
6. GlassesCategoryModel::findByName($name)
   - tồn tại → flash 'Category name already exists.' + setOldInput + redirect
7. GlassesCategoryModel::create(['category_name'=>$name, 'description'=>$desc, 'status'=>$status])
   - false → flash 'Failed to create category.' + redirect
8. flash success 'Category "{name}" created successfully!'
9. redirect('/admin/categories')
```

### GET `/admin/categories/{id}/edit`
```
1. requireAdmin()
2. $id = (int)$segments[2]
3. GlassesCategoryModel::findById($id) → not found → redirect('/admin/categories')
4. render('admin/categories/edit', [category, oldInput], 'admin')
```

### POST `/admin/categories/{id}/edit`
```
1. requireAdmin()
2. $id = (int)$segments[2]
3. GlassesCategoryModel::findById($id) → not found → redirect
4. $name   = trim($_POST['category_name'] ?? '')
5. $desc   = trim($_POST['description']  ?? '')
6. $status = (int)($_POST['status'] ?? 1)
7. Validate (giống create)
8. Check unique: tên trùng với category khác (id !== $id)
   → flash 'Category name already exists.' + setOldInput + redirect edit
9. GlassesCategoryModel::update($id, [...])
10. flash success 'Category updated successfully!'
11. redirect('/admin/categories')
```

### POST `/admin/categories/{id}/delete`
```
1. requireAdmin()
2. $id = (int)$segments[2]
3. GlassesCategoryModel::findById($id) → not found → redirect
4. GlassesCategoryModel::countProducts($id)
   → SELECT COUNT(*) FROM products WHERE category_id = ?
   - count > 0 → flash error 'Cannot delete: category has {n} products.' + redirect
5. GlassesCategoryModel::deleteById($id)
6. flash success 'Category deleted.'
7. redirect('/admin/categories')
```

### POST `/admin/categories/{id}/toggle`
```
1. requireAdmin()
2. $id = (int)$segments[2]
3. GlassesCategoryModel::findById($id) → not found → redirect
4. $newStatus = $category['status'] == 1 ? 0 : 1
5. GlassesCategoryModel::updateStatus($id, $newStatus)
6. flash success 'Category is now ' . ($newStatus ? 'visible' : 'hidden') . '.'
7. redirect('/admin/categories')
```

---

## Validation

### Create & Edit
| Field | Rule | Error |
|---|---|---|
| `category_name` | Required | "Category name is required." |
| `category_name` | Length 2–50 | "Name must be 2–50 characters." |
| `category_name` | Unique trong DB (trừ chính nó khi edit) | "Category name already exists." |
| `description` | Optional, max 500 ký tự | Trim, không validate |
| `status` | Whitelist: 0 hoặc 1 | Default 1 nếu không có |

### Delete
| Rule | Error |
|---|---|
| Category có products (kể cả status=0) | "Cannot delete: category has {n} products. Please reassign or delete products first." |

---

## Database Interaction

**Bảng:** `glasses_categories`, `products`

| Action | Method | SQL |
|---|---|---|
| Lấy tất cả + product count | `getAllWithProductCount()` | LEFT JOIN products GROUP BY id |
| Tìm theo id | `findById(int $id)` | WHERE id = ? |
| Tìm theo tên | `findByName(string $name)` | WHERE category_name = ? |
| Đếm products của category | `countProducts(int $id)` | COUNT(*) WHERE category_id = ? |
| Tạo mới | `create(array $data)` | INSERT INTO glasses_categories |
| Cập nhật | `update(int $id, array $data)` | UPDATE glasses_categories SET ... WHERE id = ? |
| Toggle status | `updateStatus(int $id, int $status)` | UPDATE SET status = ? WHERE id = ? |
| Xóa | `deleteById(int $id)` | DELETE WHERE id = ? (chỉ khi count = 0) |

---

## Permissions

- Tất cả routes: Admin only
- `requireAdmin()` trong `AdminCategoryController::__construct()`

---

## Error Handling

| Tình huống | Xử lý |
|---|---|
| Category ID không tồn tại | redirect `/admin/categories` |
| Validate fail (create) | flash + setOldInput → redirect `/admin/categories` (inline form) |
| Validate fail (edit) | flash + setOldInput → redirect `/admin/categories/{id}/edit` |
| Tên trùng | flash + setOldInput + redirect |
| Xóa category có products | flash error + redirect `/admin/categories` |
| DB error | flash 'Operation failed.' + redirect |

---

## Redirect Rules

| Sự kiện | Redirect | Old input |
|---|---|---|
| Create thành công | `/admin/categories` | Không |
| Create thất bại | `/admin/categories` | Có (inline form) |
| Edit thành công | `/admin/categories` | Không |
| Edit thất bại | `/admin/categories/{id}/edit` | Có |
| Delete thành công | `/admin/categories` | Không |
| Delete thất bại (có products) | `/admin/categories` | Không |
| Toggle status | `/admin/categories` | Không |

---

## Done ✅

- [ ] GET `/admin/categories` hiển thị list + inline create form
- [ ] Create: validate, check unique, insert DB
- [ ] Edit form: pre-fill đúng giá trị hiện tại
- [ ] Edit: validate, check unique (trừ chính nó), update DB
- [ ] Delete: block nếu category có products (kể cả đã disable)
- [ ] Toggle status: visible ↔ hidden hoạt động
- [ ] Product count hiển thị đúng trong table
- [ ] Old input restore cho inline create form khi lỗi
- [ ] Admin guard hoạt động

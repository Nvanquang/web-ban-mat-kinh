# Module: Admin — Dashboard + Products + Categories

## Objective
- Dashboard: tổng quan doanh thu, đơn hàng, khách hàng, sản phẩm hot
- Products CRUD: thêm/sửa/xóa sản phẩm, upload ảnh, toggle status
- Categories CRUD: thêm/sửa/xóa danh mục kính

---

## Wireframe thiết kế giao diện

### Admin Layout (shared)
```
┌──────────────────────────────────────────────────────────────────┐
│  [TOPBAR: ☰ EyeGlass Admin        🔔  Hi, Admin ▼  Logout]      │
├──────────┬───────────────────────────────────────────────────────┤
│ SIDEBAR  │  MAIN CONTENT AREA                                    │
│          │                                                       │
│ 📊 Dashboard│                                                    │
│ 👓 Products │                                                    │
│ 🏷️ Categories│                                                   │
│ 📦 Orders  │                                                     │
│ 👥 Customers│                                                    │
│ 💬 Consult │                                                     │
│          │                                                       │
└──────────┴───────────────────────────────────────────────────────┘
```
- Sidebar: `bg #0f172a` (dark-bg), text `#94a3b8`, active item `bg #0ea5e9`, text white
- Topbar: `bg #ffffff`, border-bottom `--border-color`, shadow `--shadow-sm`
- Main area: `bg #f8fafc` (light-bg)

### Dashboard — `/admin`
```
┌───────────────────────────────────────────────────────────────┐
│  Dashboard                                                    │
│                                                               │
│  ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────┐        │
│  │ 💰        │ │ 📦        │ │ 👥        │ │ 👓        │       │
│  │ Revenue  │ │ Orders   │ │ Customers│ │ Products │       │
│  │ 45.2M ₫  │ │ 128      │ │ 89       │ │ 32       │       │
│  │ This month│ │ Total    │ │ Active   │ │ Selling  │       │
│  └──────────┘ └──────────┘ └──────────┘ └──────────┘        │
│                                                               │
│  ┌──────────────────────────┐ ┌───────────────────────────┐  │
│  │ Recent Orders            │ │ Top Selling Products      │  │
│  │──────────────────────────│ │───────────────────────────│  │
│  │ #12 John  3,950K pending │ │ 1. Rayban Classic   89 sold│  │
│  │ #11 Alice 2,200K shipped │ │ 2. TR90 Frame       72 sold│  │
│  │ #10 Bob   850K completed │ │ 3. Retro Glasses    55 sold│  │
│  │          [View All →]    │ │                           │  │
│  └──────────────────────────┘ └───────────────────────────┘  │
│                                                               │
│  ┌──────────────────────────────────────────────────────┐    │
│  │  Pending Consultations (3)          [View All →]     │    │
│  │  ● Jan 15 - John: "TR90 vs titanium?"                │    │
│  │  ● Jan 14 - Alice: "Lens replacement?"               │    │
│  └──────────────────────────────────────────────────────┘    │
└───────────────────────────────────────────────────────────────┘
```

### Product List — `/admin/products`
```
┌───────────────────────────────────────────────────────────────┐
│  Products                          [+ Add Product]            │
│  Search: [___________] Filter: [All ▼]  [Search]             │
│                                                               │
│  ┌───┬──────────────────┬───────────┬──────┬────────┬──────┐  │
│  │ # │ Product          │ Price     │Stock │ Status │Action│  │
│  ├───┼──────────────────┼───────────┼──────┼────────┼──────┤  │
│  │[✓]│[img] TR90 Frame  │ 850,000₫  │  50  │[Active]│✏️ 🗑│  │
│  │[✓]│[img] Rayban      │2,200,000₫ │  30  │[Active]│✏️ 🗑│  │
│  │[ ]│[img] Retro Frame │  450,000₫ │   0  │[Off]   │✏️ 🗑│  │
│  └───┴──────────────────┴───────────┴──────┴────────┴──────┘  │
│  Showing 1–10 of 32      « 1 [2] 3 4 »                        │
└───────────────────────────────────────────────────────────────┘
```

### Product Create/Edit Form — `/admin/products/create` hoặc `/admin/products/{id}/edit`
```
┌───────────────────────────────────────────────────────────────┐
│  Add New Product                                              │
│  ─────────────────────────────────────────────────────────    │
│  ┌──────────────────────────────┐ ┌─────────────────────┐    │
│  │  Basic Info                  │ │  Image & Status      │    │
│  │                              │ │                     │    │
│  │  Product Name *              │ │  [Current Image]    │    │
│  │  [_________________________] │ │  or placeholder     │    │
│  │                              │ │                     │    │
│  │  Category *                  │ │  [Choose File]      │    │
│  │  [Select Category ▼]         │ │  JPG PNG WEBP <2MB  │    │
│  │                              │ │                     │    │
│  │  Price *         Old Price   │ │  Status             │    │
│  │  [___________]  [__________] │ │  ● Selling          │    │
│  │                              │ │  ○ Discontinued     │    │
│  │  Stock Quantity              │ │                     │    │
│  │  [___________]               │ └─────────────────────┘    │
│  │                              │                            │
│  │  Description                 │                            │
│  │  [_________________________] │                            │
│  │  [                         ] │                            │
│  │  [_________________________] │                            │
│  └──────────────────────────────┘                            │
│                                                               │
│  [Cancel]                           [Save Product]           │
└───────────────────────────────────────────────────────────────┘
```

**Style notes (Admin):**
- Stat cards: `bg #ffffff`, border-left `4px solid #0ea5e9`, shadow `--shadow-sm`
- Table: `bg #ffffff`, header `bg #f8fafc`, row hover `bg #f0f9ff`
- [Active] badge: `bg #dcfce7`, text `#16a34a`
- [Off/Discontinued] badge: `bg #fee2e2`, text `#dc2626`
- [+ Add Product] button: `bg #0ea5e9`
- Edit icon: `color #0ea5e9` / Delete icon: `color #ef4444`
- Save button: `bg #0ea5e9` / Cancel: `bg --border-color`

---

## Routes

| Method | URL | Handler | Auth |
|---|---|---|---|
| GET | `/admin` | `AdminDashboardController::index` | Admin |
| GET | `/admin/products` | `AdminProductController::index` | Admin |
| GET | `/admin/products/create` | `AdminProductController::createForm` | Admin |
| POST | `/admin/products/create` | `AdminProductController::create` | Admin |
| GET | `/admin/products/{id}/edit` | `AdminProductController::editForm` | Admin |
| POST | `/admin/products/{id}/edit` | `AdminProductController::update` | Admin |
| POST | `/admin/products/{id}/delete` | `AdminProductController::delete` | Admin |
| GET | `/admin/categories` | `AdminCategoryController::index` | Admin |
| POST | `/admin/categories/create` | `AdminCategoryController::create` | Admin |
| POST | `/admin/categories/{id}/edit` | `AdminCategoryController::update` | Admin |
| POST | `/admin/categories/{id}/delete` | `AdminCategoryController::delete` | Admin |

---

## UI Pages

### `views/admin/dashboard/index.php`
- 4 stat cards: revenue this month, total orders, active customers, selling products
- Recent orders table (last 5): id, customer name, total, status, link
- Top 5 selling products: rank, name, sold count
- Pending consultations list (max 3): date, customer, preview

### `views/admin/products/index.php`
- Search input + category filter dropdown
- Products table: thumbnail, product_name, price (formatted), stock_quantity, status badge, Edit/Delete actions
- Pagination
- Empty state

### `views/admin/products/create.php` & `edit.php`
- product_name (text, required)
- category_id (select, required)
- price (number, required)
- old_price (number, optional)
- stock_quantity (number, required)
- description (textarea)
- image_url (file input; edit page hiển thị ảnh hiện tại)
- status (radio: 1=Selling / 0=Discontinued)

### `views/admin/categories/index.php`
- Inline create form ở đầu trang
- Table: id, category_name, product count, status toggle, Edit/Delete
- Edit mở modal hoặc inline form

---

## Data Processing Flow

### Dashboard
```
1. requireAdmin()
2. OrderModel::getTotalRevenueThisMonth()
3. OrderModel::countByStatus()           → pending, confirmed, shipped...
4. CustomerModel::countActive()
5. ProductModel::countSelling()
6. OrderModel::getRecentOrders(limit=5)
7. ProductModel::getTopSellingProducts(limit=5)
8. ConsultationModel::getPending(limit=3)
9. render('admin/dashboard/index', [...], 'admin')
```

### POST `/admin/products/create`
```
1. requireAdmin()
2. Đọc $_POST + $_FILES['image_url']
3. Validate (xem Validation)
4. Nếu có file upload:
   handleImageUpload($_FILES['image_url'], 'products')
   → false → flash error + redirect create
5. ProductModel::create($data)
   → false → flash error + redirect create
6. flash success 'Product added successfully!'
7. redirect('/admin/products')
```

### POST `/admin/products/{id}/edit`
```
1. requireAdmin()
2. ProductModel::findById($id) → not found → redirect('/admin/products')
3. Đọc $_POST + $_FILES (nếu có upload ảnh baru)
4. Validate
5. Nếu có file upload baru → handleImageUpload() → xóa ảnh cũ nếu tồn tại
6. ProductModel::update($id, $data)
7. flash success + redirect('/admin/products')
```

### POST `/admin/products/{id}/delete`
```
1. requireAdmin()
2. ProductModel::findById($id) → not found → redirect
3. Kiểm tra: sản phẩm có trong order_details chưa?
   - Có → ProductModel::softDelete($id) (set status=0)
   - Chưa → ProductModel::deleteById($id) + xóa file ảnh
4. flash success + redirect('/admin/products')
```

---

## Validation

### Product create/edit
| Field | Rule | Error |
|---|---|---|
| `product_name` | Required | "Product name is required." |
| `product_name` | Length 2–100 | "Name must be 2–100 characters." |
| `category_id` | Required, > 0 | "Please select a category." |
| `price` | Required, > 0 | "Price must be greater than 0." |
| `old_price` | Optional; nếu có: > price | "Old price must be greater than current price." |
| `stock_quantity` | Required, ≥ 0 | "Stock quantity cannot be negative." |
| `image_url` (file) | Optional; nếu có: MIME + size | "Only JPG/PNG/WEBP under 2MB." |
| `status` | Whitelist: 0 hoặc 1 | Default 1 |

### Category create/edit
| Field | Rule | Error |
|---|---|---|
| `category_name` | Required | "Category name is required." |
| `category_name` | Length 2–50 | "Name must be 2–50 characters." |
| `category_name` | Unique (DB) | "Category name already exists." |

---

## Database Interaction

**Bảng:** `products`, `glasses_categories`, `order_details`, `orders`, `customers`, `consultations`

| Action | Method |
|---|---|
| Thống kê dashboard | `OrderModel::getTotalRevenueThisMonth()`, `countByStatus()`, `getRecentOrders()` |
| Top products | `ProductModel::getTopSellingProducts(5)` |
| Pending consultations | `ConsultationModel::getPending(3)` |
| Danh sách products (admin, có search) | `ProductModel::getAdminList($filters, $page)` |
| Tạo/sửa/xóa product | `create()`, `update()`, `softDelete()`, `deleteById()` |
| Danh sách categories | `GlassesCategoryModel::getAll()` |
| Tạo/sửa/xóa category | `create()`, `update()`, `deleteById()` |
| Count products per category | JOIN trong `GlassesCategoryModel::getAllWithCount()` |

---

## Permissions

- Tất cả route `/admin/*` đều yêu cầu `role = 'admin'`
- `requireAdmin()` gọi trong `__construct()` của từng AdminController
- Nếu chưa login → redirect `/auth/login`
- Nếu login nhưng không phải admin → HTTP 403

---

## Error Handling

| Tình huống | Xử lý |
|---|---|
| Product not found khi edit/delete | redirect `/admin/products` |
| Validate fail | flash + setOldInput + redirect form |
| Image upload fail | flash error + redirect form (không lưu product) |
| DB error | flash 'Operation failed' + redirect |
| Xóa category có sản phẩm | flash warning 'Cannot delete: category has products.' |

---

## Redirect Rules

| Sự kiện | Redirect | Old input |
|---|---|---|
| Create product thành công | `/admin/products` | Không |
| Create product thất bại | `/admin/products/create` | Có |
| Update product thành công | `/admin/products` | Không |
| Update product thất bại | `/admin/products/{id}/edit` | Có |
| Delete product | `/admin/products` | Không |
| Create/update category | `/admin/categories` | Không nếu OK, Có nếu fail |

---

## Done ✅

- [ ] Dashboard hiển thị đúng 4 stat cards
- [ ] Recent orders, top products, pending consultations đúng
- [ ] Product list với search + category filter + pagination
- [ ] Create product: validate, upload ảnh, insert DB
- [ ] Edit product: pre-fill form, upload ảnh mới (optional), update DB
- [ ] Delete: soft delete nếu có trong orders, hard delete nếu chưa
- [ ] Category list với product count
- [ ] Category CRUD (create/edit/delete)
- [ ] Không xóa được category đang có sản phẩm
- [ ] Admin layout (sidebar active state đúng theo URL)
- [ ] `requireAdmin()` guard hoạt động trên tất cả routes

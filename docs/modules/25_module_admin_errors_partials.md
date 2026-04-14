# Module: Admin — Error Pages & Shared Partials

## Objective
- Chuẩn hóa các trang lỗi 404 / 403 / 500 dùng chung cho cả frontend và admin
- Định nghĩa các partial view tái sử dụng: navbar, footer, admin sidebar, admin topbar
- Đảm bảo mọi trang lỗi trả về HTTP status code đúng
- Không cần controller riêng — render trực tiếp từ Base Controller hoặc Router

---

## Wireframe thiết kế giao diện

### 404 Page
```
┌─────────────────────────────────────────────────────────────────┐
│  [NAVBAR: Logo | Products | Cart | Login]                       │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│                    404                                          │
│              (font-size: 8rem, color: #0ea5e9)                  │
│                                                                 │
│           Page Not Found                                        │
│     The page you're looking for doesn't exist                   │
│           or has been moved.                                    │
│                                                                 │
│            [  Go to Homepage  ]                                 │
│            (bg: #0ea5e9)                                        │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

### 403 Page
```
┌─────────────────────────────────────────────────────────────────┐
│  [NAVBAR]                                                       │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│                    403                                          │
│              (font-size: 8rem, color: #f59e0b)                  │
│                                                                 │
│              Access Denied                                      │
│      You don't have permission to view this page.               │
│                                                                 │
│            [  Go Back  ]    [  Homepage  ]                      │
│            (secondary)      (bg: #0ea5e9)                       │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

### 500 Page
```
┌─────────────────────────────────────────────────────────────────┐
│  [NAVBAR]                                                       │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│                    500                                          │
│              (font-size: 8rem, color: #ef4444)                  │
│                                                                 │
│           Something Went Wrong                                  │
│    We're working to fix this. Please try again later.           │
│                                                                 │
│            [  Go to Homepage  ]                                 │
│            (bg: #0ea5e9)                                        │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

### Frontend Navbar Partial
```
┌─────────────────────────────────────────────────────────────────┐
│ 👓 EyeGlass Shop     Products    [🔍 Search...]   🛒(3)  Login  │
│ (color: #0ea5e9)                                                │
└─────────────────────────────────────────────────────────────────┘
```

### Admin Sidebar Partial
```
┌───────────────┐
│ 👓 EyeGlass   │  ← Logo, bg #0f172a
│ Admin         │
│───────────────│
│ 📊 Dashboard  │  ← active: bg #0ea5e9, text white
│ 👓 Products   │  ← inactive: text #94a3b8, hover bg #1e293b
│ 🏷️ Categories│
│ 📦 Orders     │
│ 👥 Customers  │
│ 💬 Consult.   │
│ 📈 Reports    │
│───────────────│
│ ⚙️ Profile    │
│ 🚪 Logout     │
└───────────────┘
```

**Style notes:**
- Error pages: centered, `min-height: 60vh`, full layout (có navbar)
- Error number: `font-size: 8rem`, `font-weight: 700`, `line-height: 1`
- Error message: `color --text-muted`, `font-size: 1.1rem`
- Sidebar width: `260px`, fixed height 100vh, overflow-y auto
- Sidebar active item: `bg #0ea5e9`, `border-radius --border-radius-sm`, `margin: 2px 8px`
- Topbar height: `64px`, `bg #ffffff`, `border-bottom: 1px solid --border-color`

---

## Routes

Không có route riêng — các trang lỗi được render từ Base Controller:

```php
// Base Controller
protected function notFound(): void {
    http_response_code(404);
    $this->render('errors/404', ['title' => 'Page Not Found']);
    exit;
}

protected function forbidden(): void {
    http_response_code(403);
    $this->render('errors/403', ['title' => 'Access Denied']);
    exit;
}

// Global exception handler trong index.php
set_exception_handler(function (Throwable $e) {
    error_log($e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    http_response_code(500);
    include ROOT_PATH . '/app/views/errors/500.php'; // Direct include, không qua layout
    exit;
});
```

---

## File Structure

```
app/views/
├── errors/
│   ├── 404.php
│   ├── 403.php
│   └── 500.php
│
├── partials/                   ← Frontend partials
│   ├── navbar.php
│   └── footer.php
│
└── admin/
    └── partials/               ← Admin partials
        ├── topbar.php
        └── sidebar.php
```

---

## Partial: `partials/navbar.php`

**Dữ liệu cần có từ layout (biến tự động qua extract):**
- `$currentUser` — null nếu chưa login
- `$_SESSION['cart']` — đếm số items

```
Hiển thị:
- Logo + App name (link về /)
- Link "Products" → /products
- Search form (GET /products?keyword=...)
- Cart icon + badge (count items trong session)
- Nếu chưa login: "Login" link → /auth/login
- Nếu đã login: "Hi, {full_name} ▼" dropdown
    → My Orders (/orders)
    → Consultations (/consultations)
    → Logout (/auth/logout)
- Nếu là admin: thêm "Admin Panel" → /admin
```

**Active state:** highlight link đang active dựa vào `$_SERVER['REQUEST_URI']`

---

## Partial: `partials/footer.php`

```
Hiển thị:
- Logo + tagline
- Links: Products, About, Contact
- Copyright © {year} EyeGlass Shop
```

---

## Partial: `admin/partials/sidebar.php`

**Active state logic:**
```php
// Xác định active item dựa vào URI
$uri = $_SERVER['REQUEST_URI'];
$isActive = fn(string $path) => str_starts_with($uri, BASE_PATH . $path)
    ? 'active' : '';
```

**Menu items:**
| Icon | Label | URL | Active prefix |
|---|---|---|---|
| 📊 | Dashboard | `/admin` | `/admin` (exact) |
| 👓 | Products | `/admin/products` | `/admin/products` |
| 🏷️ | Categories | `/admin/categories` | `/admin/categories` |
| 📦 | Orders | `/admin/orders` | `/admin/orders` |
| 👥 | Customers | `/admin/customers` | `/admin/customers` |
| 💬 | Consultations | `/admin/consultations` | `/admin/consultations` |
| 📈 | Reports | `/admin/reports` | `/admin/reports` |
| --- | --- | --- | --- |
| ⚙️ | My Profile | `/admin/profile` | `/admin/profile` |
| 🚪 | Logout | `/auth/logout` | — |

---

## Partial: `admin/partials/topbar.php`

```
Hiển thị:
- ☰ Toggle sidebar button (mobile)
- Page title (optional — dùng $title)
- Right side: 🔔 (pending consultations count badge) | Hi, {full_name} ▼
    → My Profile (/admin/profile)
    → View Site (/), opens new tab
    → Logout (/auth/logout)
```

**Notification badge:** `ConsultationModel::countPending()` — chỉ query 1 lần trong layout

---

## UI Pages

### `views/errors/404.php`
- Include layout `main` (có navbar/footer)
- Hiển thị: "404", "Page Not Found", message, button Go to Homepage

### `views/errors/403.php`
- Include layout `main`
- Hiển thị: "403", "Access Denied", message, Go Back + Homepage buttons

### `views/errors/500.php`
- **Không** include layout (layout có thể lỗi)
- HTML tĩnh minimal: error number, message, link về /
- Không phụ thuộc bất kỳ PHP variable nào

---

## Data Processing Flow

### Error pages
```
Controller gọi:
    $this->notFound()    → http_response_code(404) → render errors/404
    $this->forbidden()   → http_response_code(403) → render errors/403

Global handler:
    Throwable caught     → http_response_code(500) → include errors/500.php (raw)
```

### Navbar cart badge
```php
// Trong navbar.php — đọc thẳng từ session (exception cho partial)
$cartCount = array_sum(array_column($_SESSION['cart'] ?? [], 'quantity'));
```

### Topbar notification badge
```php
// Trong admin layout (admin.php) — query 1 lần trước khi include topbar
$pendingCount = (new ConsultationModel())->countPending();
// Truyền vào partial qua variable
```

---

## Validation

- Không có form input trong các file này — không cần validation

---

## Database Interaction

| Action | Where | Method |
|---|---|---|
| Đếm pending consultations (badge) | `admin/partials/topbar.php` (qua layout) | `ConsultationModel::countPending()` |

> ⚠️ Đây là trường hợp **duy nhất** cho phép gọi Model từ layout/partial.  
> Lý do: notification count cần có ở mọi trang admin, không thể inject từ mỗi controller.  
> Cách làm: gọi trong `layouts/admin.php` trước khi include topbar, truyền vào `$pendingCount`.

---

## Permissions

- Error pages: public (không cần login)
- Admin partials: chỉ render khi đã qua `requireAdmin()` trong controller

---

## Error Handling

| Tình huống | Xử lý |
|---|---|
| Layout chính bị lỗi | `errors/500.php` là HTML tĩnh — không phụ thuộc layout |
| ConsultationModel lỗi trong topbar | try-catch, default `$pendingCount = 0` |

---

## Done ✅

**Error Pages:**
- [ ] `errors/404.php` render đúng với HTTP 404
- [ ] `errors/403.php` render đúng với HTTP 403
- [ ] `errors/500.php` là HTML tĩnh, không phụ thuộc layout
- [ ] `set_exception_handler` trong index.php hoạt động

**Frontend Partials:**
- [ ] Navbar hiển thị cart badge đúng số lượng
- [ ] Navbar dropdown user khi đã login
- [ ] Navbar hiển thị "Admin Panel" link nếu `role = admin`
- [ ] Active link highlight đúng theo URL hiện tại

**Admin Partials:**
- [ ] Sidebar active state đúng theo URL
- [ ] Topbar notification badge hiển thị số pending consultations
- [ ] Topbar dropdown: Profile, View Site, Logout
- [ ] Sidebar collapse/expand trên mobile (JS toggle class)

# Module: Auth

## Objective
- Cho phép người dùng đăng ký tài khoản, đăng nhập, đăng xuất
- Phân quyền `customer` / `admin` dựa trên `customers.role`
- Bảo vệ các route yêu cầu đăng nhập hoặc quyền admin
- Lưu thông tin user vào `$_SESSION['user']` sau khi login thành công

---

## Wireframe thiết kế giao diện

### Login Page — `/auth/login`
```
┌─────────────────────────────────────────────────────────┐
│  [NAVBAR: Logo | Products | Cart | Login]               │
├─────────────────────────────────────────────────────────┤
│                                                         │
│          ┌───────────────────────────────┐              │
│          │  🔵 EyeGlass Shop             │              │
│          │  ──────────────────────────  │              │
│          │  Welcome back                 │              │
│          │                               │              │
│          │  Username                     │              │
│          │  ┌─────────────────────────┐  │              │
│          │  │ john_doe               │  │              │
│          │  └─────────────────────────┘  │              │
│          │                               │              │
│          │  Password                     │              │
│          │  ┌─────────────────────────┐  │              │
│          │  │ ••••••••               │  │              │
│          │  └─────────────────────────┘  │              │
│          │                               │              │
│          │  [    Login (#0ea5e9)     ]   │              │
│          │                               │              │
│          │  Don't have an account?       │              │
│          │  [Register here]              │              │
│          └───────────────────────────────┘              │
│                                                         │
└─────────────────────────────────────────────────────────┘
```

### Register Page — `/auth/register`
```
┌─────────────────────────────────────────────────────────┐
│  [NAVBAR]                                               │
├─────────────────────────────────────────────────────────┤
│                                                         │
│          ┌───────────────────────────────┐              │
│          │  Create Account               │              │
│          │  ──────────────────────────  │              │
│          │                               │              │
│          │  Full Name                    │              │
│          │  ┌─────────────────────────┐  │              │
│          │  └─────────────────────────┘  │              │
│          │  Username *                   │              │
│          │  ┌─────────────────────────┐  │              │
│          │  └─────────────────────────┘  │              │
│          │  Email *                      │              │
│          │  ┌─────────────────────────┐  │              │
│          │  └─────────────────────────┘  │              │
│          │  Phone                        │              │
│          │  ┌─────────────────────────┐  │              │
│          │  └─────────────────────────┘  │              │
│          │  Password *                   │              │
│          │  ┌─────────────────────────┐  │              │
│          │  └─────────────────────────┘  │              │
│          │  Confirm Password *           │              │
│          │  ┌─────────────────────────┐  │              │
│          │  └─────────────────────────┘  │              │
│          │                               │              │
│          │  [  Create Account  ]         │              │
│          │  Already have account? Login  │              │
│          └───────────────────────────────┘              │
└─────────────────────────────────────────────────────────┘
```

**Style notes:**
- Card trắng `#ffffff`, shadow `--shadow-md`, border-radius `--border-radius-md`
- Button primary: `background #0ea5e9`, hover `#0284c7`
- Input border: `--border-color`, focus ring `#0ea5e9`
- Flash error: `background #fef2f2`, border-left `4px solid #ef4444`
- Flash success: `background #f0fdf4`, border-left `4px solid #22c55e`
- Card max-width: `420px`, centered, padding `2rem`

---

## Routes

| Method | URL | Handler | Auth |
|---|---|---|---|
| GET | `/auth/login` | `AuthController::loginForm` | Guest only |
| POST | `/auth/login` | `AuthController::login` | Guest only |
| GET | `/auth/register` | `AuthController::registerForm` | Guest only |
| POST | `/auth/register` | `AuthController::register` | Guest only |
| GET | `/auth/logout` | `AuthController::logout` | Login required |

---

## UI Pages

### `views/auth/login.php`
- Flash message (error/success)
- Form fields: `username` (text), `password` (password)
- Old input restore: `username`
- Submit button: "Login"
- Link to register

### `views/auth/register.php`
- Flash message
- Form fields:
  - `full_name` (text, optional)
  - `username` (text, required)
  - `email` (email, required)
  - `phone` (tel, optional)
  - `password` (password, required)
  - `confirm_password` (password, required)
- Old input restore: tất cả trừ password
- Submit button: "Create Account"
- Link to login

---

## Data Processing Flow

### GET `/auth/login`
```
1. Kiểm tra Session::isLoggedIn()
   └── true → redirect('/')
2. Lấy Session::getOldInput() → $oldInput
3. render('auth/login', [title, oldInput])
```

### POST `/auth/login`
```
1. Đọc: $username = trim($_POST['username']), $password = $_POST['password']
2. Validate:
   - username empty? → flash error + setOldInput + redirect('/auth/login')
   - password empty? → flash error + setOldInput + redirect('/auth/login')
3. CustomerModel::findByUsername($username)
   - not found → flash 'Invalid username or password' + redirect
4. password_verify($password, $customer['password'])
   - fail → flash 'Invalid username or password' + redirect
5. $customer['status'] === 'banned'?
   - true → flash 'Account banned' + redirect('/auth/login')
6. session_regenerate_id(true)
7. Session::setUser($customer)
8. flash success 'Welcome back, {full_name}!'
9. redirect: role==='admin' → '/admin', else → '/'
```

### GET `/auth/register`
```
1. Kiểm tra Session::isLoggedIn() → redirect('/') nếu đã login
2. render('auth/register', [title, oldInput])
```

### POST `/auth/register`
```
1. Đọc tất cả $_POST fields, trim()
2. Validate (xem section Validation)
3. CustomerModel::findByUsername($username) → tồn tại? → flash + redirect
4. CustomerModel::findByEmail($email) → tồn tại? → flash + redirect
5. CustomerModel::register($data) → false? → flash DB error + redirect
6. flash success 'Account created! Please login.'
7. redirect('/auth/login')
```

### GET `/auth/logout`
```
1. requireAuth()
2. Session::destroy()
3. flash success 'You have been logged out.'
4. redirect('/auth/login')
```

---

## Validation

### Login
| Field | Rule |
|---|---|
| username | Required, không được rỗng |
| password | Required, không được rỗng |

### Register
| Field | Rule | Error message |
|---|---|---|
| username | Required | "Username is required." |
| username | Length 3–50 | "Username must be 3–50 characters." |
| username | Regex `^[a-zA-Z0-9_]+$` | "Username only allows letters, numbers, underscore." |
| email | Required | "Email is required." |
| email | Valid email format | "Invalid email format." |
| password | Required | "Password is required." |
| password | Min 8 ký tự | "Password must be at least 8 characters." |
| password | Có chữ hoa + số | "Password needs 1 uppercase and 1 number." |
| confirm_password | === password | "Passwords do not match." |
| username | Unique (DB check) | "Username already taken." |
| email | Unique (DB check) | "Email already registered." |

---

## Database Interaction

**Bảng:** `customers`

| Action | Method | SQL |
|---|---|---|
| Tìm theo username | `findByUsername(string $username)` | `SELECT * FROM customers WHERE username = ?` |
| Tìm theo email | `findByEmail(string $email)` | `SELECT * FROM customers WHERE email = ?` |
| Tạo tài khoản | `register(array $data)` | `INSERT INTO customers (username, password, email, full_name, phone)` |

**Notes:**
- `password` lưu bằng `password_hash($raw, PASSWORD_BCRYPT)`
- `role` mặc định `'customer'`, không cho user tự chọn
- `status` mặc định `'active'`

---

## Permissions

| Route | Yêu cầu |
|---|---|
| GET/POST `/auth/login` | Guest only — nếu đã login → redirect `/` |
| GET/POST `/auth/register` | Guest only — nếu đã login → redirect `/` |
| GET `/auth/logout` | Login required — nếu chưa login → redirect `/auth/login` |

---

## Error Handling

| Tình huống | Xử lý |
|---|---|
| Username/password sai | flash error generic (không tiết lộ field nào sai) → redirect login |
| Account bị banned | flash error 'Account banned' → redirect login |
| Username đã tồn tại | flash error → setOldInput → redirect register |
| Email đã tồn tại | flash error → setOldInput → redirect register |
| DB insert thất bại | flash error 'Registration failed, please try again' → redirect register |
| Validate fail | flash error với message cụ thể → setOldInput → redirect |

---

## Redirect Rules

| Sự kiện | Redirect đến | Old input |
|---|---|---|
| Login thành công (customer) | `/` | Không |
| Login thành công (admin) | `/admin` | Không |
| Login thất bại | `/auth/login` | Có (`username`) |
| Register thành công | `/auth/login` | Không |
| Register thất bại | `/auth/register` | Có (tất cả trừ password) |
| Logout | `/auth/login` | Không |
| Đã login → vào login/register | `/` | Không |

---

## Done ✅

- [ ] GET `/auth/login` render form đúng, restore old input
- [ ] POST `/auth/login` validate, verify password, set session, redirect đúng role
- [ ] GET `/auth/register` render form đúng
- [ ] POST `/auth/register` validate đầy đủ, check unique, hash password, insert DB
- [ ] GET `/auth/logout` destroy session, redirect
- [ ] Flash message hiển thị đúng type (error/success)
- [ ] Guest-only guard hoạt động (đã login không vào được login/register page)
- [ ] Login guard hoạt động (chưa login không vào được logout)
- [ ] `session_regenerate_id(true)` được gọi sau login

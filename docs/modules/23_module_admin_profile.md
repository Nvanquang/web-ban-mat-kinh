# Module: Admin — Profile & Settings

## Objective
- Admin xem và cập nhật thông tin cá nhân: họ tên, email, SĐT, địa chỉ
- Admin đổi mật khẩu (xác nhận mật khẩu cũ trước)
- Không cho thay đổi `username` và `role`
- Mỗi action (update profile / change password) là form riêng, submit riêng

---

## Wireframe thiết kế giao diện

### Admin Profile — `/admin/profile`
```
┌───────────────────────────────────────────────────────────────┐
│  [TOPBAR]                                                     │
├──────────┬────────────────────────────────────────────────────┤
│ SIDEBAR  │                                                    │
│ ...      │  My Profile                                        │
│          │                                                    │
│          │  ┌─────────────────────────────────────────────┐   │
│          │  │  Account Information                        │   │
│          │  │  ───────────────────────────────────────    │   │
│          │  │  Username     admin          (read-only)    │   │
│          │  │  Role         Administrator  (read-only)    │   │
│          │  │  Member since Jan 1, 2025                   │   │
│          │  └─────────────────────────────────────────────┘   │
│          │                                                    │
│          │  ┌─────────────────────────────────────────────┐   │
│          │  │  Edit Profile                               │   │
│          │  │  ───────────────────────────────────────    │   │
│          │  │  Full Name                                  │   │
│          │  │  [_____________________________________]    │   │
│          │  │  Email *                                    │   │
│          │  │  [_____________________________________]    │   │
│          │  │  Phone                                      │   │
│          │  │  [_____________________________________]    │   │
│          │  │  Address                                    │   │
│          │  │  [_____________________________________]    │   │
│          │  │                    [  Save Profile  ]       │   │
│          │  │                    (bg: #0ea5e9)             │   │
│          │  └─────────────────────────────────────────────┘   │
│          │                                                    │
│          │  ┌─────────────────────────────────────────────┐   │
│          │  │  Change Password                            │   │
│          │  │  ───────────────────────────────────────    │   │
│          │  │  Current Password *                         │   │
│          │  │  [_____________________________________]    │   │
│          │  │  New Password *                             │   │
│          │  │  [_____________________________________]    │   │
│          │  │  Confirm New Password *                     │   │
│          │  │  [_____________________________________]    │   │
│          │  │                  [  Change Password  ]      │   │
│          │  │                  (bg: #0284c7)               │   │
│          │  └─────────────────────────────────────────────┘   │
└──────────┴────────────────────────────────────────────────────┘
```

**Style notes:**
- 3 card sections: `bg #ffffff`, shadow `--shadow-sm`, `border-radius --border-radius-md`, `mb-4`
- Read-only fields: `bg #f8fafc`, `color --text-muted`, không có border focus
- Save Profile button: `bg #0ea5e9`, hover `#0284c7`
- Change Password button: `bg #0284c7`, hover `#0369a1` (slightly darker để phân biệt)
- Flash success: green / Flash error: red — hiển thị trên từng section tương ứng

---

## Routes

| Method | URL | Handler | Auth |
|---|---|---|---|
| GET | `/admin/profile` | `AdminProfileController::index` | Admin |
| POST | `/admin/profile/update` | `AdminProfileController::update` | Admin |
| POST | `/admin/profile/password` | `AdminProfileController::changePassword` | Admin |

---

## UI Pages

### `views/admin/profile/index.php`
- Section 1 — Account info (read-only): username, role, created_at
- Section 2 — Edit Profile form:
  - `full_name` (text, optional)
  - `email` (email, required)
  - `phone` (tel, optional)
  - `address` (textarea, optional)
  - Old input restore
- Section 3 — Change Password form:
  - `current_password` (password, required)
  - `new_password` (password, required)
  - `confirm_password` (password, required)
  - Không restore old input (password fields)

---

## Data Processing Flow

### GET `/admin/profile`
```
1. requireAdmin()
2. $adminId = Session::getUser()['id']
3. CustomerModel::findById($adminId) → $admin (fresh từ DB)
4. $oldInput = Session::getOldInput()
5. render('admin/profile/index', [admin, oldInput], 'admin')
```

### POST `/admin/profile/update`
```
1. requireAdmin()
2. $adminId  = Session::getUser()['id']
3. $fullName = trim($_POST['full_name'] ?? '')
4. $email    = trim(strtolower($_POST['email'] ?? ''))
5. $phone    = trim($_POST['phone']    ?? '')
6. $address  = trim($_POST['address'] ?? '')
7. Validate:
   - empty($email) → flash error + setOldInput + redirect
   - !validateEmail($email) → flash error + setOldInput + redirect
8. Check email unique: CustomerModel::findByEmail($email)
   - tồn tại && id !== $adminId → flash 'Email already in use.' + setOldInput + redirect
9. CustomerModel::updateProfile($adminId, [full_name, email, phone, address])
   - false → flash 'Update failed.' + redirect
10. Cập nhật session: $_SESSION['user']['full_name'] = $fullName
11. flash success 'Profile updated successfully!'
12. redirect('/admin/profile')
```

### POST `/admin/profile/password`
```
1. requireAdmin()
2. $adminId         = Session::getUser()['id']
3. $currentPassword = $_POST['current_password'] ?? ''
4. $newPassword     = $_POST['new_password']      ?? ''
5. $confirmPassword = $_POST['confirm_password']  ?? ''
6. Validate:
   - any empty → flash 'All password fields are required.' + redirect
   - $newPassword !== $confirmPassword → flash 'New passwords do not match.' + redirect
   - validatePassword($newPassword) errors → flash errors + redirect
7. CustomerModel::findById($adminId) → lấy hash hiện tại
8. !password_verify($currentPassword, $admin['password'])
   → flash 'Current password is incorrect.' + redirect
9. $newHash = password_hash($newPassword, PASSWORD_BCRYPT)
10. CustomerModel::updatePassword($adminId, $newHash)
    - false → flash 'Password change failed.' + redirect
11. flash success 'Password changed successfully!'
12. redirect('/admin/profile')
```

---

## Validation

### Update Profile
| Field | Rule | Error |
|---|---|---|
| `email` | Required | "Email is required." |
| `email` | Valid format | "Invalid email format." |
| `email` | Unique (trừ chính mình) | "Email already in use by another account." |
| `phone` | Optional; nếu có: VN format | "Invalid phone number." |
| `full_name` | Optional, max 100 ký tự | Trim, không enforce |
| `address` | Optional, max 500 ký tự | Trim, không enforce |

### Change Password
| Field | Rule | Error |
|---|---|---|
| `current_password` | Required | "Current password is required." |
| `new_password` | Required | "New password is required." |
| `new_password` | Min 8 ký tự | "Password must be at least 8 characters." |
| `new_password` | Có chữ hoa + số | "Password needs 1 uppercase letter and 1 number." |
| `confirm_password` | === new_password | "New passwords do not match." |
| `current_password` | Phải khớp hash trong DB | "Current password is incorrect." |
| `new_password` | Khác `current_password` | "New password must differ from current password." |

---

## Database Interaction

**Bảng:** `customers`

| Action | Method | SQL |
|---|---|---|
| Lấy thông tin admin | `CustomerModel::findById($id)` | SELECT * WHERE id = ? |
| Kiểm tra email unique | `CustomerModel::findByEmail($email)` | SELECT id WHERE email = ? |
| Cập nhật profile | `CustomerModel::updateProfile($id, $data)` | UPDATE SET full_name, email, phone, address WHERE id = ? |
| Cập nhật password | `CustomerModel::updatePassword($id, $hash)` | UPDATE SET password = ? WHERE id = ? |

> `username` và `role` **không** được phép cập nhật từ form này.

---

## Permissions

| Route | Yêu cầu |
|---|---|
| Tất cả `/admin/profile/*` | Admin only — `requireAdmin()` |
| Admin chỉ sửa được chính mình | `$adminId` luôn lấy từ `Session::getUser()['id']` (không lấy từ URL/POST) |

---

## Error Handling

| Tình huống | Xử lý |
|---|---|
| Email đã dùng bởi account khác | flash + setOldInput + redirect |
| Mật khẩu hiện tại sai | flash error + redirect (không lộ password trong old input) |
| Password không match | flash error + redirect |
| DB update thất bại | flash 'Operation failed.' + redirect |

---

## Redirect Rules

| Sự kiện | Redirect | Old input |
|---|---|---|
| Update profile thành công | `/admin/profile` | Không |
| Update profile thất bại | `/admin/profile` | Có (profile fields) |
| Change password thành công | `/admin/profile` | Không |
| Change password thất bại | `/admin/profile` | Không (không restore password) |

---

## Done ✅

- [ ] GET `/admin/profile` hiển thị đủ 3 sections
- [ ] Read-only fields: username, role, created_at không thể edit
- [ ] POST update profile: validate email, check unique, update DB
- [ ] Session `full_name` cập nhật ngay sau khi save (topbar hiển thị tên mới)
- [ ] POST change password: verify current password trước
- [ ] Không cho đặt lại mật khẩu giống mật khẩu cũ
- [ ] Password fields không bao giờ restore old input
- [ ] Old input restore cho profile fields khi lỗi
- [ ] Admin guard hoạt động

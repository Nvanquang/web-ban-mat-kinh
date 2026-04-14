<?php
/**
 * View cho trang Profile dùng chung cho User và Admin
 * $user: thông tin người dùng được truyền từ controller
 * $oldInput: dữ liệu lỗi form profile
 * $baseRedirect: route gốc chuyển về (profile hoặc admin/profile)
 */
$user = $user ?? [];
$oldInput = $oldInput ?? [];
$baseRedirect = $baseRedirect ?? '/profile';

if ($isFrontend ?? true) {
    require_once APPROOT . '/app/views/layouts/header.php';
}
?>

<div class="container py-4">
    <h2 class="fw-bold mb-4">Hồ sơ cá nhân</h2>

    <div class="row">
        <div class="col-lg-12">
            
            <!-- 1. Account Information -->
            <div class="card shadow-sm border-0 rounded-3 mb-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0 fw-semibold text-primary"><i class="bi bi-person-badge me-2"></i>Thông tin chung</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label text-muted mb-1 small">Username</label>
                            <input type="text" class="form-control bg-light text-muted border-0" value="<?= htmlspecialchars($user['username'] ?? '', ENT_QUOTES) ?>" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted mb-1 small">Vai trò</label>
                            <input type="text" class="form-control bg-light text-muted border-0 text-capitalize" value="<?= htmlspecialchars($user['role'] ?? '', ENT_QUOTES) ?>" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted mb-1 small">Ngày tham gia</label>
                            <input type="text" class="form-control bg-light text-muted border-0" value="<?= date('d/m/Y', strtotime($user['created_at'] ?? 'now')) ?>" readonly>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. Edit Profile Form -->
            <div class="card shadow-sm border-0 rounded-3 mb-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0 fw-semibold text-primary"><i class="bi bi-pencil-square me-2"></i>Chỉnh sửa thông tin liên hệ</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?= BASE_URL . $baseRedirect . '/update' ?>" id="profileUpdateForm">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Session::getCsrfToken(), ENT_QUOTES) ?>">

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Họ và tên</label>
                                <input type="text" name="full_name" class="form-control" 
                                       value="<?= htmlspecialchars($oldInput['full_name'] ?? $user['full_name'] ?? '', ENT_QUOTES) ?>" 
                                       maxlength="100">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control" required
                                       value="<?= htmlspecialchars($oldInput['email'] ?? $user['email'] ?? '', ENT_QUOTES) ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Số điện thoại</label>
                                <input type="tel" name="phone" class="form-control"
                                       value="<?= htmlspecialchars($oldInput['phone'] ?? $user['phone'] ?? '', ENT_QUOTES) ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Địa chỉ</label>
                                <textarea name="address" class="form-control" rows="1" maxlength="500"><?= htmlspecialchars($oldInput['address'] ?? $user['address'] ?? '', ENT_QUOTES) ?></textarea>
                            </div>
                            <div class="col-12 mt-4 text-end">
                                <button type="submit" class="btn text-white px-4" style="background:#0ea5e9;">Lưu hồ sơ</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- 3. Change Password Form -->
            <div class="card shadow-sm border-0 rounded-3 mb-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0 fw-semibold" style="color: #0284c7;"><i class="bi bi-shield-lock me-2"></i>Đổi mật khẩu</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?= BASE_URL . $baseRedirect . '/password' ?>" id="passwordChangeForm">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Session::getCsrfToken(), ENT_QUOTES) ?>">

                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Mật khẩu hiện tại <span class="text-danger">*</span></label>
                                <input type="password" name="current_password" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Mật khẩu mới <span class="text-danger">*</span></label>
                                <input type="password" name="new_password" class="form-control" minlength="8" required>
                                <div class="form-text text-muted small">Tối thiểu 8 ký tự.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Xác nhận mật khẩu mới <span class="text-danger">*</span></label>
                                <input type="password" name="confirm_password" class="form-control" minlength="8" required>
                            </div>
                            <div class="col-12 mt-4 text-end">
                                <button type="submit" class="btn text-white px-4" style="background:#0284c7;">Đổi mật khẩu</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const handleAjaxForm = (formId, submitBtnId) => {
        const form = document.getElementById(formId);
        if (!form) return;

        form.addEventListener('submit', async function (e) {
            e.preventDefault();
            const btn = form.querySelector('button[type="submit"]');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Đang xử lý...';
            btn.disabled = true;

            // Xóa lỗi cũ
            form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
            form.querySelectorAll('.invalid-feedback').forEach(el => el.remove());

            // Hàm tạo Toast tuỳ biến (nếu không có sẵn ở admin)
            const displayToast = (msg, type) => {
                if (typeof window.showToast === 'function') {
                    window.showToast(msg, type === 'error' ? 'danger' : type);
                    return;
                }
                const toast = document.createElement('div');
                toast.className = 'position-fixed top-0 start-50 translate-middle-x mt-3 px-3 py-2 rounded-3 shadow text-white';
                toast.style.zIndex = '9999';
                toast.style.background = type === 'error' ? '#ef4444' : '#22c55e';
                toast.textContent = msg || '';
                document.body.appendChild(toast);
                setTimeout(() => toast.remove(), 2500);
            };

            try {
                const formData = new FormData(form);
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const data = await response.json();

                if (data.success) {
                    displayToast(data.message || 'Thành công!', 'success');
                    if (data.full_name) {
                        const nameEl = document.querySelector('.dropdown-toggle span');
                        if (nameEl) nameEl.textContent = data.full_name;
                    }
                    if (formId === 'passwordChangeForm') {
                        form.reset();
                    }
                } else {
                    if (data.errors) {
                        for (let field in data.errors) {
                            const input = form.querySelector(`[name="${field}"]`);
                            if (input) {
                                input.classList.add('is-invalid');
                                const errorDiv = document.createElement('div');
                                errorDiv.className = 'invalid-feedback fw-semibold d-block';
                                errorDiv.textContent = data.errors[field];
                                const nextEl = input.nextElementSibling;
                                if (nextEl && nextEl.classList.contains('form-text')) {
                                    input.parentElement.insertBefore(errorDiv, nextEl);
                                } else {
                                    input.parentElement.appendChild(errorDiv);
                                }
                            }
                        }
                    }
                    if (data.message) {
                        displayToast(data.message, 'error');
                    }
                }
            } catch (error) {
                displayToast('Gặp lỗi khi kết nối tới máy chủ.', 'error');
            } finally {
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        });
    };

    handleAjaxForm('profileUpdateForm');
    handleAjaxForm('passwordChangeForm');
});
</script>

<?php
if ($isFrontend ?? true) {
    require_once APPROOT . '/app/views/layouts/footer.php';
}
?>

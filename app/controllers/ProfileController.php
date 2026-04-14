<?php
// app/controllers/ProfileController.php

class ProfileController extends Controller {
    protected string $layout = '';
    protected bool $isFrontend = true;
    protected string $viewName = 'profile/index';
    protected string $baseRedirect = '/profile';

    public function index(): void {
        $this->requireAuth();
        
        $userId = (int)Session::getUser()['id'];
        $model = new CustomerModel();
        $user = $model->findById($userId);
        
        if (!$user) {
            Router::error404();
            return;
        }

        $this->render($this->viewName, [
            'title' => 'Thông tin tài khoản',
            'currentPage' => 'profile',
            'user' => $user,
            'oldInput' => Session::getOldInput(),
            'baseRedirect' => $this->baseRedirect,
            'isFrontend' => $this->isFrontend
        ], $this->layout);
    }

    public function update(): void {
        $this->requireAuth();
        $userId = (int)Session::getUser()['id'];
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

        if (!Session::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            if ($isAjax) {
                echo json_encode(['success' => false, 'message' => 'Token CSRF không hợp lệ.']);
                exit;
            }
            Session::flash('error', 'Token CSRF không hợp lệ.');
            $this->redirect($this->baseRedirect);
            return;
        }

        $fullName = trim($_POST['full_name'] ?? '');
        $email    = trim(strtolower($_POST['email'] ?? ''));
        $phone    = trim($_POST['phone'] ?? '');
        $address  = trim($_POST['address'] ?? '');

        $errors = [];

        if (empty($email)) {
            $errors['email'] = 'Vui lòng nhập Email.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Định dạng Email không hợp lệ.';
        }

        $model = new CustomerModel();
        
        // Kiểm tra unique email
        if (!isset($errors['email'])) {
            $existing = $model->findByEmail($email);
            if ($existing && (int)$existing['id'] !== $userId) {
                $errors['email'] = 'Email đã được sử dụng bởi tài khoản khác.';
            }
        }

        if (!empty($errors)) {
            if ($isAjax) {
                echo json_encode(['success' => false, 'errors' => $errors, 'message' => 'Vui lòng kiểm tra lại các trường nhập liệu.']);
                exit;
            }
            // Fallback for non-ajax
            Session::flash('error', reset($errors));
            Session::setOldInput($_POST);
            $this->redirect($this->baseRedirect);
            return;
        }

        // Cập nhật profile
        $data = [
            'full_name' => $fullName,
            'email'     => $email,
            'phone'     => $phone,
            'address'   => $address
        ];

        if ($model->updateProfile($userId, $data)) {
            $currentUser = Session::getUser();
            $currentUser['full_name'] = $fullName;
            Session::set('user', $currentUser);

            if ($isAjax) {
                echo json_encode(['success' => true, 'message' => 'Đã cập nhật thông tin thành công!', 'full_name' => $fullName]);
                exit;
            }
            Session::flash('success', 'Đã cập nhật thông tin thành công!');
        } else {
            if ($isAjax) {
                echo json_encode(['success' => false, 'message' => 'Cập nhật thất bại. Xin thử lại.']);
                exit;
            }
            Session::flash('error', 'Cập nhật thất bại. Xin thử lại.');
            Session::setOldInput($_POST);
        }

        $this->redirect($this->baseRedirect);
    }

    public function changePassword(): void {
        $this->requireAuth();
        $userId = (int)Session::getUser()['id'];
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

        if (!Session::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            if ($isAjax) {
                echo json_encode(['success' => false, 'message' => 'Token CSRF không hợp lệ.']);
                exit;
            }
            Session::flash('error', 'Token CSRF không hợp lệ.');
            $this->redirect($this->baseRedirect);
            return;
        }

        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword     = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        $errors = [];

        if (empty($currentPassword)) {
            $errors['current_password'] = 'Vui lòng nhập mật khẩu hiện tại.';
        }
        if (empty($newPassword)) {
            $errors['new_password'] = 'Vui lòng nhập mật khẩu mới.';
        } elseif (strlen($newPassword) < 8) {
            $errors['new_password'] = 'Mật khẩu mới phải dài ít nhất 8 ký tự.';
        }
        if (empty($confirmPassword)) {
            $errors['confirm_password'] = 'Vui lòng xác nhận mật khẩu mới.';
        } elseif ($newPassword !== $confirmPassword) {
            $errors['confirm_password'] = 'Mật khẩu xác nhận không khớp.';
        }
        if (!empty($newPassword) && $currentPassword === $newPassword) {
            $errors['new_password'] = 'Mật khẩu mới phải khác mật khẩu hiện tại.';
        }

        $model = new CustomerModel();
        $user = $model->findById($userId);

        if (empty($errors['current_password'])) {
            if (!$user || !password_verify($currentPassword, $user['password'])) {
                $errors['current_password'] = 'Mật khẩu hiện tại không đúng.';
            }
        }

        if (!empty($errors)) {
            if ($isAjax) {
                echo json_encode(['success' => false, 'errors' => $errors, 'message' => 'Vui lòng kiểm tra lại thông tin.']);
                exit;
            }
            Session::flash('error', reset($errors));
            $this->redirect($this->baseRedirect);
            return;
        }

        $newHash = password_hash($newPassword, PASSWORD_BCRYPT);
        if ($model->updatePassword($userId, $newHash)) {
            if ($isAjax) {
                echo json_encode(['success' => true, 'message' => 'Đổi mật khẩu thành công!']);
                exit;
            }
            Session::flash('success', 'Đổi mật khẩu thành công!');
        } else {
            if ($isAjax) {
                echo json_encode(['success' => false, 'message' => 'Đổi mật khẩu thất bại. Xin thử lại.']);
                exit;
            }
            Session::flash('error', 'Đổi mật khẩu thất bại. Xin thử lại.');
        }

        $this->redirect($this->baseRedirect);
    }
}

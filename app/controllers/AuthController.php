<?php
// app/controllers/AuthController.php

class AuthController extends Controller {
    public function loginForm(): void {
        if (Session::isLoggedIn()) {
            $this->redirect('/');
        }

        $this->render('auth/login', [
            'title'    => 'Login — ' . APP_NAME,
            'oldInput' => Session::getOldInput(),
        ]);
    }

    public function login(): void {
        if (Session::isLoggedIn()) {
            $this->redirect('/');
        }

        $token = $_POST['csrf_token'] ?? '';
        if (!Session::verifyCsrfToken($token)) {
            http_response_code(403);
            Session::flash('error', 'Invalid CSRF token.');
            $this->redirect('/auth/login');
        }

        $username = trim((string)($_POST['username'] ?? ''));
        $password = (string)($_POST['password'] ?? '');

        $errors = [];
        if ($username === '') $errors[] = 'Username is required.';
        if ($password === '') $errors[] = 'Password is required.';

        if ($errors) {
            Session::flash('error', implode(' ', $errors));
            Session::setOldInput(['username' => $username]);
            $this->redirect('/auth/login');
        }

        $model = new CustomerModel();
        $customer = $model->findByUsername($username);

        if (!$customer || !password_verify($password, (string)$customer['password'])) {
            Session::flash('error', 'Tên đăng nhập hoặc mật khẩu chưa chính xác.');
            Session::setOldInput(['username' => $username]);
            $this->redirect('/auth/login');
        }

        if (($customer['status'] ?? '') === 'banned') {
            Session::flash('error', 'Tài khoản của bạn đã bị khóa.');
            $this->redirect('/auth/login');
        }

        session_regenerate_id(true);
        Session::setUser($customer);

        // Lưu Cookie 30 ngày (đơn giản, lưu ID)
        setcookie('user_id', (string)$customer['id'], time() + (30 * 24 * 60 * 60), '/');

        $fullName = trim((string)($customer['full_name'] ?? ''));
        $nameForMsg = $fullName !== '' ? $fullName : (string)($customer['username'] ?? '');
        Session::flash('success', 'Chào mừng bạn trở lại, ' . $nameForMsg . '!');

        $this->redirect(($customer['role'] ?? '') === 'admin' ? '/admin' : '/');
    }

    public function registerForm(): void {
        if (Session::isLoggedIn()) {
            $this->redirect('/');
        }

        $this->render('auth/register', [
            'title'    => 'Register — ' . APP_NAME,
            'oldInput' => Session::getOldInput(),
        ]);
    }

    public function register(): void {
        if (Session::isLoggedIn()) {
            $this->redirect('/');
        }

        $token = $_POST['csrf_token'] ?? '';
        if (!Session::verifyCsrfToken($token)) {
            http_response_code(403);
            Session::flash('error', 'Mã CSRF không hợp lệ.');
            $this->redirect('/auth/register');
        }

        $data = [
            'full_name'         => trim((string)($_POST['full_name'] ?? '')),
            'username'          => trim((string)($_POST['username'] ?? '')),
            'email'             => trim(strtolower((string)($_POST['email'] ?? ''))),
            'phone'             => trim((string)($_POST['phone'] ?? '')),
            'password'          => (string)($_POST['password'] ?? ''),
            'confirm_password'  => (string)($_POST['confirm_password'] ?? ''),
        ];

        $errors = $this->validateRegister($data);
        if ($errors) {
            Session::flash('error', implode(' ', $errors));
            Session::setOldInput([
                'full_name' => $data['full_name'],
                'username'  => $data['username'],
                'email'     => $data['email'],
                'phone'     => $data['phone'],
            ]);
            $this->redirect('/auth/register');
        }

        $model = new CustomerModel();
        if ($model->findByUsername($data['username'])) {
            Session::flash('error', 'Tên đăng nhập đã tồn tại.');
            Session::setOldInput([
                'full_name' => $data['full_name'],
                'username'  => $data['username'],
                'email'     => $data['email'],
                'phone'     => $data['phone'],
            ]);
            $this->redirect('/auth/register');
        }
        if ($model->findByEmail($data['email'])) {
            Session::flash('error', 'Email đã được đăng ký.');
            Session::setOldInput([
                'full_name' => $data['full_name'],
                'username'  => $data['username'],
                'email'     => $data['email'],
                'phone'     => $data['phone'],
            ]);
            $this->redirect('/auth/register');
        }

        $id = $model->register([
            'username'  => $data['username'],
            'password'  => $data['password'],
            'email'     => $data['email'],
            'full_name' => $data['full_name'] !== '' ? $data['full_name'] : null,
            'phone'     => $data['phone'] !== '' ? $data['phone'] : null,
        ]);

        if (!$id) {
            Session::flash('error', 'Đăng ký thất bại, vui lòng thử lại.');
            Session::setOldInput([
                'full_name' => $data['full_name'],
                'username'  => $data['username'],
                'email'     => $data['email'],
                'phone'     => $data['phone'],
            ]);
            $this->redirect('/auth/register');
        }

        Session::flash('success', 'Tài khoản đã được tạo! Vui lòng đăng nhập.');
        $this->redirect('/auth/login');
    }

    public function logout(): void {
        $this->requireAuth();
        
        // Xóa Cookie đăng nhập
        if (isset($_COOKIE['user_id'])) {
            setcookie('user_id', '', time() - 3600, '/');
        }

        Session::destroy();
        Session::flash('success', 'Bạn đã đăng xuất thành công.');
        $this->redirect('/auth/login');
    }

    private function validateRegister(array $data): array {
        $errors = [];

        if ($data['username'] === '') $errors[] = 'Tên đăng nhập là bắt buộc.';
        if ($data['email'] === '')    $errors[] = 'Email là bắt buộc.';
        if ($data['password'] === '') $errors[] = 'Mật khẩu là bắt buộc.';

        $uLen = mb_strlen($data['username']);
        if ($data['username'] !== '' && ($uLen < 3 || $uLen > 50)) {
            $errors[] = 'Tên đăng nhập phải có 3–50 ký tự.';
        }
        if ($data['username'] !== '' && !preg_match('/^[a-zA-Z0-9_]+$/', $data['username'])) {
            $errors[] = 'Tên đăng nhập chỉ cho phép chữ cái, số và dấu gạch dưới.';
        }

        if ($data['email'] !== '' && filter_var($data['email'], FILTER_VALIDATE_EMAIL) === false) {
            $errors[] = 'Email không hợp lệ.';
        }

        if ($data['password'] !== '' && strlen($data['password']) < 8) {
            $errors[] = 'Mật khẩu phải có ít nhất 8 ký tự.';
        }
        if ($data['password'] !== '' && (!preg_match('/[A-Z]/', $data['password']) || !preg_match('/[0-9]/', $data['password']))) {
            $errors[] = 'Mật khẩu phải có ít nhất 1 chữ cái viết hoa và 1 số.';
        }

        if ($data['confirm_password'] !== $data['password']) {
            $errors[] = 'Mật khẩu không khớp.';
        }

        return $errors;
    }
}


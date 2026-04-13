<?php
// app/core/Session.php

class Session {
    /**
     * Khởi tạo session nếu chưa có
     */
    public static function init() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Gán giá trị vào session
     */
    public static function set($key, $value) {
        $_SESSION[$key] = $value;
    }

    /**
     * Lấy giá trị từ session
     */
    public static function get($key, $default = null) {
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Xóa một key trong session
     */
    public static function remove($key) {
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }

    /**
     * Hủy toàn bộ session
     */
    public static function destroy() {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }
        session_destroy();
    }

    /**
     * Quản lý Flash message (Hiển thị 1 lần rồi xóa)
     */
    public static function flash($type = '', $message = '') {
        if (!empty($type) && !empty($message)) {
            $_SESSION['flash'] = [
                'type' => $type,
                'message' => $message
            ];
        } elseif (empty($type) && isset($_SESSION['flash'])) {
            $flash = $_SESSION['flash'];
            unset($_SESSION['flash']);
            return $flash;
        }
        return null;
    }

    /**
     * Kiểm tra người dùng đã đăng nhập chưa
     */
    public static function isLoggedIn() {
        return isset($_SESSION['user']);
    }

    /**
     * Lấy thông tin user đang đăng nhập
     */
    public static function getUser(): array|null {
        return $_SESSION['user'] ?? null;
    }

    /**
     * Set user vào session (chỉ lưu field cần thiết)
     */
    public static function setUser(array $customer): void {
        $_SESSION['user'] = [
            'id'        => (int)($customer['id'] ?? 0),
            'username'  => (string)($customer['username'] ?? ''),
            'full_name' => (string)($customer['full_name'] ?? ''),
            'role'      => (string)($customer['role'] ?? 'customer'),
            'status'    => (string)($customer['status'] ?? 'active'),
        ];
    }

    /**
     * Kiểm tra vai trò admin
     */
    public static function isAdmin() {
        return self::isLoggedIn() && ($_SESSION['user']['role'] ?? '') === 'admin';
    }

    /**
     * Old input (PRG) - lưu tạm 1 lần để restore form
     */
    public static function setOldInput(array $data): void {
        $_SESSION['old_input'] = $data;
    }

    public static function getOldInput(): array {
        $data = $_SESSION['old_input'] ?? [];
        unset($_SESSION['old_input']);
        return is_array($data) ? $data : [];
    }

    /**
     * CSRF token
     */
    public static function getCsrfToken(): string {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return (string)$_SESSION['csrf_token'];
    }

    public static function verifyCsrfToken(string $token): bool {
        return hash_equals((string)($_SESSION['csrf_token'] ?? ''), (string)$token);
    }
}

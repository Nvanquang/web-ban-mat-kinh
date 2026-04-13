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
        session_unset();
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
     * Kiểm tra vai trò admin
     */
    public static function isAdmin() {
        return self::isLoggedIn() && ($_SESSION['user']['role'] ?? '') === 'admin';
    }
}

<?php
// index.php (Front Controller)

// 1. Load configuration
require_once __DIR__ . '/config/config.php';

// 2. Simple Autoloading (Cho các file core, controller, model)
spl_autoload_register(function ($class) {
    $paths = [
        __DIR__ . '/app/core/',
        __DIR__ . '/app/models/',
        __DIR__ . '/app/controllers/',
        __DIR__ . '/app/controllers/admin/',
    ];

    foreach ($paths as $path) {
        $file = $path . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// 3. Start Session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 4. Auto-login via Cookie
if (!isset($_SESSION['user']) && isset($_COOKIE['user_id'])) {
    $userId = (int)$_COOKIE['user_id'];
    $customerModel = new CustomerModel();
    $customer = $customerModel->findById($userId);
    
    if ($customer && ($customer['status'] ?? 'active') !== 'banned') {
        // Khôi phục session từ cookie
        if (class_exists('Session')) {
            Session::setUser($customer);
        } else {
            $_SESSION['user'] = [
                'id'        => (int)($customer['id'] ?? 0),
                'username'  => (string)($customer['username'] ?? ''),
                'full_name' => (string)($customer['full_name'] ?? ''),
                'role'      => (string)($customer['role'] ?? 'customer'),
                'status'    => (string)($customer['status'] ?? 'active'),
            ];
        }
    } else {
        // Cookie không hợp lệ hoặc bị khóa, xóa cookie
        setcookie('user_id', '', time() - 3600, '/');
    }
}

// 4. Register Exception Handler
set_exception_handler(function (\Throwable $e) {
    if (class_exists('Router')) {
        Router::error500($e);
    } else {
        http_response_code(500);
        echo "Internal Server Error";
    }
});

// 5. Dispatch Request
Router::dispatch();

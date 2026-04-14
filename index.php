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

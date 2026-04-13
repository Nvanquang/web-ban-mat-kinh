<?php
// config/config.php

// 1. Database Configuration
define('DB_HOST',    'localhost');
define('DB_NAME',    'eyeglass_db');
define('DB_USER',    'root');
define('DB_PASS',    '27072004'); // Thay đổi nếu XAMPP của bạn có mật khẩu
define('DB_CHARSET', 'utf8mb4');

// 2. Base URL
define('BASE_URL',    'http://localhost/web-ban-mat-kinh');

// 3. Paths
define('APPROOT',     dirname(dirname(__FILE__)));
define('UPLOAD_PATH', APPROOT . '/public/uploads/');
define('UPLOAD_URL',  BASE_URL . '/public/uploads/');

// 4. App Constants
define('APP_NAME',    'EyeGlass Shop');
define('APP_VERSION', '1.0.0');

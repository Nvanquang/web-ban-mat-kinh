<?php
// app/core/Router.php

class Router {
    public static function dispatch() {
        // Lấy URL từ request
        $url = isset($_GET['url']) ? rtrim($_GET['url'], '/') : '';
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        
        // Mặc định là HomeController@index
        if ($url == '') {
            $controllerName = 'HomeController';
            $action = 'index';
            $params = [];
        } else {
            $urlParts = explode('/', $url);
            
            // Special routes for Auth module (method-aware)
            if (($urlParts[0] ?? '') === 'auth') {
                $controllerName = 'AuthController';
                $segment = $urlParts[1] ?? '';

                if ($segment === 'login') {
                    $action = ($method === 'POST') ? 'login' : 'loginForm';
                    $params = [];
                } elseif ($segment === 'register') {
                    $action = ($method === 'POST') ? 'register' : 'registerForm';
                    $params = [];
                } elseif ($segment === 'logout') {
                    $action = 'logout';
                    $params = [];
                } else {
                    self::error404();
                    return;
                }
            } elseif (($urlParts[0] ?? '') === 'products') {
                // Products routes:
                // - GET /products           -> ProductController::index
                // - GET /products/{id}      -> ProductController::show($id)
                $controllerName = 'ProductController';
                $segment = $urlParts[1] ?? '';

                if ($segment === '' || $segment === null) {
                    $action = 'index';
                    $params = [];
                } elseif (ctype_digit((string)$segment)) {
                    $action = 'show';
                    $params = [(int)$segment];
                } else {
                    // Backward compatibility: /products/show/{id}
                    $action = $segment;
                    unset($urlParts[0], $urlParts[1]);
                    $params = array_values($urlParts);
                }
            } elseif (($urlParts[0] ?? '') === 'cart') {
                // Cart routes:
                // - GET  /cart         -> CartController::index
                // - POST /cart/add     -> CartController::add
                // - POST /cart/update  -> CartController::update
                // - POST /cart/remove  -> CartController::remove
                // - POST /cart/clear   -> CartController::clear
                $controllerName = 'CartController';
                $segment = $urlParts[1] ?? '';

                if ($segment === '' || $segment === null) {
                    $action = 'index';
                    $params = [];
                } else {
                    if ($method !== 'POST') {
                        self::error404();
                        return;
                    }
                    $action = $segment; // add|addAjax|update|remove|clear
                    $params = [];
                }
            } elseif (($urlParts[0] ?? '') === 'orders') {
                // Orders routes:
                // - GET  /orders             -> OrderController::index
                // - GET  /orders/{id}        -> OrderController::show($id)
                // - GET  /orders/checkout    -> OrderController::checkoutForm
                // - POST /orders/checkout    -> OrderController::checkout
                $controllerName = 'OrderController';
                $segment = $urlParts[1] ?? '';

                if ($segment === '' || $segment === null) {
                    $action = 'index';
                    $params = [];
                } elseif ($segment === 'checkout') {
                    $action = ($method === 'POST') ? 'checkout' : 'checkoutForm';
                    $params = [];
                } elseif (ctype_digit((string)$segment)) {
                    $action = 'show';
                    $params = [(int)$segment];
                } else {
                    self::error404();
                    return;
                }
            } elseif (($urlParts[0] ?? '') === 'consultations') {
                // Consultations routes:
                // - GET  /consultations        -> ConsultationController::index
                // - POST /consultations/send   -> ConsultationController::send
                $controllerName = 'ConsultationController';
                $segment = $urlParts[1] ?? '';

                if ($segment === '' || $segment === null) {
                    $action = 'index';
                    $params = [];
                } elseif ($segment === 'send') {
                    if ($method !== 'POST') {
                        self::error404();
                        return;
                    }
                    $action = 'send';
                    $params = [];
                } else {
                    self::error404();
                    return;
                }
            } else {
                // Controller
                $controllerName = ucfirst($urlParts[0]) . 'Controller';
                unset($urlParts[0]);

                // Action
                $action = isset($urlParts[1]) ? $urlParts[1] : 'index';
                unset($urlParts[1]);

                // Params
                $params = array_values($urlParts);
            }
        }

        // Kiểm tra controller tồn tại
        if (class_exists($controllerName)) {
            $controller = new $controllerName();
            
            // Kiểm tra method tồn tại
            if (method_exists($controller, $action)) {
                call_user_func_array([$controller, $action], $params);
            } else {
                self::error404();
            }
        } else {
            self::error404();
        }
    }

    private static function error404() {
        http_response_code(404);
        echo "<h1>404 - Trang không tìm thấy</h1>";
        exit;
    }
}

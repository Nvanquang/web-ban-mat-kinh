<?php
// app/core/Router.php

class Router
{
    public static function dispatch()
    {
        // Lấy URL từ request
        $url = isset($_GET['url']) ? rtrim($_GET['url'], '/') : '';
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        // Khởi tạo giá trị mặc định
        $controllerName = 'HomeController';
        $action = 'index';
        $params = [];

        if ($url == '') {
            // Mặc định là HomeController@index
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
                } elseif ($segment !== '' && ctype_digit((string)$segment)) {
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
                } elseif ($segment !== '' && ctype_digit((string)$segment)) {
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
            } elseif (($urlParts[0] ?? '') === 'admin') {
                // Admin routes - require admin access
                $segment = $urlParts[1] ?? '';

                if ($segment === '' || $segment === null) {
                    // GET /admin -> AdminDashboardController::index
                    $controllerName = 'AdminDashboardController';
                    $action = 'index';
                    $params = [];
                } elseif ($segment === 'products') {
                    $controllerName = 'AdminProductController';
                    $subSegment = $urlParts[2] ?? '';

                    if ($subSegment === '' || $subSegment === null) {
                        // GET /admin/products -> index
                        $action = 'index';
                        $params = [];
                    } elseif ($subSegment === 'create') {
                        // GET/POST /admin/products/create
                        $action = ($method === 'POST') ? 'create' : 'createForm';
                        $params = [];
                    } elseif ($subSegment !== '' && ctype_digit((string)$subSegment)) {
                        $id = (int)$subSegment;
                        $subSubSegment = $urlParts[3] ?? '';

                        if ($subSubSegment === 'edit') {
                            // GET/POST /admin/products/{id}/edit
                            $action = ($method === 'POST') ? 'update' : 'editForm';
                            $params = [$id];
                        } elseif ($subSubSegment === 'delete' && $method === 'POST') {
                            // POST /admin/products/{id}/delete
                            $action = 'delete';
                            $params = [$id];
                        } else {
                            self::error404();
                            return;
                        }
                    } else {
                        self::error404();
                        return;
                    }
                } elseif ($segment === 'categories') {
                    $controllerName = 'AdminCategoryController';
                    $subSegment = $urlParts[2] ?? '';

                    if ($subSegment === '' || $subSegment === null) {
                        // GET /admin/categories -> index
                        $action = 'index';
                        $params = [];
                    } elseif ($subSegment === 'create' && $method === 'POST') {
                        // POST /admin/categories/create
                        $action = 'create';
                        $params = [];
                    } elseif ($subSegment !== '' && ctype_digit((string)$subSegment)) {
                        $id = (int)$subSegment;
                        $subSubSegment = $urlParts[3] ?? '';

                        if ($subSubSegment === 'edit') {
                            // GET/POST /admin/categories/{id}/edit
                            $action = ($method === 'POST') ? 'update' : 'editForm';
                            $params = [$id];
                        } elseif ($subSubSegment === 'delete' && $method === 'POST') {
                            // POST /admin/categories/{id}/delete
                            $action = 'delete';
                            $params = [$id];
                        } else {
                            self::error404();
                            return;
                        }
                    } else {
                        self::error404();
                        return;
                    }
                } elseif ($segment === 'orders') {
                    $controllerName = 'AdminOrderController';
                    $subSegment = $urlParts[2] ?? '';

                    if ($subSegment === '' || $subSegment === null) {
                        $action = 'index';
                        $params = [];
                    } elseif ($subSegment !== '' && ctype_digit((string)$subSegment)) {
                        $id = (int)$subSegment;
                        $subSubSegment = $urlParts[3] ?? '';

                        if ($subSubSegment === '' || $subSubSegment === null) {
                            $action = 'show';
                            $params = [$id];
                        } elseif ($subSubSegment === 'status' && $method === 'POST') {
                            $action = 'updateStatus';
                            $params = [$id];
                        } else {
                            self::error404();
                            return;
                        }
                    } else {
                        self::error404();
                        return;
                    }
                } elseif ($segment === 'customers') {
                    $controllerName = 'AdminCustomerController';
                    $subSegment = $urlParts[2] ?? '';

                    if ($subSegment === '' || $subSegment === null) {
                        $action = 'index';
                        $params = [];
                    } elseif ($subSegment !== '' && ctype_digit((string)$subSegment)) {
                        $id = (int)$subSegment;
                        $subSubSegment = $urlParts[3] ?? '';

                        if ($subSubSegment === '' || $subSubSegment === null) {
                            $action = 'show';
                            $params = [$id];
                        } elseif ($subSubSegment === 'ban' && $method === 'POST') {
                            $action = 'toggleBan';
                            $params = [$id];
                        } else {
                            self::error404();
                            return;
                        }
                    } else {
                        self::error404();
                        return;
                    }
                } elseif ($segment === 'consultations') {
                    $controllerName = 'AdminConsultationController';
                    $subSegment = $urlParts[2] ?? '';

                    if ($subSegment === '' || $subSegment === null) {
                        $action = 'index';
                        $params = [];
                    } elseif ($subSegment !== '' && ctype_digit((string)$subSegment)) {
                        $id = (int)$subSegment;
                        $subSubSegment = $urlParts[3] ?? '';

                        if ($subSubSegment === '' || $subSubSegment === null) {
                            $action = 'show';
                            $params = [$id];
                        } elseif ($subSubSegment === 'reply' && $method === 'POST') {
                            $action = 'reply';
                            $params = [$id];
                        } else {
                            self::error404();
                            return;
                        }
                    } else {
                        self::error404();
                        return;
                    }
                } else {
                    self::error404();
                    return;
                }
            } else {
                // Route không khớp bất kỳ nhóm nào
                self::error404();
                return;
            }
        }

        // Gọi controller và action (áp dụng cho mọi route, kể cả trang chủ)
        if (class_exists($controllerName)) {
            $controller = new $controllerName();

            if (method_exists($controller, $action)) {
                call_user_func_array([$controller, $action], $params);
            } else {
                self::error404();
            }
        } else {
            self::error404();
        }
    }

    private static function error404()
    {
        http_response_code(404);
        echo "<h1>404 - Trang không tìm thấy</h1>";
        exit;
    }
}
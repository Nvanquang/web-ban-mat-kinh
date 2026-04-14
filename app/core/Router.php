<?php
// app/core/Router.php

class Router
{
    // Bảng route: [prefix => [controllerName, subRoutes]]
    private static array $routes = [
        ''              => ['HomeController', null],
        'auth'          => ['AuthController', [
            'login'    => ['GET' => 'loginForm',  'POST' => 'login'],
            'register' => ['GET' => 'registerForm', 'POST' => 'register'],
            'logout'   => ['GET' => 'logout'],
        ]],
        'products'      => ['ProductController', null],
        'cart'          => ['CartController', null],
        'orders'        => ['OrderController', null],
        'consultations' => ['ConsultationController', [
            ''     => ['GET' => 'index'],
            'send' => ['POST' => 'send'],
        ]],
        'admin'         => [null, null], // Xử lý riêng
    ];

    public static function dispatch(): void
    {
        $url    = isset($_GET['url']) ? rtrim($_GET['url'], '/') : '';
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $parts  = $url === '' ? [] : explode('/', $url);
        $prefix = $parts[0] ?? '';

        match ($prefix) {
            ''              => self::call('HomeController', 'index'),
            'auth'          => self::dispatchAuth($parts, $method),
            'products'      => self::dispatchProducts($parts, $method),
            'cart'          => self::dispatchCart($parts, $method),
            'orders'        => self::dispatchOrders($parts, $method),
            'consultations' => self::dispatchConsultations($parts, $method),
            'admin'         => self::dispatchAdmin($parts, $method),
            default         => self::error404(),
        };
    }

    // ------------------------------------------------------------------ Auth

    private static function dispatchAuth(array $p, string $m): void
    {
        $map = [
            'login'    => ['GET' => 'loginForm',    'POST' => 'login'],
            'register' => ['GET' => 'registerForm', 'POST' => 'register'],
            'logout'   => ['GET' => 'logout',        'POST' => 'logout'],
        ];
        $seg = $p[1] ?? '';
        if (!isset($map[$seg][$m])) { self::error404(); return; }
        self::call('AuthController', $map[$seg][$m]);
    }

    // --------------------------------------------------------------- Products

    private static function dispatchProducts(array $p, string $m): void
    {
        $seg = $p[1] ?? '';
        if ($seg === '')                        { self::call('ProductController', 'index'); return; }
        if (ctype_digit($seg))                  { self::call('ProductController', 'show', [(int)$seg]); return; }
        // Backward compatibility: /products/{action}/...
        self::call('ProductController', $seg, array_values(array_slice($p, 2)));
    }

    // ------------------------------------------------------------------ Cart

    private static function dispatchCart(array $p, string $m): void
    {
        $seg = $p[1] ?? '';
        if ($seg === '')                        { self::call('CartController', 'index'); return; }
        if ($m !== 'POST')                      { self::error404(); return; }
        self::call('CartController', $seg);
    }

    // ---------------------------------------------------------------- Orders

    private static function dispatchOrders(array $p, string $m): void
    {
        $seg = $p[1] ?? '';
        if ($seg === '')                        { self::call('OrderController', 'index'); return; }
        if ($seg === 'checkout')                { self::call('OrderController', $m === 'POST' ? 'checkout' : 'checkoutForm'); return; }
        if (ctype_digit($seg))                  { self::call('OrderController', 'show', [(int)$seg]); return; }
        self::error404();
    }

    // --------------------------------------------------------- Consultations

    private static function dispatchConsultations(array $p, string $m): void
    {
        $seg = $p[1] ?? '';
        if ($seg === '')                        { self::call('ConsultationController', 'index'); return; }
        if ($seg === 'send' && $m === 'POST')   { self::call('ConsultationController', 'send'); return; }
        self::error404();
    }

    // ----------------------------------------------------------------- Admin

    private static function dispatchAdmin(array $p, string $m): void
    {
        $seg = $p[1] ?? '';

        if ($seg === '') { self::call('AdminDashboardController', 'index'); return; }

        $adminMap = [
            'products'      => 'AdminProductController',
            'categories'    => 'AdminCategoryController',
            'orders'        => 'AdminOrderController',
            'customers'     => 'AdminCustomerController',
            'consultations' => 'AdminConsultationController',
        ];

        if (!isset($adminMap[$seg])) { self::error404(); return; }

        $ctrl     = $adminMap[$seg];
        $sub      = $p[2] ?? '';
        $id       = ctype_digit($sub) ? (int)$sub : null;
        $subSub   = $p[3] ?? '';

        // Danh sách
        if ($sub === '') { self::call($ctrl, 'index'); return; }

        // /admin/{resource}/create
        if ($sub === 'create' && in_array($seg, ['products'])) {
            self::call($ctrl, $m === 'POST' ? 'create' : 'createForm'); return;
        }
        if ($sub === 'create' && $seg === 'categories' && $m === 'POST') {
            self::call($ctrl, 'create'); return;
        }

        if ($id === null) { self::error404(); return; }

        // /admin/{resource}/{id}
        if ($subSub === '') {
            if (in_array($seg, ['orders', 'customers', 'consultations'])) {
                self::call($ctrl, 'show', [$id]); return;
            }
            self::error404(); return;
        }

        // /admin/{resource}/{id}/{action}
        $subActions = [
            'edit'   => ['products', 'categories'],
            'delete' => ['products', 'categories'],
            'status' => ['orders'],
            'ban'    => ['customers'],
            'reply'  => ['consultations'],
        ];

        if (!isset($subActions[$subSub]) || !in_array($seg, $subActions[$subSub])) {
            self::error404(); return;
        }

        $actionMap = [
            'edit'   => ['GET' => 'editForm', 'POST' => 'update'],
            'delete' => ['POST' => 'delete'],
            'status' => ['POST' => 'updateStatus'],
            'ban'    => ['POST' => 'toggleBan'],
            'reply'  => ['POST' => 'reply'],
        ];

        $action = $actionMap[$subSub][$m] ?? null;
        if (!$action) { self::error404(); return; }
        self::call($ctrl, $action, [$id]);
    }

    // ---------------------------------------------------------------- Helpers

    private static function call(string $controller, string $action, array $params = []): void
    {
        if (!class_exists($controller) || !method_exists($controller, $action)) {
            self::error404(); return;
        }
        call_user_func_array([new $controller(), $action], $params);
    }

    private static function error404(): void
    {
        http_response_code(404);
        echo "<h1>404 - Trang không tìm thấy</h1>";
        exit;
    }
}
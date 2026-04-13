<?php
// app/core/Router.php

class Router {
    public static function dispatch() {
        // Lấy URL từ request
        $url = isset($_GET['url']) ? rtrim($_GET['url'], '/') : '';
        
        // Mặc định là HomeController@index
        if ($url == '') {
            $controllerName = 'HomeController';
            $action = 'index';
            $params = [];
        } else {
            $urlParts = explode('/', $url);
            
            // Controller
            $controllerName = ucfirst($urlParts[0]) . 'Controller';
            unset($urlParts[0]);

            // Action
            $action = isset($urlParts[1]) ? $urlParts[1] : 'index';
            unset($urlParts[1]);

            // Params
            $params = array_values($urlParts);
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

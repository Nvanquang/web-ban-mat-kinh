<?php
// app/core/Controller.php

abstract class Controller {
    /**
     * Render view với dữ liệu.
     * Project dùng partial header/footer tuỳ trang; layout là tuỳ chọn.
     */
    protected function render(string $view, array $data = [], string $layout = ''): void {
        // Extract dữ liệu thành biến PHP
        extract($data);

        $viewFile = APPROOT . '/app/views/' . $view . '.php';
        
        if (file_exists($viewFile)) {
            // Nếu không dùng layout thì include view trực tiếp (view tự include header/footer nếu cần)
            if ($layout === '') {
                require_once $viewFile;
                return;
            }

            // Dùng layout (tương thích ngược) - capture nội dung view
            ob_start();
            require_once $viewFile;
            $content = ob_get_clean();
        } else {
            die("View $view không tồn tại.");
        }

        // Include layout và đẩy nội dung vào
        $layoutFile = APPROOT . '/app/views/layouts/' . $layout . '.php';
        if (file_exists($layoutFile)) {
            require_once $layoutFile;
        } else {
            // Nếu không có layout, in trực tiếp content
            echo $content;
        }
    }

    /**
     * Chuyển hướng trang
     */
    protected function redirect(string $url, string $flashType = '', string $flashMsg = ''): void {
        if (!empty($flashType) && !empty($flashMsg)) {
            Session::flash($flashType, $flashMsg);
        }
        header('Location: ' . BASE_URL . $url);
        exit;
    }

    /**
     * Bắt buộc đăng nhập
     */
    protected function requireAuth(): void {
        if (!Session::isLoggedIn()) {
            $this->redirect('/auth/login', 'error', 'Bạn cần đăng nhập để thực hiện thao tác này.');
        }
    }

    /**
     * Bắt buộc quyền admin
     */
    protected function requireAdmin(): void {
        if (!Session::isAdmin()) {
            $this->redirect('/', 'error', 'Bạn không có quyền truy cập khu vực này.');
        }
    }

    /**
     * Lấy dữ liệu POST
     */
    protected function getPost(string $key, mixed $default = null): mixed {
        return isset($_POST[$key]) ? trim($_POST[$key]) : $default;
    }

    /**
     * Lấy dữ liệu GET
     */
    protected function getQuery(string $key, mixed $default = null): mixed {
        return $_GET[$key] ?? $default;
    }
}

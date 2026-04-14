<?php
// app/controllers/admin/AdminCategoryController.php

class AdminCategoryController extends Controller {
    public function __construct() {
        $this->requireAdmin();
    }

    /**
     * GET /admin/categories - Danh sách categories
     */
    public function index(): void {
        $categoryModel = new GlassesCategoryModel();
        $categories = $categoryModel->getAllWithCount();

        $this->render('admin/categories/index', [
            'title' => 'Categories',
            'currentPage' => 'categories',
            'categories' => $categories,
            'oldInput' => Session::getOldInput(),
        ], 'admin');
    }

    /**
     * POST /admin/categories/create - Tạo category mới
     */
    public function create(): void {
        // Validate CSRF
        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!Session::verifyCsrfToken($csrfToken)) {
            http_response_code(403);
            die('Invalid CSRF token.');
        }

        // Sanitize input
        $data = [
            'category_name' => trim($_POST['category_name'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
        ];

        // Validate
        $errors = $this->validateCategory($data);
        if ($errors) {
            Session::flash('error', implode('<br>', $errors));
            Session::setOldInput($data);
            $this->redirect('/admin/categories');
        }

        // Check if name already exists
        $categoryModel = new GlassesCategoryModel();
        if ($categoryModel->nameExists($data['category_name'])) {
            Session::flash('error', 'Tên danh mục đã tồn tại.');
            Session::setOldInput($data);
            $this->redirect('/admin/categories');
        }

        // Create
        $id = $categoryModel->create($data);
        if (!$id) {
            Session::flash('error', 'Không thể tạo danh mục.');
            Session::setOldInput($data);
            $this->redirect('/admin/categories');
        }

        Session::flash('success', 'Danh mục đã được tạo thành công!');
        $this->redirect('/admin/categories');
    }

    /**
     * POST /admin/categories/{id}/edit - Cập nhật category
     */
    public function update(int $id): void {
        // Validate CSRF
        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!Session::verifyCsrfToken($csrfToken)) {
            http_response_code(403);
            die('Invalid CSRF token.');
        }

        // Sanitize input
        $data = [
            'category_name' => trim($_POST['category_name'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
        ];

        // Validate
        $errors = $this->validateCategory($data);
        if ($errors) {
            Session::flash('error', implode('<br>', $errors));
            Session::setOldInput($data);
            $this->redirect('/admin/categories');
        }

        // Check if name already exists (excluding current)
        $categoryModel = new GlassesCategoryModel();
        if ($categoryModel->nameExists($data['category_name'], $id)) {
            Session::flash('error', 'Tên danh mục đã tồn tại.');
            Session::setOldInput($data);
            $this->redirect('/admin/categories');
        }

        // Update
        if (!$categoryModel->update($id, $data)) {
            Session::flash('error', 'Không thể cập nhật danh mục.');
            Session::setOldInput($data);
            $this->redirect('/admin/categories');
        }

        Session::flash('success', 'Danh mục đã được cập nhật thành công!');
        $this->redirect('/admin/categories');
    }

    /**
     * POST /admin/categories/{id}/delete - Xóa category
     */
    public function delete(int $id): void {
        // Validate CSRF
        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!Session::verifyCsrfToken($csrfToken)) {
            http_response_code(403);
            die('Invalid CSRF token.');
        }

        $categoryModel = new GlassesCategoryModel();

        // Check if category has products
        if ($categoryModel->hasProducts($id)) {
            Session::flash('error', 'Không thể xóa danh mục vì nó chứa sản phẩm.');
            $this->redirect('/admin/categories');
        }

        // Delete
        if (!$categoryModel->delete($id)) {
            Session::flash('error', 'Không thể xóa danh mục.');
            $this->redirect('/admin/categories');
        }

        Session::flash('success', 'Danh mục đã được xóa thành công!');
        $this->redirect('/admin/categories');
    }

    /**
     * Validate category data
     */
    private function validateCategory(array $data): array {
        $errors = [];

        if (empty($data['category_name'])) {
            $errors[] = 'Tên danh mục là bắt buộc.';
        } elseif (strlen($data['category_name']) < 2 || strlen($data['category_name']) > 50) {
            $errors[] = 'Tên danh mục phải từ 2-50 ký tự.';
        }

        return $errors;
    }
}
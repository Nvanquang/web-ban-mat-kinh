<?php
// app/controllers/admin/AdminProductController.php

class AdminProductController extends Controller {
    public function __construct() {
        $this->requireAdmin();
    }

    /**
     * GET /admin/products - Danh sách sản phẩm
     */
    public function index(): void {
        $productModel = new ProductModel();
        $categoryModel = new GlassesCategoryModel();

        $page = max(1, (int)($this->getQuery('page', 1)));
        $keyword = trim($this->getQuery('keyword', ''));
        $categoryId = (int)$this->getQuery('category_id', 0);
        $gender = trim($this->getQuery('gender', ''));

        $filters = [];
        if ($keyword !== '') $filters['keyword'] = $keyword;
        if ($categoryId > 0) $filters['category_id'] = $categoryId;
        if ($gender !== '') $filters['gender'] = $gender;

        $result = $productModel->getAdminList($filters, $page, 10);
        $categories = $categoryModel->getAllVisible();

        $this->render('admin/products/index', [
            'title' => 'Products',
            'currentPage' => 'products',
            'products' => $result['items'],
            'pagination' => $result,
            'categories' => $categories,
            'filters' => $filters,
        ], 'admin');
    }

    /**
     * GET /admin/products/create - Form tạo sản phẩm
     */
    public function createForm(): void {
        $categoryModel = new GlassesCategoryModel();
        $categories = $categoryModel->getAllVisible();

        $this->render('admin/products/create', [
            'title' => 'Add Product',
            'currentPage' => 'products',
            'categories' => $categories,
            'oldInput' => Session::getOldInput(),
        ], 'admin');
    }

    /**
     * POST /admin/products/create - Xử lý tạo sản phẩm
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
            'category_id' => (int)($_POST['category_id'] ?? 0),
            'product_name' => trim($_POST['product_name'] ?? ''),
            'price' => (float)($_POST['price'] ?? 0),
            'old_price' => !empty($_POST['old_price']) ? (float)$_POST['old_price'] : null,
            'stock_quantity' => (int)($_POST['stock_quantity'] ?? 0),
            'description' => trim($_POST['description'] ?? ''),
            'gender' => trim($_POST['gender'] ?? 'all'),
            'status' => (int)($_POST['status'] ?? 1),
        ];

        // Validate
        $errors = $this->validateProduct($data);
        if ($errors) {
            Session::flash('error', implode('<br>', $errors));
            Session::setOldInput($data);
            $this->redirect('/admin/products/create');
        }

        // Handle image upload
        $imageUrl = null;
        if (!empty($_FILES['image_url']['name'])) {
            $imageUrl = $this->handleImageUpload($_FILES['image_url'], 'products');
            if (!$imageUrl) {
                Session::flash('error', 'Upload hình ảnh thất bại. Chỉ cho phép JPG/PNG/WEBP dưới 2MB.');
                Session::setOldInput($data);
                $this->redirect('/admin/products/create');
            }
        }
        $data['image_url'] = $imageUrl;

        // Save to database
        $productModel = new ProductModel();
        $id = $productModel->create($data);

        if (!$id) {
            Session::flash('error', 'Thất bại khi tạo sản phẩm. Vui lòng thử lại.');
            Session::setOldInput($data);
            $this->redirect('/admin/products/create');
        }

        Session::flash('success', 'Sản phẩm đã được tạo thành công!');
        $this->redirect('/admin/products');
    }

    /**
     * GET /admin/products/{id}/edit - Form chỉnh sửa sản phẩm
     */
    public function editForm(int $id): void {
        $productModel = new ProductModel();
        $categoryModel = new GlassesCategoryModel();

        $product = $productModel->findByIdAdmin($id);
        if (!$product) {
            $this->redirect('/admin/products');
        }

        $categories = $categoryModel->getAllVisible();

        // Get return URL from query parameter
        $returnUrl = $this->getQuery('return', BASE_URL . '/admin/products');

        $this->render('admin/products/edit', [
            'title' => 'Edit Product',
            'currentPage' => 'products',
            'product' => $product,
            'categories' => $categories,
            'oldInput' => Session::getOldInput(),
            'returnUrl' => $returnUrl,
        ], 'admin');
    }

    /**
     * POST /admin/products/{id}/edit - Xử lý cập nhật sản phẩm
     */
    public function update(int $id): void {
        // Validate CSRF
        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!Session::verifyCsrfToken($csrfToken)) {
            if ($this->isAjaxRequest()) {
                $this->jsonResponse(['success' => false, 'message' => 'Invalid CSRF token.'], 403);
            }
            http_response_code(403);
            die('Invalid CSRF token.');
        }

        // Check if product exists
        $productModel = new ProductModel();
        $existingProduct = $productModel->findByIdAdmin($id);
        if (!$existingProduct) {
            if ($this->isAjaxRequest()) {
                $this->jsonResponse(['success' => false, 'message' => 'Sản phẩm không tồn tại.']);
            }
            $this->redirect('/admin/products');
        }

        // Sanitize input
        $data = [
            'category_id' => (int)($_POST['category_id'] ?? 0),
            'product_name' => trim($_POST['product_name'] ?? ''),
            'price' => (float)($_POST['price'] ?? 0),
            'old_price' => !empty($_POST['old_price']) ? (float)$_POST['old_price'] : null,
            'stock_quantity' => (int)($_POST['stock_quantity'] ?? 0),
            'description' => trim($_POST['description'] ?? ''),
            'gender' => trim($_POST['gender'] ?? 'all'),
            'status' => (int)($_POST['status'] ?? 1),
        ];

        // Validate
        $errors = $this->validateProduct($data);
        if ($errors) {
            if ($this->isAjaxRequest()) {
                $this->jsonResponse(['success' => false, 'message' => implode('<br>', $errors)]);
            }
            Session::flash('error', implode('<br>', $errors));
            Session::setOldInput($data);
            $this->redirect("/admin/products/{$id}/edit");
        }

        // Handle image upload (optional)
        $imageUrl = $existingProduct['image_url']; // Keep existing image
        if (!empty($_FILES['image_url']['name'])) {
            $newImageUrl = $this->handleImageUpload($_FILES['image_url'], 'products');
            if ($newImageUrl) {
                // Delete old image if exists
                if ($imageUrl) {
                    $oldPath = UPLOAD_PATH . $imageUrl;
                    if (file_exists($oldPath)) {
                        unlink($oldPath);
                    }
                }
                $imageUrl = $newImageUrl;
            } else {
                if ($this->isAjaxRequest()) {
                    $this->jsonResponse(['success' => false, 'message' => 'Upload hình ảnh thất bại. Chỉ cho phép JPG/PNG/WEBP dưới 2MB.']);
                }
                Session::flash('error', 'Upload hình ảnh thất bại. Chỉ cho phép JPG/PNG/WEBP dưới 2MB.');
                Session::setOldInput($data);
                $this->redirect("/admin/products/{$id}/edit");
            }
        }
        $data['image_url'] = $imageUrl;

        // Update database
        if (!$productModel->update($id, $data)) {
            if ($this->isAjaxRequest()) {
                $this->jsonResponse(['success' => false, 'message' => 'Cập nhật sản phẩm thất bại. Vui lòng thử lại.']);
            }
            Session::flash('error', 'Cập nhật sản phẩm thất bại. Vui lòng thử lại.');
            Session::setOldInput($data);
            $this->redirect("/admin/products/{$id}/edit");
        }

        if ($this->isAjaxRequest()) {
            $this->jsonResponse(['success' => true, 'message' => 'Sản phẩm đã được cập nhật thành công!', 'redirect_url' => $_POST['redirect_url'] ?? BASE_URL . '/admin/products']);
        }
        // Redirect back to the page where user came from
        $redirectUrl = $_POST['redirect_url'] ?? BASE_URL . '/admin/products';
        header('Location: ' . $redirectUrl);
        exit;
    }

    /**
     * POST /admin/products/{id}/delete - Xóa sản phẩm
     */
    public function delete(int $id): void {
        // Validate CSRF
        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!Session::verifyCsrfToken($csrfToken)) {
            if ($this->isAjaxRequest()) {
                $this->jsonResponse(['success' => false, 'message' => 'Invalid CSRF token.'], 403);
            }
            http_response_code(403);
            die('Invalid CSRF token.');
        }

        // Check if product exists (admin can delete any product regardless of status)
        $productModel = new ProductModel();
        $product = $productModel->findByIdAdmin($id);
        if (!$product) {
            if ($this->isAjaxRequest()) {
                $this->jsonResponse(['success' => false, 'message' => 'Sản phẩm không tồn tại.']);
            }
            Session::flash('error', 'Sản phẩm không tồn tại.');
            $this->redirect('/admin/products');
        }

        // Hard delete - remove image file too
        if ($product['image_url']) {
            $imagePath = UPLOAD_PATH . $product['image_url'];
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }

        if (!$productModel->deleteById($id)) {
            if ($this->isAjaxRequest()) {
                $this->jsonResponse(['success' => false, 'message' => 'Xóa sản phẩm thất bại.']);
            }
            Session::flash('error', 'Xóa sản phẩm thất bại.');
            $this->redirect('/admin/products');
        }

        if ($this->isAjaxRequest()) {
            $this->jsonResponse(['success' => true, 'message' => 'Sản phẩm đã được xóa thành công!']);
        }
        // For non-AJAX requests, redirect back to products list
        $this->redirect('/admin/products');
    }

    /**
     * Validate product data
     */
    private function validateProduct(array $data): array {
        $errors = [];

        if (empty($data['product_name'])) {
            $errors[] = 'Tên sản phẩm là bắt buộc.';
        } elseif (strlen($data['product_name']) < 2 || strlen($data['product_name']) > 100) {
            $errors[] = 'Tên sản phẩm phải từ 2-100 ký tự.';
        }

        if ($data['category_id'] <= 0) {
            $errors[] = 'Vui lòng chọn danh mục.';
        }

        if ($data['price'] <= 0) {
            $errors[] = 'Giá phải lớn hơn 0.';
        }

        if ($data['old_price'] !== null && $data['old_price'] <= $data['price']) {
            $errors[] = 'Giá cũ phải lớn hơn giá hiện tại.';
        }

        if ($data['stock_quantity'] < 0) {
            $errors[] = 'Số lượng tồn kho không thể âm.';
        }

        if (!isset($data['gender']) || !in_array($data['gender'], ['male', 'female', 'all'])) {
            $errors[] = 'Giới tính không hợp lệ.';
        }

        return $errors;
    }

    /**
     * Handle image upload
     */
    private function handleImageUpload(array $file, string $folder): string|false {
        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return false;
        }

        // Check file size (2MB)
        if ($file['size'] > 2 * 1024 * 1024) {
            return false;
        }

        // Check MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];
        if (!in_array($mimeType, $allowedMimes)) {
            return false;
        }

        // Check file extension
        $allowedExts = ['jpg', 'jpeg', 'png', 'webp'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedExts)) {
            return false;
        }

        // Generate safe filename
        $newFilename = uniqid('img_', true) . '.' . $ext;

        // Ensure upload directory exists
        $uploadDir = UPLOAD_PATH . $folder . '/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $uploadDir . $newFilename)) {
            return false;
        }

        return $folder . '/' . $newFilename;
    }
}
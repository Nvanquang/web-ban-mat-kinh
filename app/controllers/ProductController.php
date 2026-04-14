<?php
// app/controllers/ProductController.php

class ProductController extends Controller {
    /**
     * Danh sách sản phẩm
     */
    public function index() {
        $page = max(1, (int)($this->getQuery('page', 1)));
        $categoryId = max(0, (int)($this->getQuery('category', 0)));
        $minPrice = max(0, (float)($this->getQuery('min_price', 0)));
        $maxPrice = max(0, (float)($this->getQuery('max_price', 0)));
        $keyword = trim((string)($this->getQuery('keyword', $this->getQuery('q', ''))));
        if (mb_strlen($keyword) > 100) $keyword = mb_substr($keyword, 0, 100);

        if ($maxPrice > 0 && $maxPrice < $minPrice) {
            $maxPrice = 0; // ignore
        }

        $sort = (string)($this->getQuery('sort', 'newest'));
        $allowedSort = ['newest', 'price_asc', 'price_desc', 'popular'];
        if (!in_array($sort, $allowedSort, true)) $sort = 'newest';

        $gender = trim((string)$this->getQuery('gender', ''));
        if (!in_array($gender, ['male', 'female', 'all'])) $gender = '';

        $filters = [
            'category_id' => $categoryId,
            'min_price'   => $minPrice,
            'max_price'   => $maxPrice,
            'keyword'     => $keyword,
            'sort'        => $sort,
            'gender'      => $gender
        ];

        $productModel = new ProductModel();
        $result = $productModel->getPaginated($page, 12, $filters);
        if (!empty($result['error'])) {
            http_response_code(500);
            $this->render('errors/500', ['title' => 'Server Error']);
            return;
        }

        $categoryModel = new GlassesCategoryModel();
        $categories = $categoryModel->getAllVisible();

        $this->render('products/index', [
            'products'   => $result['items'],
            'pagination' => $result,
            'categories' => $categories,
            'filters'    => $filters,
            'title'      => 'Products',
        ]);
    }

    /**
     * Chi tiết sản phẩm
     */
    public function show($id) {
        $id = (int)$id;
        if ($id <= 0) {
            $this->redirect('/products');
        }

        $productModel = new ProductModel();
        $product = $productModel->getProductWithCategory($id);

        if (!$product) {
            http_response_code(404);
            $this->render('errors/404', ['title' => 'Page Not Found']);
            return;
        }

        $productModel->incrementViewCount($id);
        $related = [];
        $categoryId = (int)($product['category_id'] ?? 0);
        if ($categoryId > 0) {
            $related = $productModel->getRelated($id, $categoryId, 4);
        }

        $this->render('products/detail', [
            'product' => $product,
            'related' => $related,
            'title'   => $product['product_name']
        ]);
    }
}

<?php
// app/controllers/ProductController.php

class ProductController extends Controller {
    /**
     * Danh sách sản phẩm
     */
    public function index() {
        $productModel = new ProductModel();
        
        // Xử lý tìm kiếm nếu có
        $query = $this->getQuery('q');
        if ($query) {
            $products = $productModel->searchProducts($query);
        } else {
            $products = $productModel->getShopProducts(12);
        }

        $this->render('products/index', [
            'products' => $products,
            'title'    => 'Cửa hàng - ' . APP_NAME
        ]);
    }

    /**
     * Chi tiết sản phẩm
     */
    public function show($id) {
        $productModel = new ProductModel();
        $product = $productModel->findById((int)$id);

        if (!$product) {
            $this->redirect('/products', 'error', 'Sản phẩm không tồn tại.');
        }

        $this->render('products/detail', [
            'product' => $product,
            'title'   => $product['product_name'] . ' - ' . APP_NAME
        ]);
    }
}

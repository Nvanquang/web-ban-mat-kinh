<?php
// app/controllers/HomeController.php

class HomeController extends Controller {
    public function index() {
        $productModel = new ProductModel();
        $featuredProducts = $productModel->getFeaturedProducts(4);

        $this->render('home/index', [
            'products' => $featuredProducts,
            'title'    => 'Trang chủ - ' . APP_NAME
        ]);
    }
}

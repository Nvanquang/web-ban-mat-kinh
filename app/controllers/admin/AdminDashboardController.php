<?php
// app/controllers/admin/AdminDashboardController.php

class AdminDashboardController extends Controller {
    public function __construct() {
        $this->requireAdmin();
    }

    /**
     * GET /admin - Dashboard chính
     */
    public function index(): void {
        // Lấy dữ liệu thống kê
        $orderModel = new OrderModel();
        $customerModel = new CustomerModel();
        $productModel = new ProductModel();
        $consultationModel = new ConsultationModel();

        $revenue = $orderModel->getTotalRevenueThisMonth();
        $orderStats = $orderModel->countByStatus();
        $activeCustomers = $customerModel->countActive();
        $sellingProducts = $productModel->countSelling();
        $recentOrders = $orderModel->getRecentOrders(5);
        $topProducts = $productModel->getTopSellingProducts(5);
        $pendingConsultations = $consultationModel->getPending(3);

        $this->render('admin/dashboard/index', [
            'title' => 'Dashboard',
            'currentPage' => 'dashboard',
            'revenue' => $revenue,
            'orderStats' => $orderStats,
            'activeCustomers' => $activeCustomers,
            'sellingProducts' => $sellingProducts,
            'recentOrders' => $recentOrders,
            'topProducts' => $topProducts,
            'pendingConsultations' => $pendingConsultations,
        ], 'admin');
    }
}
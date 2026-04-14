<?php
// app/controllers/admin/AdminOrderController.php

class AdminOrderController extends Controller {
    private OrderModel $orderModel;

    public function __construct() {
        $this->requireAdmin();
        $this->orderModel = new OrderModel();
    }

    public function index(): void {
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $status = $_GET['status'] ?? 'all';

        $filters = ['status' => $status];
        $result = $this->orderModel->getAdminList($filters, $page, 10);

        $this->render('admin/orders/index', [
            'title' => 'Orders Management',
            'currentPage' => 'orders',
            'orders' => $result['data'],
            'total' => $result['total'],
            'current_page' => $result['current_page'],
            'lastPage' => $result['last_page'],
            'currentStatus' => $status
        ], 'admin');
    }

    public function show(int $id): void {
        $order = $this->orderModel->getOrderWithDetails($id);
        if (!$order) {
            $this->redirect('/admin/orders');
            return;
        }

        $this->render('admin/orders/detail', [
            'title' => 'Order Detail',
            'currentPage' => 'orders',
            'order' => $order
        ], 'admin');
    }

    public function updateStatus(int $id): void {
        if (!Session::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            Session::flash('error', 'Invalid CSRF token.');
            $this->redirect("/admin/orders/{$id}");
            return;
        }

        $order = $this->orderModel->getOrderWithDetails($id);
        if (!$order) {
            $this->redirect('/admin/orders');
            return;
        }

        $newStatus = trim($_POST['status'] ?? '');
        $validStatuses = ['confirmed', 'shipped', 'completed', 'cancelled'];

        if (!in_array($newStatus, $validStatuses, true)) {
            Session::flash('error', 'Invalid status.');
            $this->redirect("/admin/orders/{$id}");
            return;
        }

        $currentStatus = $order['status'];
        $isValidTransition = false;

        switch ($currentStatus) {
            case 'pending':
                if (in_array($newStatus, ['confirmed', 'cancelled'])) {
                    $isValidTransition = true;
                }
                break;
            case 'confirmed':
                if (in_array($newStatus, ['shipped', 'cancelled'])) {
                    $isValidTransition = true;
                }
                break;
            case 'shipped':
                if (in_array($newStatus, ['completed', 'cancelled'])) {
                    $isValidTransition = true;
                }
                break;
            case 'completed':
            case 'cancelled':
                $isValidTransition = false; // Cannot change from final state
                break;
        }

        if (!$isValidTransition) {
            Session::flash('error', "Cannot change status from {$currentStatus}.");
            $this->redirect("/admin/orders/{$id}");
            return;
        }

        if ($this->orderModel->updateStatus($id, $newStatus)) {
            Session::flash('success', "Order status updated to {$newStatus}.");
        } else {
            Session::flash('error', 'Operation failed.');
        }

        $this->redirect("/admin/orders/{$id}");
    }
}

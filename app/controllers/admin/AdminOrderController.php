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
            'title' => 'Quản lý đơn hàng',
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
            'title' => 'Chi tiết đơn hàng',
            'currentPage' => 'orders',
            'order' => $order
        ], 'admin');
    }

    public function updateStatus(int $id): void {
        if (!Session::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            Session::flash('error', 'Token CSRF không hợp lệ.');
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
            Session::flash('error', 'Trạng thái không hợp lệ.');
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
                $isValidTransition = false; // Không thể thay đổi từ trạng thái cuối
                break;
        }

        if (!$isValidTransition) {
            Session::flash('error', "Không thể chuyển trạng thái từ '{$currentStatus}'.");
            $this->redirect("/admin/orders/{$id}");
            return;
        }

        if ($this->orderModel->updateStatus($id, $newStatus)) {
            // Nếu hủy đơn hàng, thực hiện hoàn kho
            if ($newStatus === 'cancelled') {
                $this->orderModel->returnStock($id);
            }

            $statusName = $this->getStatusName($newStatus); // Tên tiếng Việt đẹp hơn
            Session::flash('success', "Trạng thái đơn hàng đã được cập nhật thành: {$statusName}.");
        } else {
            Session::flash('error', 'Thao tác thất bại. Vui lòng thử lại.');
        }

        $this->redirect("/admin/orders/{$id}");
    }

    /**
     * Chuyển trạng thái code sang tên tiếng Việt dễ đọc
     */
    private function getStatusName(string $status): string {
        $names = [
            'pending'    => 'Chờ xác nhận',
            'confirmed'  => 'Đã xác nhận',
            'shipped'    => 'Đang giao hàng',
            'completed'  => 'Hoàn thành',
            'cancelled'  => 'Đã hủy'
        ];

        return $names[$status] ?? ucfirst($status);
    }
}
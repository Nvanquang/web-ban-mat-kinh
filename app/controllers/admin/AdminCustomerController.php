<?php
// app/controllers/admin/AdminCustomerController.php

class AdminCustomerController extends Controller {
    private CustomerModel $customerModel;
    private OrderModel $orderModel;

    public function __construct() {
        $this->requireAdmin();
        $this->customerModel = new CustomerModel();
        $this->orderModel = new OrderModel();
    }

    public function index(): void {
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $search = trim($_GET['search'] ?? '');

        $result = $this->customerModel->getAdminList($search, $page, 10);

        $this->render('admin/customers/index', [
            'title' => 'Customers Management',
            'currentPage' => 'customers',
            'customers' => $result['data'],
            'total' => $result['total'],
            'current_page' => $result['current_page'],
            'lastPage' => $result['last_page'],
            'search' => $search
        ], 'admin');
    }

    public function show(int $id): void {
        $customer = $this->customerModel->findById($id);
        if (!$customer) {
            $this->redirect('/admin/customers');
            return;
        }

        $orders = $this->orderModel->getOrdersByCustomer($id);

        $this->render('admin/customers/detail', [
            'title' => 'Customer Profile',
            'currentPage' => 'customers',
            'customer' => $customer,
            'orders' => $orders
        ], 'admin');
    }

    public function toggleBan(int $id): void {
        if (!Session::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            Session::flash('error', 'Token CSRF không hợp lệ.');
            $this->redirect('/admin/customers');
            return;
        }

        $customer = $this->customerModel->findById($id);
        if (!$customer) {
            $this->redirect('/admin/customers');
            return;
        }

        $currentUser = Session::get('user');
        if ($currentUser && $currentUser['id'] == $id) {
            Session::flash('error', 'Bạn không thể cấm tài khoản của chính mình.');
            $this->redirect('/admin/customers');
            return;
        }

        if ($customer['role'] === 'admin') {
            Session::flash('error', 'Không thể cấm tài khoản quản trị viên.');
            $this->redirect('/admin/customers');
            return;
        }

        $newStatus = ($customer['status'] === 'active') ? 'banned' : 'active';
        if ($this->customerModel->updateCustomerStatus($id, $newStatus)) {
            $action = ($newStatus === 'banned') ? 'đã bị cấm' : 'đã được gỡ cấm';
            Session::flash('success', "{$customer['full_name']} {$action}.");
        } else {
            Session::flash('error', 'Thao tác thất bại. Vui lòng thử lại.');
        }

        $this->redirect('/admin/customers');
    }
}

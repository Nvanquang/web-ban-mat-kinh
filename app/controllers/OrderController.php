<?php
// app/controllers/OrderController.php

class OrderController extends Controller {
    public function index(): void {
        $this->requireAuth();
        $customerId = (int)Session::getUser()['id'];

        $model = new OrderModel();
        $orders = $model->getOrdersByCustomer($customerId);

        $this->render('orders/index', [
            'title'  => 'Đơn hàng của tôi',
            'orders' => $orders,
        ]);
    }

    public function show(int $id): void {
        $this->requireAuth();

        $id = (int)$id;
        if ($id <= 0) {
            $this->redirect('/orders');
        }

        $model = new OrderModel();
        $order = $model->getOrderWithDetails($id);
        if (!$order) {
            Router::error404();
            return;
        }

        $customerId = (int)Session::getUser()['id'];
        if ((int)($order['customer_id'] ?? 0) !== $customerId) {
            Router::error403();
            return;
        }

        $this->render('orders/detail', [
            'title' => 'Đơn hàng #' . (int)$order['id'],
            'order' => $order,
        ]);
    }

    public function checkoutForm(): void {
        $this->requireAuth();
        $customerId = (int)Session::getUser()['id'];

        $cartModel = new CartItemModel();
        $rows = $cartModel->getItemsWithProduct($customerId);
        $cart = [];
        foreach ($rows as $row) {
            if ((int)($row['status'] ?? 0) !== 1) continue;
            $pid = (int)$row['product_id'];
            $cart[$pid] = [
                'id'           => $pid,
                'product_name' => (string)($row['product_name'] ?? ''),
                'price'        => (float)($row['sale_price'] ?? 0),
                'quantity'     => (int)($row['quantity'] ?? 0),
                'image_url'    => (string)($row['image_url'] ?? ''),
            ];
        }

        if (empty($cart)) {
            Session::flash('warning', 'Giỏ hàng của bạn đang trống.');
            $this->redirect('/cart');
        }

        $subtotal = 0.0;
        foreach ($cart as $item) {
            $subtotal += ((float)$item['price']) * ((int)$item['quantity']);
        }
        $shipping = $subtotal >= 500000 ? 0 : 30000;
        $total = $subtotal + $shipping;

        $customerModel = new CustomerModel();
        $customer = $customerModel->findById($customerId);

        $this->render('orders/checkout', [
            'title'    => 'Thanh toán',
            'cart'     => $cart,
            'subtotal' => $subtotal,
            'shipping' => $shipping,
            'total'    => $total,
            'customer' => $customer ?: [],
            'oldInput' => Session::getOldInput(),
        ]);
    }

    public function checkout(): void {
        $this->requireAuth();
        $customerId = (int)Session::getUser()['id'];

        $token = (string)($_POST['csrf_token'] ?? '');
        if (!Session::verifyCsrfToken($token)) {
            http_response_code(403);
            Session::flash('error', 'Token CSRF không hợp lệ.');
            $this->redirect('/orders/checkout');
        }

        $cartModel = new CartItemModel();
        $rows = $cartModel->getItemsWithProduct($customerId);
        $cartItems = [];
        foreach ($rows as $row) {
            if ((int)($row['status'] ?? 0) !== 1) continue;
            $pid = (int)$row['product_id'];
            $cartItems[] = [
                'id'       => $pid,
                'quantity' => (int)($row['quantity'] ?? 0),
                'price'    => (float)($row['sale_price'] ?? 0),
                'name'     => (string)($row['product_name'] ?? ''),
            ];
        }
        if (empty($cartItems)) {
            $this->redirect('/cart');
        }

        $shippingData = [
            'receiver_name'    => trim((string)($_POST['receiver_name'] ?? '')),
            'receiver_phone'   => trim((string)($_POST['receiver_phone'] ?? '')),
            'shipping_address' => trim((string)($_POST['shipping_address'] ?? '')),
            'note'             => trim((string)($_POST['note'] ?? '')),
            'payment_method'   => (string)($_POST['payment_method'] ?? 'COD'),
        ];

        $errors = $this->validateCheckout($shippingData);
        if ($errors) {
            Session::flash('error', implode(' ', $errors));
            Session::setOldInput([
                'receiver_name'    => $shippingData['receiver_name'],
                'receiver_phone'   => $shippingData['receiver_phone'],
                'shipping_address' => $shippingData['shipping_address'],
                'note'             => $shippingData['note'],
                'payment_method'   => $shippingData['payment_method'],
            ]);
            $this->redirect('/orders/checkout');
        }

        // Verify stock (fresh DB)
        $productModel = new ProductModel();
        foreach ($cartItems as $item) {
            $p = $productModel->findById((int)$item['id']);
            if (!$p) {
                Session::flash('error', 'Sản phẩm không khả dụng.');
                $this->redirect('/cart');
            }
            $stock = (int)($p['stock_quantity'] ?? 0);
            if ((int)$item['quantity'] > $stock) {
                Session::flash('error', 'Sản phẩm "' . (string)($p['product_name'] ?? '') . '" chỉ còn ' . $stock . ' sản phẩm.');
                $this->redirect('/cart');
            }
        }

        $orderModel = new OrderModel();
        $orderId = $orderModel->createOrder($customerId, $shippingData, $cartItems);
        if (!$orderId) {
            Session::flash('error', 'Đặt hàng thất bại. Vui lòng thử lại sau.');
            Session::setOldInput([
                'receiver_name'    => $shippingData['receiver_name'],
                'receiver_phone'   => $shippingData['receiver_phone'],
                'shipping_address' => $shippingData['shipping_address'],
                'note'             => $shippingData['note'],
                'payment_method'   => $shippingData['payment_method'],
            ]);
            $this->redirect('/orders/checkout');
        }

        // Clear DB cart after success
        $cartModel->clearCart($customerId);

        Session::flash('success', 'Đặt hàng thành công! Đơn hàng #' . $orderId);
        $this->redirect('/orders/' . $orderId);
    }

    private function validateCheckout(array $data): array {
        $errors = [];

        $name = $data['receiver_name'] ?? '';
        if ($name === '') $errors[] = 'Vui lòng nhập họ tên người nhận.';
        $len = mb_strlen((string)$name);
        if ($name !== '' && ($len < 2 || $len > 100)) $errors[] = 'Họ tên phải từ 2 đến 100 ký tự.';

        $phone = $data['receiver_phone'] ?? '';
        if ($phone === '') $errors[] = 'Vui lòng nhập số điện thoại.';
        if ($phone !== '' && !preg_match('/^(0[3|5|7|8|9])[0-9]{8}$/', (string)$phone)) {
            $errors[] = 'Số điện thoại không hợp lệ.';
        }

        $addr = $data['shipping_address'] ?? '';
        if ($addr === '') $errors[] = 'Vui lòng nhập địa chỉ giao hàng.';
        $alen = mb_strlen((string)$addr);
        if ($addr !== '' && ($alen < 10 || $alen > 500)) $errors[] = 'Địa chỉ phải từ 10 đến 500 ký tự.';

        $note = (string)($data['note'] ?? '');
        if (mb_strlen($note) > 500) $data['note'] = mb_substr($note, 0, 500);

        $allowed = ['COD', 'Bank Transfer', 'VNPay'];
        if (!in_array((string)($data['payment_method'] ?? 'COD'), $allowed, true)) {
            $data['payment_method'] = 'COD';
        }

        return $errors;
    }
}
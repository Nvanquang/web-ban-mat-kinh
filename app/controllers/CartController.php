<?php
// app/controllers/CartController.php

class CartController extends Controller {
    public function index(): void {
        $this->requireAuth();

        $customerId = (int)Session::getUser()['id'];
        $cartModel = new CartItemModel();
        $rows = $cartModel->getItemsWithProduct($customerId);

        $cart = [];
        foreach ($rows as $row) {
            if ((int)($row['status'] ?? 0) !== 1) continue;
            $pid = (int)($row['product_id'] ?? 0);
            if ($pid <= 0) continue;
            $cart[$pid] = [
                'id'           => $pid,
                'product_name' => (string)($row['product_name'] ?? ''),
                'price'        => (float)($row['sale_price'] ?? 0),
                'quantity'     => (int)($row['quantity'] ?? 0),
                'image_url'    => (string)($row['image_url'] ?? ''),
            ];
        }

        $subtotal = 0.0;
        foreach ($cart as $item) {
            $subtotal += ((float)($item['price'] ?? 0)) * ((int)($item['quantity'] ?? 0));
        }
        $shipping = $subtotal >= 500000 ? 0 : 30000;
        $total = $subtotal + $shipping;

        $this->render('cart/index', [
            'title'    => 'Shopping Cart',
            'cart'     => $cart,
            'subtotal' => $subtotal,
            'shipping' => $shipping,
            'total'    => $total,
        ]);
    }

    public function add(): void {
        $this->requireAuth();
        $this->verifyCsrfOr403('/auth/login', 'Please login to add items to cart.');

        $customerId = (int)Session::getUser()['id'];
        $productId = (int)($_POST['product_id'] ?? 0);
        $quantity = max(1, (int)($_POST['quantity'] ?? 1));

        if ($productId <= 0) {
            $this->redirect('/products');
        }

        $productModel = new ProductModel();
        $product = $productModel->findById($productId);

        if (!$product || (int)($product['status'] ?? 0) !== 1) {
            Session::flash('error', 'Product not available.');
            $this->redirect('/products');
        }

        $stock = (int)($product['stock_quantity'] ?? 0);
        $cartModel = new CartItemModel();
        $currentQty = $cartModel->getItemQuantity($customerId, $productId);
        $newQty = $currentQty + $quantity;
        if ($newQty > $stock) {
            Session::flash('error', 'Only ' . $stock . ' items available in stock.');
            $this->redirect('/products/' . $productId);
        }

        $cartModel->upsertAdd($customerId, $productId, $quantity, (float)($product['price'] ?? 0));

        Session::flash('success', (string)($product['product_name'] ?? 'Product') . ' added to cart!');
        $this->redirect($this->safeBackPath('/products/' . $productId));
    }

    public function addAjax(): void {
        // AJAX add-to-cart: no redirect, return JSON
        $this->requireAuth();
        $customerId = (int)Session::getUser()['id'];

        $token = (string)($_POST['csrf_token'] ?? '');
        if (!Session::verifyCsrfToken($token)) {
            http_response_code(403);
            $this->json(['success' => false, 'message' => 'Invalid CSRF token.']);
        }

        $productId = (int)($_POST['product_id'] ?? 0);
        $quantity = max(1, (int)($_POST['quantity'] ?? 1));
        if ($productId <= 0) {
            http_response_code(400);
            $this->json(['success' => false, 'message' => 'Invalid product.']);
        }

        $productModel = new ProductModel();
        $product = $productModel->findById($productId);
        if (!$product || (int)($product['status'] ?? 0) !== 1) {
            http_response_code(404);
            $this->json(['success' => false, 'message' => 'Product not available.']);
        }

        $stock = (int)($product['stock_quantity'] ?? 0);
        $cartModel = new CartItemModel();
        $currentQty = $cartModel->getItemQuantity($customerId, $productId);
        $newQty = $currentQty + $quantity;
        if ($newQty > $stock) {
            http_response_code(409);
            $this->json(['success' => false, 'message' => 'Only ' . $stock . ' items available in stock.']);
        }

        $cartModel->upsertAdd($customerId, $productId, $quantity, (float)($product['price'] ?? 0));
        $count = $cartModel->countQuantity($customerId);

        $this->json([
            'success'    => true,
            'message'    => (string)($product['product_name'] ?? 'Product') . ' added to cart!',
            'cart_count' => $count,
        ]);
    }

    public function update(): void {
        $this->requireAuth();
        $this->verifyCsrfOr403('/cart');
        $customerId = (int)Session::getUser()['id'];

        $productId = (int)($_POST['product_id'] ?? 0);
        $quantity = max(1, (int)($_POST['quantity'] ?? 1));

        if ($productId <= 0) {
            $this->redirect('/products');
        }

        $cartModel = new CartItemModel();
        if ($cartModel->getItemQuantity($customerId, $productId) <= 0) {
            Session::flash('error', 'Item not found in cart.');
            $this->redirect('/cart');
        }

        $productModel = new ProductModel();
        $product = $productModel->findById($productId);
        if (!$product || (int)($product['status'] ?? 0) !== 1) {
            Session::flash('error', 'Product not available.');
            $cartModel->removeItem($customerId, $productId);
            $this->redirect('/cart');
        }

        $stock = (int)($product['stock_quantity'] ?? 0);
        if ($quantity > $stock) {
            Session::flash('error', 'Only ' . $stock . ' items available in stock.');
            $this->redirect('/cart');
        }

        $cartModel->updateQuantity($customerId, $productId, $quantity);

        Session::flash('success', 'Cart updated.');
        $this->redirect('/cart');
    }

    public function updateAjax(): void {
        $this->requireAuth();
        $customerId = (int)Session::getUser()['id'];

        $token = (string)($_POST['csrf_token'] ?? '');
        if (!Session::verifyCsrfToken($token)) {
            http_response_code(403);
            $this->json(['success' => false, 'message' => 'Invalid CSRF token.']);
        }

        $productId = (int)($_POST['product_id'] ?? 0);
        $quantity = max(1, (int)($_POST['quantity'] ?? 1));
        if ($productId <= 0) {
            http_response_code(400);
            $this->json(['success' => false, 'message' => 'Invalid product.']);
        }

        $cartModel = new CartItemModel();
        if ($cartModel->getItemQuantity($customerId, $productId) <= 0) {
            http_response_code(404);
            $this->json(['success' => false, 'message' => 'Item not found in cart.']);
        }

        $productModel = new ProductModel();
        $product = $productModel->findById($productId);
        if (!$product || (int)($product['status'] ?? 0) !== 1) {
            $cartModel->removeItem($customerId, $productId);
            http_response_code(404);
            $this->json(['success' => false, 'message' => 'Product not available.']);
        }

        $stock = (int)($product['stock_quantity'] ?? 0);
        if ($quantity > $stock) {
            http_response_code(409);
            $this->json(['success' => false, 'message' => 'Only ' . $stock . ' items available in stock.']);
        }

        $cartModel->updateQuantity($customerId, $productId, $quantity);
        $rows = $cartModel->getItemsWithProduct($customerId);

        $subtotal = 0.0;
        $count = 0;
        $unitPrice = 0.0;
        foreach ($rows as $it) {
            if ((int)($it['status'] ?? 0) !== 1) continue;
            $subtotal += ((float)($it['sale_price'] ?? 0)) * ((int)($it['quantity'] ?? 0));
            $count += (int)($it['quantity'] ?? 0);
            if ((int)($it['product_id'] ?? 0) === $productId) {
                $unitPrice = (float)($it['sale_price'] ?? 0);
            }
        }
        $shipping = $subtotal >= 500000 ? 0 : ($subtotal > 0 ? 30000 : 0);
        $total = $subtotal + $shipping;

        $rowTotal = $unitPrice * $quantity;

        $this->json([
            'success'     => true,
            'message'     => 'Cart updated.',
            'cart_count'  => $count,
            'product_id'  => $productId,
            'quantity'    => $quantity,
            'row_total'   => $rowTotal,
            'subtotal'    => $subtotal,
            'shipping'    => $shipping,
            'total'       => $total,
            'empty'       => empty($cart),
        ]);
    }

    public function remove(): void {
        $this->requireAuth();
        $this->verifyCsrfOr403('/cart');
        $customerId = (int)Session::getUser()['id'];

        $productId = (int)($_POST['product_id'] ?? 0);
        if ($productId > 0) {
            $cartModel = new CartItemModel();
            $cartModel->removeItem($customerId, $productId);
            Session::flash('success', 'Item removed.');
        }

        $this->redirect('/cart');
    }

    public function removeAjax(): void {
        $this->requireAuth();
        $customerId = (int)Session::getUser()['id'];

        $token = (string)($_POST['csrf_token'] ?? '');
        if (!Session::verifyCsrfToken($token)) {
            http_response_code(403);
            $this->json(['success' => false, 'message' => 'Invalid CSRF token.']);
        }

        $productId = (int)($_POST['product_id'] ?? 0);
        $cartModel = new CartItemModel();
        if ($productId > 0) {
            $cartModel->removeItem($customerId, $productId);
        }

        $rows = $cartModel->getItemsWithProduct($customerId);
        $subtotal = 0.0;
        $count = 0;
        foreach ($rows as $it) {
            if ((int)($it['status'] ?? 0) !== 1) continue;
            $subtotal += ((float)($it['sale_price'] ?? 0)) * ((int)($it['quantity'] ?? 0));
            $count += (int)($it['quantity'] ?? 0);
        }
        $shipping = $subtotal >= 500000 ? 0 : ($subtotal > 0 ? 30000 : 0);
        $total = $subtotal + $shipping;

        $this->json([
            'success'    => true,
            'message'    => 'Item removed.',
            'cart_count' => $count,
            'subtotal'   => $subtotal,
            'shipping'   => $shipping,
            'total'      => $total,
            'empty'      => empty($cart),
        ]);
    }

    public function clear(): void {
        $this->requireAuth();
        $this->verifyCsrfOr403('/cart');
        $customerId = (int)Session::getUser()['id'];

        $cartModel = new CartItemModel();
        $cartModel->clearCart($customerId);
        Session::flash('success', 'Cart cleared.');
        $this->redirect('/cart');
    }

    private function verifyCsrfOr403(string $redirectPath, string $messageIfGuest = ''): void {
        $token = (string)($_POST['csrf_token'] ?? '');
        if (!Session::verifyCsrfToken($token)) {
            http_response_code(403);
            if (!Session::isLoggedIn() && $messageIfGuest !== '') {
                Session::flash('warning', $messageIfGuest);
                $this->redirect($redirectPath);
            }
            Session::flash('error', 'Invalid CSRF token.');
            $this->redirect($redirectPath);
        }
    }

    private function safeBackPath(string $fallbackPath): string {
        $ref = (string)($_SERVER['HTTP_REFERER'] ?? '');
        if ($ref === '') return $fallbackPath;

        // Chỉ cho phép redirect nội bộ (tránh open redirect)
        if (stripos($ref, BASE_URL) !== 0) return $fallbackPath;

        $path = (string)parse_url($ref, PHP_URL_PATH);
        $query = (string)parse_url($ref, PHP_URL_QUERY);

        if ($path === '') return $fallbackPath;
        $path = str_replace(parse_url(BASE_URL, PHP_URL_PATH) ?: '', '', $path);
        $path = '/' . ltrim($path, '/');

        return $query !== '' ? ($path . '?' . $query) : $path;
    }

    private function json(array $payload): void {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE);
        exit;
    }
}


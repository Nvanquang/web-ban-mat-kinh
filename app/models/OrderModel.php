<?php
// app/models/OrderModel.php

class OrderModel extends Model {
    protected string $table = 'orders';

    public function getOrdersByCustomer(int $customerId): array {
        try {
            $stmt = $this->db->prepare("
                SELECT id, order_date, status, total_amount, payment_method
                FROM {$this->table}
                WHERE customer_id = ?
                ORDER BY order_date DESC
            ");
            $stmt->execute([$customerId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('OrderModel::getOrdersByCustomer error: ' . $e->getMessage());
            return [];
        }
    }

    public function getOrderWithDetails(int $orderId): array|false {
        try {
            $stmt = $this->db->prepare("
                SELECT
                    o.*,
                    c.username,
                    c.email,
                    c.full_name AS account_name
                FROM {$this->table} o
                LEFT JOIN customers c ON o.customer_id = c.id
                WHERE o.id = ?
                LIMIT 1
            ");
            $stmt->execute([$orderId]);
            $order = $stmt->fetch();
            if (!$order) return false;

            $stmt2 = $this->db->prepare("
                SELECT
                    od.product_id,
                    od.quantity,
                    od.sale_price,
                    p.product_name,
                    p.image_url
                FROM order_details od
                LEFT JOIN products p ON od.product_id = p.id
                WHERE od.order_id = ?
                ORDER BY od.id ASC
            ");
            $stmt2->execute([$orderId]);
            $order['items'] = $stmt2->fetchAll();

            return $order;
        } catch (PDOException $e) {
            error_log('OrderModel::getOrderWithDetails error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * @param array<int,array{id:int,quantity:int,price:float}> $cartItems
     */
    public function createOrder(int $customerId, array $shipping, array $cartItems): int|false {
        try {
            $this->db->beginTransaction();

            $totalAmount = 0.0;
            foreach ($cartItems as $item) {
                $totalAmount += ((float)$item['price']) * ((int)$item['quantity']);
            }

            $paymentMethod = (string)($shipping['payment_method'] ?? 'COD');
            $stmt = $this->db->prepare("
                INSERT INTO orders
                    (customer_id, receiver_name, receiver_phone, shipping_address, note, payment_method, total_amount, status)
                VALUES
                    (?, ?, ?, ?, ?, ?, ?, 'pending')
            ");
            $stmt->execute([
                $customerId,
                $shipping['receiver_name'],
                $shipping['receiver_phone'],
                $shipping['shipping_address'],
                $shipping['note'] ?? null,
                $paymentMethod,
                $totalAmount,
            ]);
            $orderId = (int)$this->db->lastInsertId();

            $stmtDetail = $this->db->prepare("
                INSERT INTO order_details (order_id, product_id, quantity, sale_price)
                VALUES (?, ?, ?, ?)
            ");
            $stmtStock = $this->db->prepare("
                UPDATE products
                SET stock_quantity = stock_quantity - ?
                WHERE id = ? AND stock_quantity >= ?
            ");

            foreach ($cartItems as $item) {
                $stmtDetail->execute([
                    $orderId,
                    (int)$item['id'],
                    (int)$item['quantity'],
                    (float)$item['price'],
                ]);

                $qty = (int)$item['quantity'];
                $pid = (int)$item['id'];
                $stmtStock->execute([$qty, $pid, $qty]);
                if ($stmtStock->rowCount() === 0) {
                    $this->db->rollBack();
                    return false;
                }
            }

            $this->db->commit();
            return $orderId;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            error_log('OrderModel::createOrder error: ' . $e->getMessage());
            return false;
        }
    }
}


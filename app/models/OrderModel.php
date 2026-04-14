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

    /**
     * Admin: Lấy tổng doanh thu tháng này
     */
    public function getTotalRevenueThisMonth(): float {
        try {
            $stmt = $this->db->prepare("
                SELECT SUM(total_amount) AS revenue
                FROM {$this->table}
                WHERE status = 'completed'
                  AND MONTH(order_date) = MONTH(CURRENT_DATE())
                  AND YEAR(order_date) = YEAR(CURRENT_DATE())
            ");
            $stmt->execute();
            $result = $stmt->fetchColumn();
            return (float)($result ?? 0);
        } catch (PDOException $e) {
            error_log('OrderModel::getTotalRevenueThisMonth error: ' . $e->getMessage());
            return 0.0;
        }
    }

    /**
     * Admin: Đếm đơn hàng theo trạng thái
     */
    public function countByStatus(): array {
        try {
            $stmt = $this->db->prepare("
                SELECT status, COUNT(*) AS total
                FROM {$this->table}
                GROUP BY status
            ");
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
            return $result ?: [];
        } catch (PDOException $e) {
            error_log('OrderModel::countByStatus error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Admin: Lấy đơn hàng gần đây
     */
    public function getRecentOrders(int $limit = 5): array {
        try {
            $stmt = $this->db->prepare("
                SELECT
                    o.id,
                    o.total_amount,
                    o.status,
                    o.order_date,
                    c.full_name
                FROM {$this->table} o
                LEFT JOIN customers c ON o.customer_id = c.id
                ORDER BY o.order_date DESC
                LIMIT ?
            ");
            $stmt->execute([$limit]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('OrderModel::getRecentOrders error: ' . $e->getMessage());
            return [];
        }
    }
    /**
     * Admin: Lấy danh sách orders
     */
    public function getAdminList(array $filters = [], int $page = 1, int $limit = 10): array {
        try {
            $offset = ($page - 1) * $limit;
            $where = [];
            $params = [];

            if (!empty($filters['status']) && $filters['status'] !== 'all') {
                $where[] = "o.status = ?";
                $params[] = $filters['status'];
            }

            $whereStr = '';
            if (!empty($where)) {
                $whereStr = 'WHERE ' . implode(' AND ', $where);
            }

            $stmt = $this->db->prepare("
                SELECT
                    o.id,
                    o.total_amount,
                    o.status,
                    o.order_date,
                    c.full_name
                FROM {$this->table} o
                LEFT JOIN customers c ON o.customer_id = c.id
                {$whereStr}
                ORDER BY o.order_date DESC
                LIMIT ? OFFSET ?
            ");

            foreach ($params as $i => $param) {
                $stmt->bindValue($i + 1, $param);
            }
            $stmt->bindValue(count($params) + 1, $limit, PDO::PARAM_INT);
            $stmt->bindValue(count($params) + 2, $offset, PDO::PARAM_INT);

            $stmt->execute();
            $items = $stmt->fetchAll();

            $countStmt = $this->db->prepare("
                SELECT COUNT(*)
                FROM {$this->table} o
                {$whereStr}
            ");
            if (!empty($params)) {
                $countStmt->execute($params);
            } else {
                $countStmt->execute();
            }
            $total = (int)$countStmt->fetchColumn();

            return [
                'data' => $items,
                'total' => $total,
                'current_page' => $page,
                'last_page' => max(1, ceil($total / $limit))
            ];
        } catch (PDOException $e) {
            error_log('OrderModel::getAdminList error: ' . $e->getMessage());
            return ['data' => [], 'total' => 0, 'current_page' => 1, 'last_page' => 1];
        }
    }

    /**
     * Cập nhật trạng thái đơn hàng
     */
    /**
     * Cập nhật trạng thái đơn hàng
     */
    public function updateStatus(int $id, string $status): bool {
        try {
            $stmt = $this->db->prepare("UPDATE {$this->table} SET status = ? WHERE id = ?");
            return $stmt->execute([$status, $id]);
        } catch (PDOException $e) {
            error_log('OrderModel::updateStatus error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Hoàn lại tồn kho khi đơn hàng bị hủy
     */
    public function returnStock(int $orderId): bool {
        try {
            // Lấy chi tiết đơn hàng
            $stmtDetails = $this->db->prepare("SELECT product_id, quantity FROM order_details WHERE order_id = ?");
            $stmtDetails->execute([$orderId]);
            $items = $stmtDetails->fetchAll();

            if (empty($items)) return true;

            $this->db->beginTransaction();

            $stmtUpdate = $this->db->prepare("
                UPDATE products 
                SET stock_quantity = stock_quantity + ? 
                WHERE id = ?
            ");

            foreach ($items as $item) {
                $stmtUpdate->execute([
                    (int)$item['quantity'],
                    (int)$item['product_id']
                ]);
            }

            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            error_log('OrderModel::returnStock error: ' . $e->getMessage());
            return false;
        }
    }
}

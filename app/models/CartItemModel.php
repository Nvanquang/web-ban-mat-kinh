<?php
// app/models/CartItemModel.php

class CartItemModel extends Model {
    protected string $table = 'cart_items';

    public function getItemsWithProduct(int $customerId): array {
        try {
            $stmt = $this->db->prepare("
                SELECT
                    ci.product_id,
                    ci.quantity,
                    ci.sale_price,
                    p.product_name,
                    p.image_url,
                    p.stock_quantity,
                    p.status
                FROM {$this->table} ci
                JOIN products p ON p.id = ci.product_id
                WHERE ci.customer_id = ?
                ORDER BY ci.updated_at DESC, ci.created_at DESC
            ");
            $stmt->execute([$customerId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('CartItemModel::getItemsWithProduct error: ' . $e->getMessage());
            return [];
        }
    }

    public function getItemQuantity(int $customerId, int $productId): int {
        try {
            $stmt = $this->db->prepare("
                SELECT quantity
                FROM {$this->table}
                WHERE customer_id = ? AND product_id = ?
                LIMIT 1
            ");
            $stmt->execute([$customerId, $productId]);
            $qty = $stmt->fetchColumn();
            return (int)($qty ?? 0);
        } catch (PDOException $e) {
            error_log('CartItemModel::getItemQuantity error: ' . $e->getMessage());
            return 0;
        }
    }

    public function upsertAdd(int $customerId, int $productId, int $addQty, float $salePrice): bool {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO {$this->table} (customer_id, product_id, quantity, sale_price)
                VALUES (?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    quantity = quantity + VALUES(quantity)
            ");
            return $stmt->execute([$customerId, $productId, $addQty, $salePrice]);
        } catch (PDOException $e) {
            error_log('CartItemModel::upsertAdd error: ' . $e->getMessage());
            return false;
        }
    }

    public function updateQuantity(int $customerId, int $productId, int $quantity): bool {
        try {
            $stmt = $this->db->prepare("
                UPDATE {$this->table}
                SET quantity = ?
                WHERE customer_id = ? AND product_id = ?
            ");
            $stmt->execute([$quantity, $customerId, $productId]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log('CartItemModel::updateQuantity error: ' . $e->getMessage());
            return false;
        }
    }

    public function removeItem(int $customerId, int $productId): bool {
        try {
            $stmt = $this->db->prepare("
                DELETE FROM {$this->table}
                WHERE customer_id = ? AND product_id = ?
            ");
            return $stmt->execute([$customerId, $productId]);
        } catch (PDOException $e) {
            error_log('CartItemModel::removeItem error: ' . $e->getMessage());
            return false;
        }
    }

    public function clearCart(int $customerId): bool {
        try {
            $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE customer_id = ?");
            return $stmt->execute([$customerId]);
        } catch (PDOException $e) {
            error_log('CartItemModel::clearCart error: ' . $e->getMessage());
            return false;
        }
    }

    public function countQuantity(int $customerId): int {
        try {
            $stmt = $this->db->prepare("
                SELECT COALESCE(SUM(quantity), 0)
                FROM {$this->table}
                WHERE customer_id = ?
            ");
            $stmt->execute([$customerId]);
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log('CartItemModel::countQuantity error: ' . $e->getMessage());
            return 0;
        }
    }
}


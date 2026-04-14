<?php
// app/models/GlassesCategoryModel.php

class GlassesCategoryModel extends Model {
    protected string $table = 'glasses_categories';

    public function getAllVisible(): array {
        try {
            $stmt = $this->db->prepare("
                SELECT id, category_name, description
                FROM {$this->table}
                WHERE status = 1
                ORDER BY category_name ASC
            ");
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('GlassesCategoryModel::getAllVisible error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Admin: Lấy tất cả categories với số lượng sản phẩm
     */
    public function getAllWithCount(): array {
        try {
            $stmt = $this->db->prepare("
                SELECT
                    gc.id,
                    gc.category_name,
                    gc.description,
                    gc.status,
                    COUNT(p.id) AS product_count
                FROM {$this->table} gc
                LEFT JOIN products p ON gc.id = p.category_id AND p.status = 1
                GROUP BY gc.id, gc.category_name, gc.description, gc.status
                ORDER BY gc.category_name ASC
            ");
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('GlassesCategoryModel::getAllWithCount error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Admin: Tạo category mới
     */
    public function create(array $data): int|false {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO {$this->table} (category_name, description, status)
                VALUES (?, ?, 1)
            ");
            $result = $stmt->execute([
                trim($data['category_name']),
                trim($data['description'] ?? ''),
            ]);
            return $result ? (int)$this->db->lastInsertId() : false;
        } catch (PDOException $e) {
            error_log('GlassesCategoryModel::create error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Admin: Cập nhật category
     */
    public function update(int $id, array $data): bool {
        try {
            $stmt = $this->db->prepare("
                UPDATE {$this->table} SET
                    category_name = ?,
                    description = ?
                WHERE id = ?
            ");
            return $stmt->execute([
                trim($data['category_name']),
                trim($data['description'] ?? ''),
                $id,
            ]);
        } catch (PDOException $e) {
            error_log('GlassesCategoryModel::update error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Admin: Xóa category (chỉ khi không có sản phẩm)
     */
    public function delete(int $id): bool {
        try {
            $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log('GlassesCategoryModel::delete error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Admin: Kiểm tra category có sản phẩm không
     */
    public function hasProducts(int $id): bool {
        try {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) FROM products WHERE category_id = ? AND status = 1
            ");
            $stmt->execute([$id]);
            return (int)$stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log('GlassesCategoryModel::hasProducts error: ' . $e->getMessage());
            return true; // Assume has products if error
        }
    }

    /**
     * Admin: Kiểm tra tên category đã tồn tại chưa
     */
    public function nameExists(string $name, int $excludeId = 0): bool {
        try {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) FROM {$this->table}
                WHERE category_name = ? AND id != ?
            ");
            $stmt->execute([trim($name), $excludeId]);
            return (int)$stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log('GlassesCategoryModel::nameExists error: ' . $e->getMessage());
            return true; // Assume exists if error
        }
    }
}


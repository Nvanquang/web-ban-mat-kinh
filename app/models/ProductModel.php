<?php
// app/models/ProductModel.php

class ProductModel extends Model {
    protected string $table = 'products';

    /**
     * Lấy danh sách sản phẩm nổi bật cho trang chủ
     */
    public function getFeaturedProducts(int $limit = 4) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE status = 1 ORDER BY id DESC LIMIT :limit");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Lấy danh sách sản phẩm cho trang shop (có phân trang/lọc - sẽ mở rộng sau)
     */
    public function getShopProducts(int $limit = 12) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE status = 1 LIMIT :limit");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Tìm kiến sản phẩm theo tên
     */
    public function searchProducts(string $query) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE product_name LIKE :query AND status = 1");
        $stmt->execute(['query' => "%$query%"]);
        return $stmt->fetchAll();
    }

    /**
     * Lấy sản phẩm theo danh mục
     */
    public function getByCategory(int $categoryId) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE category_id = :category_id AND status = 1");
        $stmt->execute(['category_id' => $categoryId]);
        return $stmt->fetchAll();
    }
}

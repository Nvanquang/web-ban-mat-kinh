<?php
// app/models/ProductModel.php

class ProductModel extends Model {
    protected string $table = 'products';

    /**
     * Tìm sản phẩm theo ID (dùng cho Cart - read-only)
     * Trả về false nếu không tồn tại hoặc status=0
     */
    public function findById(int $id): array|false {
        try {
            $stmt = $this->db->prepare("
                SELECT id, product_name, price, image_url, stock_quantity, status, category_id, old_price, description
                FROM {$this->table}
                WHERE id = ? AND status = 1
                LIMIT 1
            ");
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log('ProductModel::findById error: ' . $e->getMessage());
            return false;
        }
    }

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

    /**
     * Lấy danh sách sản phẩm có filter + sort + pagination
     */
    public function getPaginated(int $page, int $perPage, array $filters): array {
        try {
            $page = max(1, $page);
            $perPage = max(1, $perPage);
            $offset = ($page - 1) * $perPage;

            $where = ['p.status = 1'];
            $params = [];

            $categoryId = (int)($filters['category_id'] ?? 0);
            if ($categoryId > 0) {
                $where[] = 'p.category_id = ?';
                $params[] = $categoryId;
            }

            $minPrice = (float)($filters['min_price'] ?? 0);
            if ($minPrice > 0) {
                $where[] = 'p.price >= ?';
                $params[] = $minPrice;
            }

            $maxPrice = (float)($filters['max_price'] ?? 0);
            if ($maxPrice > 0) {
                $where[] = 'p.price <= ?';
                $params[] = $maxPrice;
            }

            $keyword = trim((string)($filters['keyword'] ?? ''));
            if ($keyword !== '') {
                $where[] = '(p.product_name LIKE ? OR p.description LIKE ?)';
                $kw = '%' . $keyword . '%';
                $params[] = $kw;
                $params[] = $kw;
            }

            $orderMap = [
                'newest'     => 'p.created_at DESC',
                'price_asc'  => 'p.price ASC',
                'price_desc' => 'p.price DESC',
                'popular'    => 'p.view_count DESC',
            ];
            $sortKey = (string)($filters['sort'] ?? 'newest');
            $orderBy = $orderMap[$sortKey] ?? $orderMap['newest'];

            $whereSql = implode(' AND ', $where);

            // Count total
            $countStmt = $this->db->prepare("
                SELECT COUNT(*)
                FROM {$this->table} p
                LEFT JOIN glasses_categories gc ON p.category_id = gc.id
                WHERE {$whereSql}
            ");
            $countStmt->execute($params);
            $total = (int)$countStmt->fetchColumn();

            // Items
            $stmt = $this->db->prepare("
                SELECT
                    p.id, p.category_id, p.product_name, p.price, p.old_price,
                    p.stock_quantity, p.description, p.image_url, p.view_count, p.created_at,
                    gc.category_name
                FROM {$this->table} p
                LEFT JOIN glasses_categories gc ON p.category_id = gc.id
                WHERE {$whereSql}
                ORDER BY {$orderBy}
                LIMIT ? OFFSET ?
            ");

            $dataParams = array_merge($params, [$perPage, $offset]);
            $stmt->execute($dataParams);

            $totalPages = (int)ceil($total / $perPage);

            return [
                'items'        => $stmt->fetchAll(),
                'total'        => $total,
                'per_page'     => $perPage,
                'current_page' => $page,
                'total_pages'  => max(1, $totalPages),
                'has_prev'     => $page > 1,
                'has_next'     => $page < $totalPages,
            ];
        } catch (PDOException $e) {
            error_log('ProductModel::getPaginated error: ' . $e->getMessage());
            return [
                'items'        => [],
                'total'        => 0,
                'per_page'     => $perPage,
                'current_page' => $page,
                'total_pages'  => 1,
                'has_prev'     => false,
                'has_next'     => false,
                'error'        => true,
            ];
        }
    }

    public function getProductWithCategory(int $id): array|false {
        try {
            $stmt = $this->db->prepare("
                SELECT
                    p.*,
                    gc.category_name
                FROM {$this->table} p
                LEFT JOIN glasses_categories gc ON p.category_id = gc.id
                WHERE p.id = ? AND p.status = 1
                LIMIT 1
            ");
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log('ProductModel::getProductWithCategory error: ' . $e->getMessage());
            return false;
        }
    }

    public function incrementViewCount(int $id): void {
        try {
            $stmt = $this->db->prepare("UPDATE {$this->table} SET view_count = view_count + 1 WHERE id = ?");
            $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log('ProductModel::incrementViewCount error: ' . $e->getMessage());
        }
    }

    public function getRelated(int $id, int $categoryId, int $limit = 4): array {
        try {
            $stmt = $this->db->prepare("
                SELECT id, product_name, price, old_price, image_url
                FROM {$this->table}
                WHERE status = 1 AND category_id = ? AND id != ?
                ORDER BY created_at DESC
                LIMIT ?
            ");
            $stmt->bindValue(1, $categoryId, PDO::PARAM_INT);
            $stmt->bindValue(2, $id, PDO::PARAM_INT);
            $stmt->bindValue(3, $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('ProductModel::getRelated error: ' . $e->getMessage());
            return [];
        }
    }
}

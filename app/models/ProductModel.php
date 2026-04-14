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
                SELECT id, product_name, price, image_url, stock_quantity, status, category_id, old_price, description, gender, created_at
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
     * Tìm sản phẩm theo ID (dùng cho Admin - có thể xem tất cả sản phẩm)
     * Trả về false nếu không tồn tại
     */
    public function findByIdAdmin(int $id): array|false {
        try {
            $stmt = $this->db->prepare("
                SELECT id, product_name, price, image_url, stock_quantity, status, category_id, old_price, description, gender, created_at
                FROM {$this->table}
                WHERE id = ?
                LIMIT 1
            ");
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log('ProductModel::findByIdAdmin error: ' . $e->getMessage());
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
            
            $gender = $filters['gender'] ?? '';
            if (in_array($gender, ['male', 'female', 'all'])) {
                $where[] = 'p.gender = ?';
                $params[] = $gender;
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
                    p.stock_quantity, p.description, p.image_url, p.view_count, p.gender, p.created_at,
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

    /**
     * Admin: Lấy danh sách sản phẩm với filter và pagination
     */
    public function getAdminList(array $filters = [], int $page = 1, int $perPage = 10): array {
        try {
            $page = max(1, $page);
            $perPage = max(1, $perPage);
            $offset = ($page - 1) * $perPage;

            $where = [];
            $params = [];

            // Search keyword
            $keyword = trim($filters['keyword'] ?? '');
            if ($keyword !== '') {
                $where[] = '(p.product_name LIKE ? OR p.description LIKE ?)';
                $kw = '%' . $keyword . '%';
                $params[] = $kw;
                $params[] = $kw;
            }

            // Category filter
            $categoryId = (int)($filters['category_id'] ?? 0);
            if ($categoryId > 0) {
                $where[] = 'p.category_id = ?';
                $params[] = $categoryId;
            }

            // Status filter
            $status = $filters['status'] ?? '';
            if ($status !== '') {
                $where[] = 'p.status = ?';
                $params[] = (int)$status;
            }

            // Gender filter
            $gender = $filters['gender'] ?? '';
            if (in_array($gender, ['male', 'female', 'all'])) {
                $where[] = 'p.gender = ?';
                $params[] = $gender;
            }

            $whereSql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

            // Count total
            $countStmt = $this->db->prepare("
                SELECT COUNT(*)
                FROM {$this->table} p
                LEFT JOIN glasses_categories gc ON p.category_id = gc.id
                {$whereSql}
            ");
            $countStmt->execute($params);
            $total = (int)$countStmt->fetchColumn();

            // Get items
            $stmt = $this->db->prepare("
                SELECT
                    p.id, p.product_name, p.price, p.old_price, p.stock_quantity,
                    p.image_url, p.status, p.created_at, p.view_count, p.gender,
                    gc.category_name
                FROM {$this->table} p
                LEFT JOIN glasses_categories gc ON p.category_id = gc.id
                {$whereSql}
                ORDER BY p.created_at DESC
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
            error_log('ProductModel::getAdminList error: ' . $e->getMessage());
            return [
                'items'        => [],
                'total'        => 0,
                'per_page'     => $perPage,
                'current_page' => $page,
                'total_pages'  => 1,
                'has_prev'     => false,
                'has_next'     => false,
            ];
        }
    }

    /**
     * Admin: Tạo sản phẩm mới
     */
    public function create(array $data): int|false {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO {$this->table}
                    (category_id, product_name, price, old_price, stock_quantity,
                     description, image_url, gender, status)
                VALUES
                    (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $result = $stmt->execute([
                (int)$data['category_id'],
                trim($data['product_name']),
                (float)$data['price'],
                $data['old_price'] ? (float)$data['old_price'] : null,
                (int)$data['stock_quantity'],
                trim($data['description'] ?? ''),
                $data['image_url'] ?? null,
                $data['gender'] ?? 'all',
                (int)($data['status'] ?? 1),
            ]);
            return $result ? (int)$this->db->lastInsertId() : false;
        } catch (PDOException $e) {
            error_log('ProductModel::create error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Admin: Cập nhật sản phẩm
     */
    public function update(int $id, array $data): bool {
        try {
            $stmt = $this->db->prepare("
                UPDATE {$this->table} SET
                    category_id = ?,
                    product_name = ?,
                    price = ?,
                    old_price = ?,
                    stock_quantity = ?,
                    description = ?,
                    image_url = ?,
                    gender = ?,
                    status = ?
                WHERE id = ?
            ");
            return $stmt->execute([
                (int)$data['category_id'],
                trim($data['product_name']),
                (float)$data['price'],
                $data['old_price'] ? (float)$data['old_price'] : null,
                (int)$data['stock_quantity'],
                trim($data['description'] ?? ''),
                $data['image_url'] ?? null,
                $data['gender'] ?? 'all',
                (int)($data['status'] ?? 1),
                $id,
            ]);
        } catch (PDOException $e) {
            error_log('ProductModel::update error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Admin: Soft delete sản phẩm (set status = 0)
     */
    public function softDelete(int $id): bool {
        try {
            $stmt = $this->db->prepare("UPDATE {$this->table} SET status = 0 WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log('ProductModel::softDelete error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Admin: Kiểm tra sản phẩm có trong đơn hàng nào chưa
     */
    public function isUsedInOrders(int $id): bool {
        try {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) FROM order_details WHERE product_id = ?
            ");
            $stmt->execute([$id]);
            return (int)$stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log('ProductModel::isUsedInOrders error: ' . $e->getMessage());
            return true; // Assume it's used if error
        }
    }

    /**
     * Admin: Đếm số sản phẩm đang bán
     */
    public function countSelling(): int {
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM {$this->table} WHERE status = 1");
            $stmt->execute();
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log('ProductModel::countSelling error: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Admin: Lấy top sản phẩm bán chạy
     */
    public function getTopSellingProducts(int $limit = 5): array {
        try {
            $stmt = $this->db->prepare("
                SELECT
                    p.id,
                    p.product_name,
                    p.image_url,
                    SUM(od.quantity) AS total_sold
                FROM {$this->table} p
                JOIN order_details od ON p.id = od.product_id
                JOIN orders o ON od.order_id = o.id
                WHERE o.status IN ('completed', 'shipped') AND p.status = 1
                GROUP BY p.id, p.product_name, p.image_url
                ORDER BY total_sold DESC
                LIMIT ?
            ");
            $stmt->execute([$limit]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('ProductModel::getTopSellingProducts error: ' . $e->getMessage());
            return [];
        }
    }
}

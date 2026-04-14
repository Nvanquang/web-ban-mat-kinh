<?php
// app/models/CustomerModel.php

class CustomerModel extends Model {
    protected string $table = 'customers';

    /**
     * Tìm người dùng theo username để đăng nhập
     */
    public function findByUsername(string $username): array|false {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE username = :username LIMIT 1");
            $stmt->execute([':username' => $username]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log('CustomerModel::findByUsername error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Tìm người dùng theo email
     */
    public function findByEmail(string $email): array|false {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE email = :email LIMIT 1");
            $stmt->execute([':email' => $email]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log('CustomerModel::findByEmail error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Đăng ký người dùng mới
     */
    public function register(array $data): int|false {
        try {
            $sql = "INSERT INTO {$this->table} (username, password, email, full_name, phone, role, status)
                    VALUES (:username, :password, :email, :full_name, :phone, 'customer', 'active')";

            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                ':username'  => $data['username'],
                ':password'  => password_hash($data['password'], PASSWORD_BCRYPT),
                ':email'     => $data['email'],
                ':full_name' => $data['full_name'] ?? null,
                ':phone'     => $data['phone'] ?? null,
            ]);

            return $result ? (int)$this->db->lastInsertId() : false;
        } catch (PDOException $e) {
            error_log('CustomerModel::register error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Admin: Đếm số khách hàng active
     */
    public function countActive(): int {
        try {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) FROM {$this->table}
                WHERE role = 'customer' AND status = 'active'
            ");
            $stmt->execute();
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log('CustomerModel::countActive error: ' . $e->getMessage());
            return 0;
        }
    }
    
    public function findById(int $id): array|false {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = ? LIMIT 1");
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log('CustomerModel::findById error: ' . $e->getMessage());
            return false;
        }
    }

    public function getAdminList(string $keyword = '', int $page = 1, int $limit = 10): array {
        try {
            $offset = ($page - 1) * $limit;
            $whereStr = '';
            $params = [];

            if (!empty($keyword)) {
                $whereStr = "WHERE full_name LIKE ? OR email LIKE ?";
                $params[] = "%$keyword%";
                $params[] = "%$keyword%";
            }

            $stmt = $this->db->prepare("
                SELECT id, username, full_name, email, phone, role, status, created_at
                FROM {$this->table}
                {$whereStr}
                ORDER BY created_at DESC
                LIMIT ? OFFSET ?
            ");

            foreach ($params as $i => $param) {
                $stmt->bindValue($i + 1, $param);
            }
            $stmt->bindValue(count($params) + 1, $limit, PDO::PARAM_INT);
            $stmt->bindValue(count($params) + 2, $offset, PDO::PARAM_INT);

            $stmt->execute();
            $items = $stmt->fetchAll();

            $countStmt = $this->db->prepare("SELECT COUNT(*) FROM {$this->table} {$whereStr}");
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
            error_log('CustomerModel::getAdminList error: ' . $e->getMessage());
            return ['data' => [], 'total' => 0, 'current_page' => 1, 'last_page' => 1];
        }
    }

    public function updateCustomerStatus(int $id, string $status): bool {
        try {
            $stmt = $this->db->prepare("UPDATE {$this->table} SET status = ? WHERE id = ?");
            return $stmt->execute([$status, $id]);
        } catch (PDOException $e) {
            error_log('CustomerModel::updateCustomerStatus error: ' . $e->getMessage());
            return false;
        }
    }
}

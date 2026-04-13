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
}

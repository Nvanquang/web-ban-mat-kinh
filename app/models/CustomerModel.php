<?php
// app/models/CustomerModel.php

class CustomerModel extends Model {
    protected string $table = 'customers';

    /**
     * Tìm người dùng theo username để đăng nhập
     */
    public function getByUsername(string $username) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE username = :username LIMIT 1");
        $stmt->execute(['username' => $username]);
        return $stmt->fetch();
    }

    /**
     * Tìm người dùng theo email
     */
    public function getByEmail(string $email) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        return $stmt->fetch();
    }

    /**
     * Đăng ký người dùng mới
     */
    public function register(array $data) {
        $sql = "INSERT INTO {$this->table} (username, email, password, full_name, role, status) 
                VALUES (:username, :email, :password, :full_name, :role, :status)";
        
        $params = [
            'username'  => $data['username'],
            'email'     => $data['email'],
            'password'  => $data['password'], // Password should already be hashed
            'full_name' => $data['full_name'] ?? '',
            'role'      => $data['role'] ?? 'customer',
            'status'    => $data['status'] ?? 'active'
        ];

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }
}

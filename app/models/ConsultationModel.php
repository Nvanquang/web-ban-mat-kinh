<?php
// app/models/ConsultationModel.php

class ConsultationModel extends Model {
    protected string $table = 'consultations';

    public function getByCustomer(int $customerId): array {
        try {
            $stmt = $this->db->prepare("
                SELECT id, content, reply, status, sent_at
                FROM {$this->table}
                WHERE customer_id = ?
                ORDER BY sent_at DESC
            ");
            $stmt->execute([$customerId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('ConsultationModel::getByCustomer error: ' . $e->getMessage());
            return [];
        }
    }

    public function create(array $data): int|false {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO {$this->table} (customer_id, content)
                VALUES (?, ?)
            ");
            $ok = $stmt->execute([
                (int)$data['customer_id'],
                (string)$data['content'],
            ]);
            return $ok ? (int)$this->db->lastInsertId() : false;
        } catch (PDOException $e) {
            error_log('ConsultationModel::create error: ' . $e->getMessage());
            return false;
        }
    }
}


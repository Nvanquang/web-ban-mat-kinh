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

    /**
     * Admin: Lấy danh sách tư vấn đang pending
     */
    public function getPending(int $limit = 3): array {
        try {
            $stmt = $this->db->prepare("
                SELECT
                    cn.id,
                    cn.content,
                    cn.sent_at,
                    c.full_name
                FROM {$this->table} cn
                LEFT JOIN customers c ON cn.customer_id = c.id
                WHERE cn.status = 'pending'
                ORDER BY cn.sent_at DESC
                LIMIT ?
            ");
            $stmt->execute([$limit]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('ConsultationModel::getPending error: ' . $e->getMessage());
            return [];
        }
    }
    public function findById(int $id): array|false {
        try {
            $stmt = $this->db->prepare("
                SELECT cn.*, c.full_name as customer_name, c.email as customer_email
                FROM {$this->table} cn
                LEFT JOIN customers c ON cn.customer_id = c.id
                WHERE cn.id = ? LIMIT 1
            ");
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log('ConsultationModel::findById error: ' . $e->getMessage());
            return false;
        }
    }

    public function getAdminList(string $status = 'all', int $page = 1, int $limit = 10): array {
        try {
            $offset = ($page - 1) * $limit;
            $where = [];
            $params = [];

            if ($status !== 'all') {
                $where[] = "cn.status = ?";
                $params[] = $status;
            }

            $whereStr = '';
            if (!empty($where)) {
                $whereStr = 'WHERE ' . implode(' AND ', $where);
            }

            $stmt = $this->db->prepare("
                SELECT
                    cn.id,
                    cn.content,
                    cn.status,
                    cn.sent_at,
                    c.full_name as customer_name
                FROM {$this->table} cn
                LEFT JOIN customers c ON cn.customer_id = c.id
                {$whereStr}
                ORDER BY cn.sent_at DESC
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
                FROM {$this->table} cn
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
            error_log('ConsultationModel::getAdminList error: ' . $e->getMessage());
            return ['data' => [], 'total' => 0, 'current_page' => 1, 'last_page' => 1];
        }
    }

    public function reply(int $id, string $reply): bool {
        try {
            $stmt = $this->db->prepare("
                UPDATE {$this->table}
                SET reply = ?, status = 'resolved'
                WHERE id = ?
            ");
            return $stmt->execute([$reply, $id]);
        } catch (PDOException $e) {
            error_log('ConsultationModel::reply error: ' . $e->getMessage());
            return false;
        }
    }
}


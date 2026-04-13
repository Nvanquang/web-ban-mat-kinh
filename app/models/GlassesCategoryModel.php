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
}


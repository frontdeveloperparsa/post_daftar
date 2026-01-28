<?php
/**
 * کلاس مدیریت انواع بسته
 */

require_once __DIR__ . '/Database.php';

class PackageType {
    
    /**
     * دریافت همه انواع
     */
    public static function getAll(): array {
        $db = Database::getInstance();
        $stmt = $db->query("SELECT * FROM package_types ORDER BY name");
        return $stmt->fetchAll();
    }
    
    /**
     * دریافت یک نوع
     */
    public static function find(int $id): ?array {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM package_types WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }
    
    /**
     * ایجاد نوع جدید
     */
    public static function create(string $name): array {
        $db = Database::getInstance();
        
        try {
            $stmt = $db->prepare("INSERT INTO package_types (name) VALUES (?)");
            $stmt->execute([$name]);
            return ['success' => true, 'id' => $db->lastInsertId()];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'خطا در ایجاد نوع بسته'];
        }
    }
    
    /**
     * حذف نوع
     */
    public static function delete(int $id): array {
        $db = Database::getInstance();
        
        // بررسی استفاده نشدن
        $stmt = $db->prepare("SELECT COUNT(*) FROM packages WHERE package_type_id = ?");
        $stmt->execute([$id]);
        if ($stmt->fetchColumn() > 0) {
            return ['success' => false, 'message' => 'این نوع بسته در حال استفاده است و قابل حذف نیست'];
        }
        
        $stmt = $db->prepare("DELETE FROM package_types WHERE id = ?");
        $stmt->execute([$id]);
        
        return ['success' => true];
    }
}

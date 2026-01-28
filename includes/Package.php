<?php
/**
 * کلاس مدیریت بسته‌ها
 */

require_once __DIR__ . '/Database.php';

class Package {
    
    /**
     * ثبت بسته جدید
     */
    public static function create(array $data): array {
        $db = Database::getInstance();
        
        try {
            $stmt = $db->prepare("
                INSERT INTO packages (tracking_code, receiver_name, receiver_phone, package_type_id, description, image_path, registered_by)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $data['tracking_code'],
                $data['receiver_name'],
                $data['receiver_phone'],
                $data['package_type_id'],
                $data['description'] ?? null,
                $data['image_path'] ?? null,
                $data['registered_by']
            ]);
            
            return ['success' => true, 'id' => $db->lastInsertId()];
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                return ['success' => false, 'message' => 'این کد رهگیری قبلا ثبت شده است'];
            }
            return ['success' => false, 'message' => 'خطا در ثبت بسته'];
        }
    }
    
    /**
     * جستجوی بسته با کد رهگیری
     */
    public static function findByTrackingCode(string $code): ?array {
        $db = Database::getInstance();
        
        $stmt = $db->prepare("
            SELECT p.*, pt.name as type_name, 
                   u1.full_name as registered_by_name,
                   u2.full_name as delivered_by_name
            FROM packages p
            LEFT JOIN package_types pt ON p.package_type_id = pt.id
            LEFT JOIN users u1 ON p.registered_by = u1.id
            LEFT JOIN users u2 ON p.delivered_by = u2.id
            WHERE p.tracking_code = ?
        ");
        $stmt->execute([$code]);
        
        return $stmt->fetch() ?: null;
    }
    
    /**
     * تحویل بسته
     */
    public static function deliver(int $packageId, int $deliveredBy, array $deliveryData = []): bool {
        $db = Database::getInstance();
        
        $sql = "UPDATE packages SET status = 'delivered', delivered_by = ?, delivered_at = NOW()";
        $params = [$deliveredBy];
        
        if (!empty($deliveryData['method'])) {
            $sql .= ", delivery_method = ?";
            $params[] = $deliveryData['method'];
        }
        
        if (!empty($deliveryData['signature'])) {
            $sql .= ", receiver_signature = ?";
            $params[] = $deliveryData['signature'];
        }
        
        if (!empty($deliveryData['photo'])) {
            $sql .= ", delivery_photo = ?";
            $params[] = $deliveryData['photo'];
        }
        
        if (!empty($deliveryData['receiver_name'])) {
            $sql .= ", delivered_to_name = ?";
            $params[] = $deliveryData['receiver_name'];
        }
        
        if (!empty($deliveryData['receiver_phone'])) {
            $sql .= ", delivered_to_phone = ?";
            $params[] = $deliveryData['receiver_phone'];
        }
        
        if (!empty($deliveryData['receiver_national_code'])) {
            $sql .= ", receiver_national_code = ?";
            $params[] = $deliveryData['receiver_national_code'];
        }
        
        $sql .= " WHERE id = ? AND status = 'pending'";
        $params[] = $packageId;
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->rowCount() > 0;
    }
    
    /**
     * تحویل چند بسته همزمان
     */
    public static function deliverMultiple(array $packageIds, int $deliveredBy, array $deliveryData = []): int {
        $count = 0;
        foreach ($packageIds as $id) {
            if (self::deliver((int)$id, $deliveredBy, $deliveryData)) {
                $count++;
            }
        }
        return $count;
    }
    
    /**
     * جستجوی پیشرفته برای تحویل
     */
    public static function searchForDelivery(string $query, ?int $typeId = null, string $status = 'pending'): array {
        $db = Database::getInstance();
        
        $where = ["(p.tracking_code LIKE ? OR p.receiver_name LIKE ? OR p.receiver_phone LIKE ?)"];
        $params = ['%' . $query . '%', '%' . $query . '%', '%' . $query . '%'];
        
        if ($status !== 'all') {
            $where[] = "p.status = ?";
            $params[] = $status;
        }
        
        if ($typeId) {
            $where[] = "p.package_type_id = ?";
            $params[] = $typeId;
        }
        
        $sql = "
            SELECT p.*, pt.name as type_name
            FROM packages p
            LEFT JOIN package_types pt ON p.package_type_id = pt.id
            WHERE " . implode(' AND ', $where) . "
            ORDER BY p.registered_at DESC
            LIMIT 100
        ";
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }
    
    /**
     * لیست بسته‌های در انتظار
     */
    public static function getPending(int $limit = 50, int $offset = 0): array {
        $db = Database::getInstance();
        
        $stmt = $db->prepare("
            SELECT p.*, pt.name as type_name
            FROM packages p
            LEFT JOIN package_types pt ON p.package_type_id = pt.id
            WHERE p.status = 'pending'
            ORDER BY p.registered_at DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$limit, $offset]);
        
        return $stmt->fetchAll();
    }
    
    /**
     * لیست بسته‌های تحویل شده
     */
    public static function getDelivered(int $limit = 50, int $offset = 0): array {
        $db = Database::getInstance();
        
        $stmt = $db->prepare("
            SELECT p.*, pt.name as type_name, u.full_name as delivered_by_name
            FROM packages p
            LEFT JOIN package_types pt ON p.package_type_id = pt.id
            LEFT JOIN users u ON p.delivered_by = u.id
            WHERE p.status = 'delivered'
            ORDER BY p.delivered_at DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$limit, $offset]);
        
        return $stmt->fetchAll();
    }
    
    /**
     * جستجوی پیشرفته
     */
    public static function search(array $filters): array {
        $db = Database::getInstance();
        
        $where = ["1=1"];
        $params = [];
        
        if (!empty($filters['tracking_code'])) {
            $where[] = "p.tracking_code LIKE ?";
            $params[] = '%' . $filters['tracking_code'] . '%';
        }
        
        if (!empty($filters['receiver_name'])) {
            $where[] = "p.receiver_name LIKE ?";
            $params[] = '%' . $filters['receiver_name'] . '%';
        }
        
        if (!empty($filters['receiver_phone'])) {
            $where[] = "p.receiver_phone LIKE ?";
            $params[] = '%' . $filters['receiver_phone'] . '%';
        }
        
        if (!empty($filters['status'])) {
            $where[] = "p.status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['type_id'])) {
            $where[] = "p.package_type_id = ?";
            $params[] = $filters['type_id'];
        }
        
        if (!empty($filters['date_from'])) {
            $where[] = "DATE(p.registered_at) >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $where[] = "DATE(p.registered_at) <= ?";
            $params[] = $filters['date_to'];
        }
        
        $sql = "
            SELECT p.*, pt.name as type_name, 
                   u1.full_name as registered_by_name,
                   u2.full_name as delivered_by_name
            FROM packages p
            LEFT JOIN package_types pt ON p.package_type_id = pt.id
            LEFT JOIN users u1 ON p.registered_by = u1.id
            LEFT JOIN users u2 ON p.delivered_by = u2.id
            WHERE " . implode(' AND ', $where) . "
            ORDER BY p.registered_at DESC
            LIMIT 500
        ";
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }
    
    /**
     * جستجوی عمومی برای مشتری‌ها (با کد رهگیری، شماره یا اسم)
     */
    public static function publicSearch(string $query): array {
        $db = Database::getInstance();
        
        $stmt = $db->prepare("
            SELECT p.tracking_code, p.receiver_name, p.status, p.registered_at, p.delivered_at, pt.name as type_name
            FROM packages p
            LEFT JOIN package_types pt ON p.package_type_id = pt.id
            WHERE p.tracking_code = ? OR p.receiver_phone = ? OR p.receiver_name LIKE ?
            ORDER BY p.registered_at DESC
            LIMIT 20
        ");
        $stmt->execute([$query, $query, '%' . $query . '%']);
        
        return $stmt->fetchAll();
    }
    
    /**
     * ثبت چند بسته همزمان
     */
    public static function createBulk(array $packages, int $registeredBy): array {
        $success = 0;
        $failed = 0;
        $errors = [];
        
        foreach ($packages as $index => $pkg) {
            $result = self::create([
                'tracking_code' => $pkg['tracking_code'],
                'receiver_name' => $pkg['receiver_name'],
                'receiver_phone' => $pkg['receiver_phone'],
                'package_type_id' => $pkg['package_type_id'],
                'description' => $pkg['description'] ?? null,
                'image_path' => null,
                'registered_by' => $registeredBy
            ]);
            
            if ($result['success']) {
                $success++;
            } else {
                $failed++;
                $errors[] = "ردیف " . ($index + 1) . ": " . $result['message'];
            }
        }
        
        return [
            'success' => $success,
            'failed' => $failed,
            'errors' => $errors
        ];
    }
    
    /**
     * جستجوی بسته‌های تحویل شده
     */
    public static function searchDelivered(string $query, ?int $typeId = null): array {
        $db = Database::getInstance();
        
        $where = ["p.status = 'delivered'", "(p.tracking_code LIKE ? OR p.receiver_name LIKE ? OR p.receiver_phone LIKE ? OR p.delivered_to_name LIKE ? OR p.delivered_to_phone LIKE ?)"];
        $params = ['%' . $query . '%', '%' . $query . '%', '%' . $query . '%', '%' . $query . '%', '%' . $query . '%'];
        
        if ($typeId) {
            $where[] = "p.package_type_id = ?";
            $params[] = $typeId;
        }
        
        $sql = "
            SELECT p.*, pt.name as type_name, u.full_name as delivered_by_name
            FROM packages p
            LEFT JOIN package_types pt ON p.package_type_id = pt.id
            LEFT JOIN users u ON p.delivered_by = u.id
            WHERE " . implode(' AND ', $where) . "
            ORDER BY p.delivered_at DESC
            LIMIT 100
        ";
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }
    
    /**
     * دریافت جزئیات بسته با ID
     */
    public static function getById(int $id): ?array {
        $db = Database::getInstance();
        
        $stmt = $db->prepare("
            SELECT p.*, pt.name as type_name, 
                   u1.full_name as registered_by_name,
                   u2.full_name as delivered_by_name
            FROM packages p
            LEFT JOIN package_types pt ON p.package_type_id = pt.id
            LEFT JOIN users u1 ON p.registered_by = u1.id
            LEFT JOIN users u2 ON p.delivered_by = u2.id
            WHERE p.id = ?
        ");
        $stmt->execute([$id]);
        
        return $stmt->fetch() ?: null;
    }
    
    /**
     * آمار کلی
     */
    public static function getStats(): array {
        $db = Database::getInstance();
        
        $stmt = $db->query("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) as delivered,
                SUM(CASE WHEN DATE(registered_at) = CURDATE() THEN 1 ELSE 0 END) as today_registered,
                SUM(CASE WHEN DATE(delivered_at) = CURDATE() THEN 1 ELSE 0 END) as today_delivered
            FROM packages
        ");
        
        return $stmt->fetch();
    }
    
    /**
     * آمار ۳۰ روز اخیر
     */
    public static function getLast30DaysStats(): array {
        $db = Database::getInstance();
        
        $stmt = $db->query("
            SELECT 
                DATE(registered_at) as date,
                COUNT(*) as registered,
                SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) as delivered
            FROM packages
            WHERE registered_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY DATE(registered_at)
            ORDER BY date ASC
        ");
        
        return $stmt->fetchAll();
    }
}

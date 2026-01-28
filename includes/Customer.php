<?php
require_once __DIR__ . '/Database.php';

class Customer {
    
    /**
     * ثبت‌نام مشتری جدید
     */
    public static function register(string $firstName, string $lastName, string $nationalCode, string $phone, string $password): array {
        $db = Database::getInstance();
        
        // چک کردن تکراری نبودن
        $stmt = $db->prepare("SELECT id FROM customers WHERE national_code = ? OR phone = ?");
        $stmt->execute([$nationalCode, $phone]);
        if ($stmt->fetch()) {
            return ['success' => false, 'message' => 'این کد ملی یا شماره موبایل قبلا ثبت شده'];
        }
        
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $db->prepare("INSERT INTO customers (first_name, last_name, national_code, phone, password) VALUES (?, ?, ?, ?, ?)");
        
        try {
            $stmt->execute([$firstName, $lastName, $nationalCode, $phone, $hashedPassword]);
            return ['success' => true, 'message' => 'ثبت‌نام با موفقیت انجام شد'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'خطا در ثبت‌نام'];
        }
    }
    
    /**
     * ورود مشتری
     */
    public static function login(string $phone, string $password): array {
        $db = Database::getInstance();
        
        $stmt = $db->prepare("SELECT * FROM customers WHERE phone = ?");
        $stmt->execute([$phone]);
        $customer = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($customer && password_verify($password, $customer['password'])) {
            return ['success' => true, 'customer' => $customer];
        }
        
        return ['success' => false, 'message' => 'شماره موبایل یا رمز عبور اشتباه است'];
    }
    
    /**
     * پیدا کردن بسته‌ها با شماره موبایل
     */
    public static function getPackages(string $phone): array {
        $db = Database::getInstance();
        
        $stmt = $db->prepare("
            SELECT p.*, pt.name as type_name 
            FROM packages p
            LEFT JOIN package_types pt ON p.package_type_id = pt.id
            WHERE p.receiver_phone = ?
            ORDER BY p.registered_at DESC
        ");
        $stmt->execute([$phone]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * گرفتن اطلاعات مشتری با ID
     */
    public static function getById(int $id): ?array {
        $db = Database::getInstance();
        
        $stmt = $db->prepare("SELECT * FROM customers WHERE id = ?");
        $stmt->execute([$id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }
}

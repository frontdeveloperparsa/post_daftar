<?php
/**
 * کلاس اتصال به دیتابیس
 * از الگوی Singleton استفاده می‌کنه
 */

require_once __DIR__ . '/config.php';

class Database {
    private static ?PDO $instance = null;
    
    public static function getInstance(): PDO {
        if (self::$instance === null) {
            try {
                $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ];
                self::$instance = new PDO($dsn, DB_USER, DB_PASS, $options);
            } catch (PDOException $e) {
                if (DEBUG_MODE) {
                    die("خطای اتصال به دیتابیس: " . $e->getMessage());
                }
                die("خطا در اتصال به دیتابیس. لطفا بعدا تلاش کنید.");
            }
        }
        return self::$instance;
    }
    
    // جلوگیری از clone کردن
    private function __clone() {}
}

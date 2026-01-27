<?php
// شروع جلسه (برای لاگین و سش‌ها)

define('BASE_URL', '/post_daftar/');
session_start();

define('DB_HOST', 'localhost');          // معمولاً localhost هست، اگر نه هاست میگه
define('DB_USER', 'smsanboi_parsa'); //  که ساختی
define('DB_PASS', '82838659akpa');
define('DB_NAME', 'smsanboi_post_khaneh');       //  parsadb_post بده

// اتصال به دیتابیس با PDO (امن و بهتر از mysqli)
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("SET NAMES 'utf8mb4'");
} catch (PDOException $e) {
    // اگر اتصال نشد، خطا رو نشون بده (در محیط واقعی می‌تونی log کنی)
    die("خطا در اتصال به دیتابیس: " . htmlspecialchars($e->getMessage()));
}

// آدرس پایه سایت (برای لینک‌های داخلی)
define('BASE_URL', '/post_daftar/');   // اگر پوشه پروژه‌اتسم دیگه‌ای داره، اینجا تغییر بده

// اتصال به فایل توابع کمکی
require_once __DIR__ . '/functions.php';
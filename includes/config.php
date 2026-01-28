<?php
/**
 * تنظیمات اصلی سایت
 * این فایل رو با اطلاعات هاست خودت پر کن
 */

// تنظیمات دیتابیس
define('DB_HOST', 'localhost');
define('DB_NAME', 'YOUR_DATABASE_NAME');  // نام دیتابیس
define('DB_USER', 'YOUR_DATABASE_USER');  // نام کاربری دیتابیس
define('DB_PASS', 'YOUR_DATABASE_PASS');  // رمز دیتابیس

// تنظیمات سایت
define('SITE_NAME', 'سیستم مدیریت بسته‌ها');
define('SITE_URL', 'https://yourdomain.com');  // آدرس سایتت

// تنظیمات امنیتی
define('CSRF_TOKEN_NAME', 'csrf_token');
define('SESSION_LIFETIME', 3600 * 8);  // 8 ساعت

// تنظیمات آپلود
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024);  // 5 مگابایت
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'webp']);

// منطقه زمانی
date_default_timezone_set('Asia/Tehran');

// نمایش خطا (در محیط تولید باید false باشه)
define('DEBUG_MODE', false);

if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

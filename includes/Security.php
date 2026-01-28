<?php
/**
 * کلاس امنیت
 * شامل توابع CSRF، XSS و validation
 */

class Security {
    
    /**
     * تولید توکن CSRF
     */
    public static function generateCSRFToken(): string {
        if (empty($_SESSION[CSRF_TOKEN_NAME])) {
            $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
        }
        return $_SESSION[CSRF_TOKEN_NAME];
    }
    
    /**
     * تایید توکن CSRF
     */
    public static function validateCSRFToken(?string $token): bool {
        if (empty($token) || empty($_SESSION[CSRF_TOKEN_NAME])) {
            return false;
        }
        return hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
    }
    
    /**
     * فیلد مخفی CSRF برای فرم‌ها
     */
    public static function csrfField(): string {
        $token = self::generateCSRFToken();
        return '<input type="hidden" name="' . CSRF_TOKEN_NAME . '" value="' . $token . '">';
    }
    
    /**
     * پاکسازی ورودی برای نمایش (جلوگیری از XSS)
     */
    public static function escape(?string $string): string {
        return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * پاکسازی ورودی
     */
    public static function sanitize(?string $string): string {
        if ($string === null) return '';
        return trim(strip_tags($string));
    }
    
    /**
     * اعتبارسنجی شماره تلفن ایرانی
     */
    public static function validatePhone(string $phone): bool {
        $phone = self::sanitize($phone);
        return preg_match('/^09[0-9]{9}$/', $phone) || preg_match('/^9[0-9]{9}$/', $phone);
    }
    
    /**
     * نرمال‌سازی شماره تلفن
     */
    public static function normalizePhone(string $phone): string {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($phone) === 10 && $phone[0] === '9') {
            $phone = '0' . $phone;
        }
        return $phone;
    }
    
    /**
     * بررسی امن بودن آپلود فایل
     */
    public static function validateUpload(array $file): array {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['valid' => false, 'message' => 'خطا در آپلود فایل'];
        }
        
        if ($file['size'] > MAX_FILE_SIZE) {
            return ['valid' => false, 'message' => 'حجم فایل بیش از حد مجاز است'];
        }
        
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ALLOWED_EXTENSIONS)) {
            return ['valid' => false, 'message' => 'فرمت فایل مجاز نیست'];
        }
        
        // بررسی واقعی نوع فایل
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);
        $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        
        if (!in_array($mimeType, $allowedMimes)) {
            return ['valid' => false, 'message' => 'نوع فایل مجاز نیست'];
        }
        
        return ['valid' => true, 'extension' => $ext];
    }
    
    /**
     * ذخیره امن فایل آپلود شده
     */
    public static function saveUpload(array $file): ?string {
        $validation = self::validateUpload($file);
        if (!$validation['valid']) {
            return null;
        }
        
        // ساخت نام یکتا برای فایل
        $filename = uniqid('pkg_', true) . '.' . $validation['extension'];
        $destination = UPLOAD_DIR . $filename;
        
        // ساخت پوشه uploads اگه وجود نداره
        if (!is_dir(UPLOAD_DIR)) {
            mkdir(UPLOAD_DIR, 0755, true);
        }
        
        if (move_uploaded_file($file['tmp_name'], $destination)) {
            return $filename;
        }
        
        return null;
    }
}

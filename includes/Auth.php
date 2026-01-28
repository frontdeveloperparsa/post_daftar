<?php
/**
 * کلاس احراز هویت
 * مدیریت لاگین، لاگ‌اوت و session
 */

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Security.php';

class Auth {
    
    public static function init(): void {
        if (session_status() === PHP_SESSION_NONE) {
            ini_set('session.cookie_httponly', 1);
            ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
            ini_set('session.use_strict_mode', 1);
            session_start();
        }
        
        // بررسی انقضای session
        if (isset($_SESSION['last_activity']) && 
            (time() - $_SESSION['last_activity'] > SESSION_LIFETIME)) {
            self::logout();
        }
        $_SESSION['last_activity'] = time();
    }
    
    public static function login(string $username, string $password): array {
        $db = Database::getInstance();
        
        $stmt = $db->prepare("SELECT id, username, password, full_name FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if (!$user || !password_verify($password, $user['password'])) {
            return ['success' => false, 'message' => 'نام کاربری یا رمز عبور اشتباه است'];
        }
        
        // بازسازی session برای جلوگیری از Session Fixation
        session_regenerate_id(true);
        
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['logged_in'] = true;
        $_SESSION['last_activity'] = time();
        
        return ['success' => true, 'message' => 'ورود موفق'];
    }
    
    public static function logout(): void {
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
    }
    
    public static function isLoggedIn(): bool {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }
    
    public static function requireLogin(): void {
        if (!self::isLoggedIn()) {
            header('Location: /login.php');
            exit;
        }
    }
    
    public static function getUserId(): ?int {
        return $_SESSION['user_id'] ?? null;
    }
    
    public static function getUsername(): ?string {
        return $_SESSION['username'] ?? null;
    }
    
    public static function getFullName(): ?string {
        return $_SESSION['full_name'] ?? null;
    }
}

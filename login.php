<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/Auth.php';
require_once __DIR__ . '/includes/Security.php';

Auth::init();

// اگه لاگین هست برو داشبورد
if (Auth::isLoggedIn()) {
    header('Location: /admin/dashboard.php');
    exit;
}

$error = '';

// پردازش فرم
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // بررسی CSRF
    if (!Security::validateCSRFToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
        $error = 'خطای امنیتی. لطفا دوباره تلاش کنید.';
    } else {
        $username = Security::sanitize($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if (empty($username) || empty($password)) {
            $error = 'نام کاربری و رمز عبور الزامی است';
        } else {
            $result = Auth::login($username, $password);
            if ($result['success']) {
                header('Location: /admin/dashboard.php');
                exit;
            } else {
                $error = $result['message'];
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ورود به سیستم</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css" rel="stylesheet">
    <style>* { font-family: 'Vazirmatn', sans-serif; }</style>
</head>
<body class="min-h-screen bg-slate-100 flex items-center justify-center p-4">
    
    <div class="w-full max-w-md">
        <!-- لوگو -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-slate-800 rounded-2xl mb-4">
                <svg class="w-8 h-8 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
            </div>
            <h1 class="text-2xl font-medium text-slate-800">سیستم مدیریت بسته‌ها</h1>
            <p class="text-slate-500 mt-2">برای ادامه وارد شوید</p>
        </div>
        
        <!-- فرم -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8">
            <?php if ($error): ?>
            <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm">
                <?= Security::escape($error) ?>
            </div>
            <?php endif; ?>
            
            <form method="POST" class="space-y-5">
                <?= Security::csrfField() ?>
                
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">نام کاربری</label>
                    <input type="text" name="username" required autofocus
                           class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition"
                           placeholder="نام کاربری خود را وارد کنید">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">رمز عبور</label>
                    <input type="password" name="password" required
                           class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition"
                           placeholder="رمز عبور خود را وارد کنید">
                </div>
                
                <button type="submit" 
                        class="w-full py-3 bg-slate-800 text-white rounded-xl font-medium hover:bg-slate-700 transition">
                    ورود به سیستم
                </button>
            </form>
        </div>
        
        <p class="text-center text-slate-400 text-sm mt-6">
            رمز پیش‌فرض: admin / admin123
        </p>
    </div>
    
</body>
</html>

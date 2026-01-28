<?php
session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/Security.php';
require_once __DIR__ . '/../includes/Customer.php';

// اگه لاگین بود برو به پنل
if (isset($_SESSION['customer_id'])) {
    header('Location: /customer/dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Security::validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'خطای امنیتی. لطفا صفحه را رفرش کنید.';
    } else {
        $phone = Security::sanitize($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if (empty($phone) || empty($password)) {
            $error = 'لطفا شماره موبایل و رمز عبور را وارد کنید';
        } else {
            $customer = new Customer();
            $result = $customer->login($phone, $password);
            
            if ($result['success']) {
                // ذخیره در سشن
                $_SESSION['customer_id'] = $result['customer']['id'];
                $_SESSION['customer_name'] = $result['customer']['first_name'] . ' ' . $result['customer']['last_name'];
                $_SESSION['customer_phone'] = $result['customer']['phone'];
                
                // ریجنریت سشن برای امنیت
                session_regenerate_id(true);
                
                header('Location: /customer/dashboard.php');
                exit;
            } else {
                $error = $result['message'];
            }
        }
    }
}

$csrfToken = Security::generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ورود - سامانه رهگیری بسته</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css" rel="stylesheet">
    <style>
        * { font-family: 'Vazirmatn', sans-serif; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center p-4">
    
    <div class="w-full max-w-md">
        <!-- لوگو -->
        <div class="text-center mb-8">
            <a href="/" class="inline-block">
                <h1 class="text-2xl font-bold text-slate-800">سامانه رهگیری بسته</h1>
            </a>
            <p class="text-slate-500 mt-2">ورود به حساب کاربری</p>
        </div>
        
        <!-- فرم -->
        <div class="bg-white rounded-2xl shadow-xl p-8">
            
            <?php if ($error): ?>
                <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="space-y-5">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">شماره موبایل</label>
                    <input 
                        type="tel" 
                        name="phone" 
                        value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>"
                        placeholder="09123456789"
                        maxlength="11"
                        class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition"
                        required
                    >
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">رمز عبور</label>
                    <input 
                        type="password" 
                        name="password" 
                        class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition"
                        required
                    >
                </div>
                
                <button type="submit" class="w-full bg-amber-500 text-slate-800 py-3 rounded-xl font-medium hover:bg-amber-400 transition">
                    ورود
                </button>
            </form>
            
            <div class="mt-6 text-center text-sm text-slate-500">
                حساب کاربری ندارید؟
                <a href="/customer/register.php" class="text-amber-600 font-medium hover:underline">ثبت‌نام کنید</a>
            </div>
        </div>
        
        <div class="text-center mt-6 space-y-2">
            <a href="/" class="block text-sm text-slate-500 hover:text-slate-700">بازگشت به صفحه اصلی</a>
            <a href="/login.php" class="block text-sm text-slate-400 hover:text-slate-600">ورود کارمندان</a>
        </div>
    </div>
    
</body>
</html>

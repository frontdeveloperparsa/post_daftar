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
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Security::validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'خطای امنیتی. لطفا صفحه را رفرش کنید.';
    } else {
        $firstName = Security::sanitize($_POST['first_name'] ?? '');
        $lastName = Security::sanitize($_POST['last_name'] ?? '');
        $nationalCode = Security::sanitize($_POST['national_code'] ?? '');
        $phone = Security::sanitize($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';
        $passwordConfirm = $_POST['password_confirm'] ?? '';
        
        // اعتبارسنجی
        if (empty($firstName) || empty($lastName) || empty($nationalCode) || empty($phone) || empty($password)) {
            $error = 'لطفا همه فیلدها را پر کنید';
        } elseif (!preg_match('/^[0-9]{10}$/', $nationalCode)) {
            $error = 'کد ملی باید ۱۰ رقم باشد';
        } elseif (!preg_match('/^09[0-9]{9}$/', $phone)) {
            $error = 'شماره موبایل معتبر نیست (مثال: 09123456789)';
        } elseif (strlen($password) < 6) {
            $error = 'رمز عبور باید حداقل ۶ کاراکتر باشد';
        } elseif ($password !== $passwordConfirm) {
            $error = 'رمز عبور و تکرار آن یکسان نیست';
        } else {
            $result = Customer::register($firstName, $lastName, $nationalCode, $phone, $password);
            
            if ($result['success']) {
                $success = 'ثبت‌نام با موفقیت انجام شد. می‌توانید وارد شوید.';
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
    <title>ثبت‌نام - سامانه رهگیری بسته</title>
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
            <p class="text-slate-500 mt-2">ثبت‌نام حساب کاربری</p>
        </div>
        
        <!-- فرم -->
        <div class="bg-white rounded-2xl shadow-xl p-8">
            
            <?php if ($error): ?>
                <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-700 rounded-xl text-sm">
                    <?= htmlspecialchars($success) ?>
                    <a href="/customer/login.php" class="block mt-2 font-medium text-green-800 underline">ورود به حساب</a>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="space-y-5">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">نام</label>
                        <input 
                            type="text" 
                            name="first_name" 
                            value="<?= htmlspecialchars($_POST['first_name'] ?? '') ?>"
                            class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition"
                            required
                        >
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">نام خانوادگی</label>
                        <input 
                            type="text" 
                            name="last_name" 
                            value="<?= htmlspecialchars($_POST['last_name'] ?? '') ?>"
                            class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition"
                            required
                        >
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">کد ملی</label>
                    <input 
                        type="text" 
                        name="national_code" 
                        value="<?= htmlspecialchars($_POST['national_code'] ?? '') ?>"
                        placeholder="۱۰ رقم"
                        maxlength="10"
                        class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition"
                        required
                    >
                </div>
                
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
                        placeholder="حداقل ۶ کاراکتر"
                        class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition"
                        required
                        minlength="6"
                    >
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">تکرار رمز عبور</label>
                    <input 
                        type="password" 
                        name="password_confirm" 
                        class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition"
                        required
                        minlength="6"
                    >
                </div>
                
                <button type="submit" class="w-full bg-amber-500 text-slate-800 py-3 rounded-xl font-medium hover:bg-amber-400 transition">
                    ثبت‌نام
                </button>
            </form>
            
            <div class="mt-6 text-center text-sm text-slate-500">
                قبلا ثبت‌نام کردید؟
                <a href="/customer/login.php" class="text-amber-600 font-medium hover:underline">وارد شوید</a>
            </div>
        </div>
        
        <div class="text-center mt-6">
            <a href="/" class="text-sm text-slate-500 hover:text-slate-700">بازگشت به صفحه اصلی</a>
        </div>
    </div>
    
</body>
</html>

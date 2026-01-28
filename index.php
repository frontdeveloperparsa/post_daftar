<?php
session_start();
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/Database.php';
require_once __DIR__ . '/includes/Security.php';
require_once __DIR__ . '/includes/Package.php';
require_once __DIR__ . '/includes/Shamsi.php';

$results = null;
$searchQuery = '';
$error = '';

// جستجوی بسته
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search'])) {
    if (!Security::validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'خطای امنیتی. لطفا صفحه را رفرش کنید.';
    } else {
        $searchQuery = Security::sanitize($_POST['query'] ?? '');
        if (strlen($searchQuery) >= 3) {
            $results = Package::publicSearch($searchQuery);
        } else {
            $error = 'حداقل ۳ کاراکتر وارد کنید';
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
    <title>سامانه رهگیری بسته</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css" rel="stylesheet">
    <style>
        * { font-family: 'Vazirmatn', sans-serif; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    
    <!-- هدر -->
    <header class="bg-slate-800 text-white shadow-lg">
        <div class="max-w-6xl mx-auto px-4 py-4 flex items-center justify-between">
            <h1 class="text-xl font-bold">سامانه رهگیری بسته</h1>
            <div class="flex gap-3">
                <?php if (isset($_SESSION['customer_id'])): ?>
                    <a href="/customer/dashboard.php" class="bg-amber-500 text-slate-800 px-4 py-2 rounded-lg text-sm font-medium hover:bg-amber-400 transition">پنل من</a>
                    <a href="/customer/logout.php" class="bg-slate-700 px-4 py-2 rounded-lg text-sm hover:bg-slate-600 transition">خروج</a>
                <?php else: ?>
                    <a href="/customer/login.php" class="bg-slate-700 px-4 py-2 rounded-lg text-sm hover:bg-slate-600 transition">ورود</a>
                    <a href="/customer/register.php" class="bg-amber-500 text-slate-800 px-4 py-2 rounded-lg text-sm font-medium hover:bg-amber-400 transition">ثبت‌نام</a>
                <?php endif; ?>
            </div>
        </div>
    </header>
    
    <!-- بخش اصلی -->
    <main class="max-w-4xl mx-auto px-4 py-12">
        
        <!-- جستجو -->
        <div class="bg-white rounded-2xl shadow-xl p-8 mb-8">
            <div class="text-center mb-8">
                <h2 class="text-2xl font-bold text-slate-800 mb-2">رهگیری بسته</h2>
                <p class="text-slate-500">با کد رهگیری، شماره موبایل یا نام گیرنده جستجو کنید</p>
            </div>
            
            <form method="POST" class="max-w-xl mx-auto">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                <div class="flex gap-3">
                    <input 
                        type="text" 
                        name="query" 
                        value="<?= htmlspecialchars($searchQuery) ?>"
                        placeholder="کد رهگیری، شماره موبایل یا نام..."
                        class="flex-1 px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition"
                        required
                        minlength="3"
                    >
                    <button type="submit" name="search" class="bg-amber-500 text-slate-800 px-6 py-3 rounded-xl font-medium hover:bg-amber-400 transition flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        جستجو
                    </button>
                </div>
            </form>
            
            <?php if ($error): ?>
                <div class="mt-4 text-center text-red-500"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
        </div>
        
        <!-- نتایج جستجو -->
        <?php if ($results !== null): ?>
            <div class="bg-white rounded-2xl shadow-xl p-8">
                <h3 class="text-lg font-bold text-slate-800 mb-4">نتایج جستجو</h3>
                
                <?php if (empty($results)): ?>
                    <div class="text-center py-8 text-slate-500">
                        <svg class="w-16 h-16 mx-auto mb-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p>بسته‌ای پیدا نشد</p>
                    </div>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-slate-200">
                                    <th class="text-right py-3 px-4 text-slate-600 font-medium">کد رهگیری</th>
                                    <th class="text-right py-3 px-4 text-slate-600 font-medium">گیرنده</th>
                                    <th class="text-right py-3 px-4 text-slate-600 font-medium">نوع</th>
                                    <th class="text-right py-3 px-4 text-slate-600 font-medium">وضعیت</th>
                                    <th class="text-right py-3 px-4 text-slate-600 font-medium">تاریخ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($results as $package): ?>
                                    <tr class="border-b border-slate-100 hover:bg-slate-50">
                                        <td class="py-3 px-4 font-mono text-sm"><?= htmlspecialchars($package['tracking_code']) ?></td>
                                        <td class="py-3 px-4"><?= htmlspecialchars($package['receiver_name']) ?></td>
                                        <td class="py-3 px-4"><?= htmlspecialchars($package['type_name'] ?? '-') ?></td>
                                        <td class="py-3 px-4">
                                            <?php if ($package['status'] === 'delivered'): ?>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">تحویل شده</span>
                                            <?php else: ?>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">در انتظار تحویل</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-3 px-4 text-sm text-slate-500">
                                            <?= Shamsi::date($package['registered_at']) ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <!-- راهنما -->
        <div class="mt-8 grid md:grid-cols-3 gap-6">
            <div class="bg-white rounded-xl p-6 text-center shadow-md">
                <div class="w-12 h-12 bg-amber-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <h3 class="font-bold text-slate-800 mb-2">جستجوی آسان</h3>
                <p class="text-sm text-slate-500">با کد رهگیری یا شماره موبایل بسته خود را پیدا کنید</p>
            </div>
            <div class="bg-white rounded-xl p-6 text-center shadow-md">
                <div class="w-12 h-12 bg-amber-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h3 class="font-bold text-slate-800 mb-2">رهگیری لحظه‌ای</h3>
                <p class="text-sm text-slate-500">وضعیت بسته خود را به صورت آنی مشاهده کنید</p>
            </div>
            <div class="bg-white rounded-xl p-6 text-center shadow-md">
                <div class="w-12 h-12 bg-amber-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <h3 class="font-bold text-slate-800 mb-2">امن و مطمئن</h3>
                <p class="text-sm text-slate-500">اطلاعات شما با امنیت کامل محافظت می‌شود</p>
            </div>
        </div>
        
    </main>
    
    <!-- فوتر -->
    <footer class="bg-slate-800 text-slate-400 py-6 mt-12">
        <div class="max-w-6xl mx-auto px-4 text-center text-sm">
            <p>تمامی حقوق محفوظ است &copy; <?= Shamsi::toShamsi()['year'] ?></p>
        </div>
    </footer>
    
</body>
</html>

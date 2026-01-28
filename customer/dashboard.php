<?php
session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/Security.php';
require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/Shamsi.php';

// چک لاگین
if (!isset($_SESSION['customer_id'])) {
    header('Location: /customer/login.php');
    exit;
}

// گرفتن بسته‌های مشتری
$db = Database::getInstance();
$stmt = $db->prepare("
    SELECT p.*, pt.name as type_name
    FROM packages p
    LEFT JOIN package_types pt ON p.package_type_id = pt.id
    WHERE p.receiver_phone = ?
    ORDER BY p.registered_at DESC
");
$stmt->execute([$_SESSION['customer_phone']]);
$packages = $stmt->fetchAll();

// آمار
$totalPackages = count($packages);
$deliveredPackages = count(array_filter($packages, fn($p) => $p['status'] === 'delivered'));
$pendingPackages = $totalPackages - $deliveredPackages;
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>پنل کاربری - سامانه رهگیری بسته</title>
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
            <a href="/" class="text-xl font-bold">سامانه رهگیری بسته</a>
            <div class="flex items-center gap-4">
                <span class="text-slate-300 text-sm"><?= htmlspecialchars($_SESSION['customer_name']) ?></span>
                <a href="/customer/logout.php" class="bg-slate-700 px-4 py-2 rounded-lg text-sm hover:bg-slate-600 transition">خروج</a>
            </div>
        </div>
    </header>
    
    <main class="max-w-6xl mx-auto px-4 py-8">
        
        <!-- آمار -->
        <div class="grid md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-xl p-6 shadow-md border-r-4 border-slate-800">
                <p class="text-slate-500 text-sm mb-1">کل بسته‌ها</p>
                <p class="text-3xl font-bold text-slate-800"><?= $totalPackages ?></p>
            </div>
            <div class="bg-white rounded-xl p-6 shadow-md border-r-4 border-amber-500">
                <p class="text-slate-500 text-sm mb-1">در انتظار تحویل</p>
                <p class="text-3xl font-bold text-amber-600"><?= $pendingPackages ?></p>
            </div>
            <div class="bg-white rounded-xl p-6 shadow-md border-r-4 border-green-500">
                <p class="text-slate-500 text-sm mb-1">تحویل شده</p>
                <p class="text-3xl font-bold text-green-600"><?= $deliveredPackages ?></p>
            </div>
        </div>
        
        <!-- لیست بسته‌ها -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <div class="p-6 border-b border-slate-200">
                <h2 class="text-lg font-bold text-slate-800">بسته‌های من</h2>
            </div>
            
            <?php if (empty($packages)): ?>
                <div class="p-12 text-center text-slate-500">
                    <svg class="w-16 h-16 mx-auto mb-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                    </svg>
                    <p>هنوز بسته‌ای برای شما ثبت نشده است</p>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="text-right py-4 px-6 text-slate-600 font-medium">کد رهگیری</th>
                                <th class="text-right py-4 px-6 text-slate-600 font-medium">نوع بسته</th>
                                <th class="text-right py-4 px-6 text-slate-600 font-medium">تاریخ ثبت</th>
                                <th class="text-right py-4 px-6 text-slate-600 font-medium">وضعیت</th>
                                <th class="text-right py-4 px-6 text-slate-600 font-medium">تاریخ تحویل</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php foreach ($packages as $package): ?>
                                <tr class="hover:bg-slate-50">
                                    <td class="py-4 px-6 font-mono text-sm"><?= htmlspecialchars($package['tracking_code']) ?></td>
                                    <td class="py-4 px-6"><?= htmlspecialchars($package['type_name'] ?? '-') ?></td>
                                    <td class="py-4 px-6 text-sm text-slate-500"><?= Shamsi::datetime($package['registered_at']) ?></td>
                                    <td class="py-4 px-6">
                                        <?php if ($package['status'] === 'delivered'): ?>
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">تحویل شده</span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-amber-100 text-amber-800">در انتظار</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-4 px-6 text-sm text-slate-500">
                                        <?= $package['delivered_at'] ? Shamsi::datetime($package['delivered_at']) : '-' ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
        
    </main>
    
</body>
</html>

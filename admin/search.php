<?php
session_start();

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/Auth.php';
require_once __DIR__ . '/../includes/Security.php';
require_once __DIR__ . '/../includes/Package.php';
require_once __DIR__ . '/../includes/PackageType.php';
require_once __DIR__ . '/../includes/Shamsi.php';

Auth::requireLogin();

$types = PackageType::getAll();
$packages = [];
$searched = false;

// جستجو
if (!empty($_GET['search'])) {
    $searched = true;
    $packages = Package::search([
        'tracking_code' => $_GET['tracking_code'] ?? '',
        'receiver_name' => $_GET['receiver_name'] ?? '',
        'receiver_phone' => $_GET['receiver_phone'] ?? '',
        'status' => $_GET['status'] ?? '',
        'type_id' => $_GET['type_id'] ?? '',
        'date_from' => $_GET['date_from'] ?? '',
        'date_to' => $_GET['date_to'] ?? '',
    ]);
}

// خروجی CSV
if (isset($_GET['export']) && !empty($packages)) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="packages-' . Shamsi::date() . '.csv"');
    echo "\xEF\xBB\xBF";
    
    $output = fopen('php://output', 'w');
    fputcsv($output, ['کد رهگیری', 'نام گیرنده', 'تلفن', 'نوع بسته', 'وضعیت', 'تاریخ ثبت', 'تاریخ تحویل']);
    
    foreach ($packages as $pkg) {
        fputcsv($output, [
            $pkg['tracking_code'],
            $pkg['receiver_name'],
            $pkg['receiver_phone'],
            $pkg['type_name'],
            $pkg['status'] === 'delivered' ? 'تحویل شده' : 'در انتظار',
            Shamsi::datetime($pkg['registered_at']),
            $pkg['delivered_at'] ? Shamsi::datetime($pkg['delivered_at']) : '-'
        ]);
    }
    fclose($output);
    exit;
}

$pageTitle = 'جستجوی پیشرفته';
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> - <?= SITE_NAME ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css" rel="stylesheet">
    <style>* { font-family: 'Vazirmatn', sans-serif; }</style>
</head>
<body class="bg-slate-50">

<div class="flex">
    <?php include __DIR__ . '/../includes/templates/sidebar.php'; ?>
    
    <main class="flex-1 mr-64 p-8">
        <h1 class="text-2xl font-medium text-slate-800 mb-8">جستجوی پیشرفته</h1>
        
        <!-- فرم جستجو -->
        <form method="GET" class="bg-white rounded-2xl border border-slate-200 p-6 mb-6">
            <input type="hidden" name="search" value="1">
            
            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4 mb-6">
                <div>
                    <label class="block text-sm text-slate-600 mb-1">کد رهگیری</label>
                    <input type="text" name="tracking_code" value="<?= Security::escape($_GET['tracking_code'] ?? '') ?>"
                           class="w-full px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-amber-500 outline-none">
                </div>
                
                <div>
                    <label class="block text-sm text-slate-600 mb-1">نام گیرنده</label>
                    <input type="text" name="receiver_name" value="<?= Security::escape($_GET['receiver_name'] ?? '') ?>"
                           class="w-full px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-amber-500 outline-none">
                </div>
                
                <div>
                    <label class="block text-sm text-slate-600 mb-1">شماره تلفن</label>
                    <input type="text" name="receiver_phone" value="<?= Security::escape($_GET['receiver_phone'] ?? '') ?>"
                           class="w-full px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-amber-500 outline-none">
                </div>
                
                <div>
                    <label class="block text-sm text-slate-600 mb-1">وضعیت</label>
                    <select name="status" class="w-full px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-amber-500 outline-none">
                        <option value="">همه</option>
                        <option value="pending" <?= ($_GET['status'] ?? '') === 'pending' ? 'selected' : '' ?>>در انتظار</option>
                        <option value="delivered" <?= ($_GET['status'] ?? '') === 'delivered' ? 'selected' : '' ?>>تحویل شده</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm text-slate-600 mb-1">نوع بسته</label>
                    <select name="type_id" class="w-full px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-amber-500 outline-none">
                        <option value="">همه</option>
                        <?php foreach ($types as $type): ?>
                        <option value="<?= $type['id'] ?>" <?= ($_GET['type_id'] ?? '') == $type['id'] ? 'selected' : '' ?>><?= Security::escape($type['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm text-slate-600 mb-1">از تاریخ</label>
                    <input type="date" name="date_from" value="<?= Security::escape($_GET['date_from'] ?? '') ?>"
                           class="w-full px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-amber-500 outline-none">
                </div>
                
                <div>
                    <label class="block text-sm text-slate-600 mb-1">تا تاریخ</label>
                    <input type="date" name="date_to" value="<?= Security::escape($_GET['date_to'] ?? '') ?>"
                           class="w-full px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-amber-500 outline-none">
                </div>
            </div>
            
            <div class="flex gap-3">
                <button type="submit" class="px-6 py-2 bg-slate-800 text-white rounded-lg hover:bg-slate-700 transition">جستجو</button>
                <?php if (!empty($packages)): ?>
                <a href="?<?= http_build_query(array_merge($_GET, ['export' => '1'])) ?>" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-500 transition">دانلود CSV</a>
                <?php endif; ?>
                <a href="/admin/search.php" class="px-6 py-2 border border-slate-200 rounded-lg hover:bg-slate-50 transition">پاک کردن</a>
            </div>
        </form>
        
        <!-- نتایج -->
        <?php if ($searched): ?>
        <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 bg-slate-50">
                <p class="text-sm text-slate-600"><?= count($packages) ?> نتیجه یافت شد</p>
            </div>
            
            <table class="w-full">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="text-right px-6 py-3 text-sm font-medium text-slate-600">کد رهگیری</th>
                        <th class="text-right px-6 py-3 text-sm font-medium text-slate-600">گیرنده</th>
                        <th class="text-right px-6 py-3 text-sm font-medium text-slate-600">تلفن</th>
                        <th class="text-right px-6 py-3 text-sm font-medium text-slate-600">نوع</th>
                        <th class="text-right px-6 py-3 text-sm font-medium text-slate-600">وضعیت</th>
                        <th class="text-right px-6 py-3 text-sm font-medium text-slate-600">تاریخ ثبت</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (empty($packages)): ?>
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-slate-400">نتیجه‌ای یافت نشد</td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($packages as $pkg): ?>
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-6 py-4 font-medium"><?= Security::escape($pkg['tracking_code']) ?></td>
                        <td class="px-6 py-4"><?= Security::escape($pkg['receiver_name']) ?></td>
                        <td class="px-6 py-4" dir="ltr"><?= Security::escape($pkg['receiver_phone']) ?></td>
                        <td class="px-6 py-4"><?= Security::escape($pkg['type_name']) ?></td>
                        <td class="px-6 py-4">
                            <?php if ($pkg['status'] === 'delivered'): ?>
                            <span class="px-2 py-1 bg-green-100 text-green-700 text-xs rounded-full">تحویل شده</span>
                            <?php else: ?>
                            <span class="px-2 py-1 bg-amber-100 text-amber-700 text-xs rounded-full">در انتظار</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 text-slate-500"><?= Shamsi::date($pkg['registered_at']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </main>
</div>

</body>
</html>

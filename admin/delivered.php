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

$query = $_GET['q'] ?? '';
$typeId = $_GET['type_id'] ?? '';
$viewId = $_GET['view'] ?? null;

// مشاهده جزئیات بسته
$packageDetail = null;
if ($viewId) {
    $packageDetail = Package::getById((int)$viewId);
}

// جستجو یا لیست
if ($query) {
    $packages = Package::searchDelivered($query, $typeId ? (int)$typeId : null);
} else {
    $packages = Package::getDelivered(100, 0);
}

$packageTypes = PackageType::getAll();
$pageTitle = 'بسته‌های تحویل شده';

require_once __DIR__ . '/../includes/templates/header.php';
?>

<div class="flex">
    <?php include __DIR__ . '/../includes/templates/sidebar.php'; ?>
    
    <main class="flex-1 mr-64 p-8">
        <h1 class="text-2xl font-medium text-slate-800 mb-6">بسته‌های تحویل شده</h1>

        <!-- فرم جستجو -->
        <div class="bg-white rounded-xl border border-slate-200 p-4 mb-6">
            <form method="GET" class="flex gap-4 items-end">
                <div class="flex-1">
                    <label class="block text-sm text-slate-600 mb-1">جستجو</label>
                    <input type="text" name="q" class="w-full px-4 py-2 border border-slate-200 rounded-lg focus:outline-none focus:border-slate-400" placeholder="کد رهگیری، نام، شماره..." value="<?= Security::escape($query) ?>">
                </div>
                <div class="w-48">
                    <label class="block text-sm text-slate-600 mb-1">نوع بسته</label>
                    <select name="type_id" class="w-full px-4 py-2 border border-slate-200 rounded-lg focus:outline-none focus:border-slate-400">
                        <option value="">همه انواع</option>
                        <?php foreach ($packageTypes as $type): ?>
                            <option value="<?= $type['id'] ?>" <?= $typeId == $type['id'] ? 'selected' : '' ?>><?= Security::escape($type['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="px-6 py-2 bg-slate-800 text-white rounded-lg hover:bg-slate-700 transition">جستجو</button>
                <a href="delivered.php" class="px-6 py-2 border border-slate-200 rounded-lg hover:bg-slate-50 transition">پاک کردن</a>
            </form>
        </div>

        <!-- مودال مشاهده جزئیات -->
        <?php if ($packageDetail): ?>
        <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" onclick="window.location.href='delivered.php<?= $query ? '?q=' . urlencode($query) : '' ?>'">
            <div class="bg-white rounded-2xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto" onclick="event.stopPropagation()">
                <div class="flex justify-between items-center p-6 border-b border-slate-200">
                    <h3 class="text-xl font-medium">جزئیات تحویل بسته</h3>
                    <a href="delivered.php<?= $query ? '?q=' . urlencode($query) : '' ?>" class="text-2xl text-slate-400 hover:text-slate-600">&times;</a>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-2 gap-4 mb-6">
                        <div>
                            <label class="block text-sm text-slate-500 mb-1">کد رهگیری</label>
                            <span class="font-medium"><?= Security::escape($packageDetail['tracking_code']) ?></span>
                        </div>
                        <div>
                            <label class="block text-sm text-slate-500 mb-1">گیرنده بسته</label>
                            <span class="font-medium"><?= Security::escape($packageDetail['receiver_name']) ?></span>
                        </div>
                        <div>
                            <label class="block text-sm text-slate-500 mb-1">شماره گیرنده</label>
                            <span class="font-medium"><?= Security::escape($packageDetail['receiver_phone']) ?></span>
                        </div>
                        <div>
                            <label class="block text-sm text-slate-500 mb-1">نوع بسته</label>
                            <span class="font-medium"><?= Security::escape($packageDetail['type_name']) ?></span>
                        </div>
                        <div>
                            <label class="block text-sm text-slate-500 mb-1">تاریخ ثبت</label>
                            <span class="font-medium"><?= Shamsi::datetime($packageDetail['registered_at']) ?></span>
                        </div>
                        <div>
                            <label class="block text-sm text-slate-500 mb-1">تاریخ تحویل</label>
                            <span class="font-medium"><?= Shamsi::datetime($packageDetail['delivered_at']) ?></span>
                        </div>
                        <div>
                            <label class="block text-sm text-slate-500 mb-1">تحویل دهنده</label>
                            <span class="font-medium"><?= Security::escape($packageDetail['delivered_by_name']) ?></span>
                        </div>
                        <div>
                            <label class="block text-sm text-slate-500 mb-1">روش تحویل</label>
                            <span class="font-medium"><?= $packageDetail['delivery_method'] == 'signature' ? 'امضا' : ($packageDetail['delivery_method'] == 'photo' ? 'عکس' : '-') ?></span>
                        </div>
                    </div>

                    <?php if ($packageDetail['delivered_to_name'] || $packageDetail['delivered_to_phone'] || $packageDetail['receiver_national_code']): ?>
                    <div class="border-t border-slate-200 pt-6 mb-6">
                        <h4 class="font-medium mb-4">اطلاعات تحویل گیرنده</h4>
                        <div class="grid grid-cols-2 gap-4">
                            <?php if ($packageDetail['delivered_to_name']): ?>
                            <div>
                                <label class="block text-sm text-slate-500 mb-1">نام تحویل گیرنده</label>
                                <span class="font-medium"><?= Security::escape($packageDetail['delivered_to_name']) ?></span>
                            </div>
                            <?php endif; ?>
                            <?php if ($packageDetail['delivered_to_phone']): ?>
                            <div>
                                <label class="block text-sm text-slate-500 mb-1">شماره تحویل گیرنده</label>
                                <span class="font-medium"><?= Security::escape($packageDetail['delivered_to_phone']) ?></span>
                            </div>
                            <?php endif; ?>
                            <?php if ($packageDetail['receiver_national_code']): ?>
                            <div>
                                <label class="block text-sm text-slate-500 mb-1">کد ملی تحویل گیرنده</label>
                                <span class="font-medium"><?= Security::escape($packageDetail['receiver_national_code']) ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if ($packageDetail['receiver_signature']): ?>
                    <div class="border-t border-slate-200 pt-6 mb-6">
                        <h4 class="font-medium mb-4">امضا تحویل گیرنده</h4>
                        <div class="bg-slate-100 rounded-lg p-4 text-center">
                            <img src="<?= $packageDetail['receiver_signature'] ?>" alt="امضا" class="max-w-full max-h-48 mx-auto rounded">
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if ($packageDetail['delivery_photo']): ?>
                    <div class="border-t border-slate-200 pt-6">
                        <h4 class="font-medium mb-4">عکس تحویل</h4>
                        <div class="bg-slate-100 rounded-lg p-4 text-center">
                            <img src="<?= SITE_URL ?>/uploads/<?= $packageDetail['delivery_photo'] ?>" alt="عکس تحویل" class="max-w-full max-h-64 mx-auto rounded">
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- جدول بسته‌ها -->
        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
            <table class="w-full">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="text-right px-4 py-3 text-sm font-medium text-slate-600">کد رهگیری</th>
                        <th class="text-right px-4 py-3 text-sm font-medium text-slate-600">گیرنده</th>
                        <th class="text-right px-4 py-3 text-sm font-medium text-slate-600">شماره</th>
                        <th class="text-right px-4 py-3 text-sm font-medium text-slate-600">نوع</th>
                        <th class="text-right px-4 py-3 text-sm font-medium text-slate-600">تحویل گیرنده</th>
                        <th class="text-right px-4 py-3 text-sm font-medium text-slate-600">روش</th>
                        <th class="text-right px-4 py-3 text-sm font-medium text-slate-600">تاریخ تحویل</th>
                        <th class="text-right px-4 py-3 text-sm font-medium text-slate-600">عملیات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (empty($packages)): ?>
                    <tr>
                        <td colspan="8" class="px-4 py-12 text-center text-slate-400">بسته‌ای یافت نشد</td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($packages as $pkg): ?>
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-4 py-3 font-mono text-sm"><?= Security::escape($pkg['tracking_code']) ?></td>
                        <td class="px-4 py-3"><?= Security::escape($pkg['receiver_name']) ?></td>
                        <td class="px-4 py-3" dir="ltr"><?= Security::escape($pkg['receiver_phone']) ?></td>
                        <td class="px-4 py-3"><?= Security::escape($pkg['type_name']) ?></td>
                        <td class="px-4 py-3"><?= $pkg['delivered_to_name'] ? Security::escape($pkg['delivered_to_name']) : '-' ?></td>
                        <td class="px-4 py-3">
                            <?php if ($pkg['delivery_method'] == 'signature'): ?>
                                <span class="px-2 py-1 bg-blue-100 text-blue-700 text-xs rounded">امضا</span>
                            <?php elseif ($pkg['delivery_method'] == 'photo'): ?>
                                <span class="px-2 py-1 bg-amber-100 text-amber-700 text-xs rounded">عکس</span>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-3 text-slate-500 text-sm"><?= Shamsi::datetime($pkg['delivered_at']) ?></td>
                        <td class="px-4 py-3">
                            <a href="?view=<?= $pkg['id'] ?><?= $query ? '&q=' . urlencode($query) : '' ?>" class="px-3 py-1 bg-slate-100 text-slate-700 text-sm rounded hover:bg-slate-200 transition">مشاهده</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/../includes/templates/footer.php'; ?>

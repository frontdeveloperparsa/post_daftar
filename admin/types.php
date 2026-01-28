<?php
$pageTitle = 'مدیریت انواع بسته';
require_once __DIR__ . '/../includes/templates/header.php';
require_once __DIR__ . '/../includes/PackageType.php';

Auth::requireLogin();

$message = '';
$messageType = '';

// اضافه کردن نوع جدید
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add'])) {
    if (!Security::validateCSRFToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
        $message = 'خطای امنیتی';
        $messageType = 'error';
    } else {
        $name = Security::sanitize($_POST['name'] ?? '');
        if (empty($name)) {
            $message = 'نام نوع بسته الزامی است';
            $messageType = 'error';
        } else {
            $result = PackageType::create($name);
            if ($result['success']) {
                $message = 'نوع بسته با موفقیت اضافه شد';
                $messageType = 'success';
            } else {
                $message = $result['message'];
                $messageType = 'error';
            }
        }
    }
}

// حذف نوع
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
    if (!Security::validateCSRFToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
        $message = 'خطای امنیتی';
        $messageType = 'error';
    } else {
        $id = (int)$_POST['type_id'];
        $result = PackageType::delete($id);
        if ($result['success']) {
            $message = 'نوع بسته با موفقیت حذف شد';
            $messageType = 'success';
        } else {
            $message = $result['message'];
            $messageType = 'error';
        }
    }
}

$types = PackageType::getAll();
?>

<div class="flex">
    <?php include __DIR__ . '/../includes/templates/sidebar.php'; ?>
    
    <main class="flex-1 mr-64 p-8">
        <div class="max-w-2xl">
            <h1 class="text-2xl font-medium text-slate-800 mb-8">مدیریت انواع بسته</h1>
            
            <?php if ($message): ?>
            <div class="mb-6 p-4 rounded-xl <?= $messageType === 'success' ? 'bg-green-50 border border-green-200 text-green-700' : 'bg-red-50 border border-red-200 text-red-700' ?>">
                <?= Security::escape($message) ?>
            </div>
            <?php endif; ?>
            
            <!-- فرم اضافه کردن -->
            <form method="POST" class="bg-white rounded-2xl border border-slate-200 p-6 mb-6">
                <?= Security::csrfField() ?>
                <label class="block text-sm font-medium text-slate-700 mb-2">نوع بسته جدید</label>
                <div class="flex gap-3">
                    <input type="text" name="name" required
                           class="flex-1 px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500 outline-none"
                           placeholder="مثال: پاکت A4">
                    <button type="submit" name="add" value="1"
                            class="px-6 py-3 bg-slate-800 text-white rounded-xl font-medium hover:bg-slate-700 transition">
                        اضافه کردن
                    </button>
                </div>
            </form>
            
            <!-- لیست انواع -->
            <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
                <table class="w-full">
                    <thead class="bg-slate-50 border-b border-slate-200">
                        <tr>
                            <th class="text-right px-6 py-4 text-sm font-medium text-slate-600">نام</th>
                            <th class="text-right px-6 py-4 text-sm font-medium text-slate-600">تاریخ ایجاد</th>
                            <th class="text-left px-6 py-4 text-sm font-medium text-slate-600">عملیات</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach ($types as $type): ?>
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-6 py-4 font-medium"><?= Security::escape($type['name']) ?></td>
                            <td class="px-6 py-4 text-slate-500"><?= date('Y/m/d', strtotime($type['created_at'])) ?></td>
                            <td class="px-6 py-4">
                                <form method="POST" class="inline">
                                    <?= Security::csrfField() ?>
                                    <input type="hidden" name="type_id" value="<?= $type['id'] ?>">
                                    <button type="submit" name="delete" value="1"
                                            class="text-red-600 hover:text-red-700 text-sm"
                                            onclick="return confirm('آیا از حذف این نوع اطمینان دارید؟')">
                                        حذف
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/../includes/templates/footer.php'; ?>

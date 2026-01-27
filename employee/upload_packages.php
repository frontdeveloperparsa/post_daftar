<?php
require_once '../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'employee') {  // یا 'admin' اگر فقط مدیر
    header('Location: ../login.php');
    exit;
}

$error = '';
$success = '';

// گرفتن انواع بسته
$stmt_types = $pdo->query("SELECT id, name FROM package_types ORDER BY name");
$types = $stmt_types->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type_id = $_POST['type_id'] ?? '';
    $file = $_FILES['upload_file'] ?? null;

    if (empty($type_id) || !$file || $file['error'] !== UPLOAD_ERR_OK) {
        $error = 'نوع بسته و فایل الزامی است.';
    } else {
        $file_path = $file['tmp_name'];
        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if ($file_ext !== 'csv') {
            $error = 'فقط فایل CSV مجاز است. (بعداً Excel اضافه می‌کنیم)';
        } else {
            $today = date('Y-m-d');
            $inserted_count = 0;

            // باز کردن CSV
            if (($handle = fopen($file_path, 'r')) !== false) {
                // رد کردن هدر اگر وجود داره (فرض: ردیف اول هدر باشه مثل "نام گیرنده,شماره تلفن")
                fgetcsv($handle);  // رد هدر

                $stmt = $pdo->prepare("
                    INSERT INTO packages (type_id, receiver_name, receiver_phone, receive_date)
                    VALUES (?, ?, ?, ?)
                ");

                while (($data = fgetcsv($handle)) !== false) {
                    $name = trim($data[0] ?? '');
                    $phone = trim($data[1] ?? '');

                    if (!empty($name) && !empty($phone)) {
                        $stmt->execute([$type_id, $name, $phone, $today]);
                        $inserted_count++;
                    }
                }
                fclose($handle);
            }

            if ($inserted_count > 0) {
                $success = "{$inserted_count} بسته از فایل با موفقیت اضافه شد! تاریخ: " . toJalali($today);
            } else {
                $error = 'هیچ داده معتبری در فایل پیدا نشد.';
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
    <title>آپلود فایل بسته‌ها</title>
    <!-- Bootstrap CSS محلی -->
<link href="/post_daftar/assets/css/bootstrap.min.css" rel="stylesheet">

<!-- فونت Vazirmatn محلی (که خودت اضافه کردی) -->
<link href="/post_daftar/assets/css/vazirmatn.css" rel="stylesheet">

<!-- استایل سفارشی سایت -->
<link rel="stylesheet" href="/post_daftar/assets/css/style.css">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="card shadow">
        <div class="card-header bg-success text-white">
            <h4 class="mb-0">آپلود فایل CSV برای اضافه کردن بسته‌ها</h4>
        </div>
        <div class="card-body">

            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label class="form-label">نوع بسته (مشترک برای همه بسته‌ها در فایل)</label>
                    <select name="type_id" class="form-select" required>
                        <option value="">انتخاب کنید...</option>
                        <?php foreach ($types as $type): ?>
                            <option value="<?= $type['id'] ?>"><?= htmlspecialchars($type['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">فایل CSV (ساختار: نام گیرنده,شماره تلفن – ردیف اول هدر باشه)</label>
                    <input type="file" name="upload_file" class="form-control" accept=".csv" required>
                    <small class="form-text text-muted">حداکثر اندازه: ۱۰ مگ (اگر بزرگ‌تر بود، در XAMPP تنظیم کن)</small>
                </div>

                <button type="submit" class="btn btn-primary w-100">آپلود و ذخیره در دیتابیس</button>
            </form>

            <div class="mt-4 text-center">
                <a href="dashboard.php" class="btn btn-secondary">بازگشت به داشبورد</a>
            </div>
        </div>
    </div>
</div>

</body>
</html>
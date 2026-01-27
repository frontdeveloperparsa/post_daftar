<?php
require_once '../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'employee') {
    header('Location: ../login.php');
    exit;
}

$error = '';
$success = '';

// گرفتن لیست فعلی انواع برای نمایش
$stmt = $pdo->query("SELECT id, name FROM package_types ORDER BY name ASC");
$types = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_type = trim($_POST['new_type'] ?? '');

    if (empty($new_type)) {
        $error = 'نام نوع بسته را وارد کنید.';
    } else {
        // چک کنیم تکراری نباشه
        $check = $pdo->prepare("SELECT COUNT(*) FROM package_types WHERE name = ?");
        $check->execute([$new_type]);
        if ($check->fetchColumn() > 0) {
            $error = 'این نوع بسته قبلاً وجود دارد.';
        } else {
            $insert = $pdo->prepare("INSERT INTO package_types (name) VALUES (?)");
            $insert->execute([$new_type]);
            $success = "نوع بسته «{$new_type}» با موفقیت اضافه شد.";

            // بروزرسانی لیست بعد از اضافه کردن
            $stmt = $pdo->query("SELECT id, name FROM package_types ORDER BY name ASC");
            $types = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>اضافه کردن نوع بسته جدید</title>
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
        <div class="card-header bg-info text-white">
            <h4 class="mb-0">مدیریت انواع بسته‌ها</h4>
        </div>
        <div class="card-body">

            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <form method="POST" class="mb-5">
                <div class="input-group mb-3">
                    <input type="text" name="new_type" class="form-control" placeholder="نام نوع جدید (مثلاً کارت بانکی)" required>
                    <button type="submit" class="btn btn-primary">اضافه کردن</button>
                </div>
            </form>

            <h5 class="mt-4">انواع بسته‌های موجود:</h5>
            <?php if (empty($types)): ?>
                <p class="text-muted">هنوز هیچ نوعی ثبت نشده است.</p>
            <?php else: ?>
                <ul class="list-group">
                    <?php foreach ($types as $type): ?>
                        <li class="list-group-item"><?= htmlspecialchars($type['name']) ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <div class="mt-4 text-center">
                <a href="dashboard.php" class="btn btn-secondary">بازگشت به داشبورد</a>
                <a href="register_package.php" class="btn btn-success ms-2">ثبت بسته جدید</a>
            </div>
        </div>
    </div>
</div>
<script src="../assets/js/main.js"></script>
</body>
</html>
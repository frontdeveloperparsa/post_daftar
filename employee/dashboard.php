<?php
require_once '../includes/config.php';

// چک کردن لاگین و نقش
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'employee') {
    header('Location: ../login.php');
    exit;
}

$username = $_SESSION['username'] ?? 'کارمند';

// تعداد بسته‌های امروز (اختیاری - برای داشبورد جذاب‌تر)
$today = date('Y-m-d');
$stmt = $pdo->prepare("SELECT COUNT(*) AS count FROM packages WHERE DATE(created_at) = ?");
$stmt->execute([$today]);
$today_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>پنل کارمند - <?= htmlspecialchars($username) ?></title>
    <!-- Bootstrap CSS محلی -->
<link href="/post_daftar/assets/css/bootstrap.min.css" rel="stylesheet">

<!-- فونت Vazirmatn محلی (که خودت اضافه کردی) -->
<link href="/post_daftar/assets/css/vazirmatn.css" rel="stylesheet">

<!-- استایل سفارشی سایت -->
<link rel="stylesheet" href="/post_daftar/assets/css/style.css">
    <style>
        body { font-family: Tahoma, sans-serif; background: #f8f9fa; }
        .sidebar { min-height: 100vh; background: #343a40; color: white; }
        .sidebar a { color: white; }
        .sidebar a:hover { background: #495057; }
    </style>
</head>
<body>

<div class="d-flex">
    <!-- سایدبار -->
    <div class="sidebar col-2 p-3">
        <h4 class="text-center mb-4">پنل کارمند</h4>
        <hr>
        <ul class="nav flex-column">
            <li class="nav-item"><a href="dashboard.php" class="nav-link active">داشبورد</a></li>
            <li class="nav-item"><a href="register_package.php" class="nav-link">ثبت بسته جدید</a></li>
            <li class="nav-item"><a href="upload_packages.php" class="nav-link">آپلود فایل بسته‌ها</a></li>
            <li class="nav-item"><a href="add_type.php" class="nav-link">مدیریت انواع بسته</a></li>
            <li class="nav-item"><a href="search.php" class="nav-link">جستجو بسته‌ها</a></li>
            <li class="nav-item"><a href="../logout.php" class="nav-link text-danger mt-5">خروج</a></li>
            
        </ul>
    </div>

    <!-- محتوای اصلی -->
    <div class="col-10 p-4">
        <h2>خوش آمدید، <?= htmlspecialchars($username) ?> 👋</h2>
        <p class="text-muted">اینجا می‌توانید بسته‌های جدید را ثبت کنید یا انواع بسته اضافه نمایید.</p>

        <div class="card mt-4">
            <div class="card-body">
                <h5>آمار سریع</h5>
                <p>تعداد بسته‌های ثبت‌شده امروز: <strong><?= $today_count ?></strong></p>
            </div>
        </div>

        <div class="mt-4">
            <a href="register_package.php" class="btn btn-success btn-lg">ثبت بسته جدید</a>
        </div>
    </div>
</div>


</body>
</html>
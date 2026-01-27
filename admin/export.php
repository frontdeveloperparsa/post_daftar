<?php
require_once '../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// گرفتن انواع بسته برای select
$stmt_types = $pdo->query("SELECT id, name FROM package_types ORDER BY name");
$types = $stmt_types->fetchAll(PDO::FETCH_ASSOC);

// اگر درخواست دانلود باشه
if (isset($_GET['download'])) {
    $type_id = $_GET['type_id'] ?? '';
    $start_date = $_GET['start_date'] ?? '';  // شمسی
    $end_date = $_GET['end_date'] ?? '';      // شمسی
    $search_term = trim($_GET['search_term'] ?? '');  // نام یا شماره فرد خاص

    $query = "SELECT pt.name AS type_name, p.receiver_name, p.receiver_phone, p.receive_date 
              FROM packages p 
              JOIN package_types pt ON p.type_id = pt.id 
              WHERE 1=1";

    $params = [];

    if (!empty($type_id)) {
        $query .= " AND p.type_id = ?";
        $params[] = $type_id;
    }

    if (!empty($start_date)) {
        $start_greg = toGregorian($start_date);
        $query .= " AND p.receive_date >= ?";
        $params[] = $start_greg;
    }

    if (!empty($end_date)) {
        $end_greg = toGregorian($end_date);
        $query .= " AND p.receive_date <= ?";
        $params[] = $end_greg;
    }

    if (!empty($search_term)) {
        $like_term = '%' . $search_term . '%';
        $query .= " AND (p.receiver_name LIKE ? OR p.receiver_phone LIKE ?)";
        $params[] = $like_term;
        $params[] = $like_term;
    }

    $query .= " ORDER BY p.receive_date DESC";

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // تولید CSV
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="packages_' . date('Y-m-d') . '.csv"');

    $output = fopen('php://output', 'w');
    fputs($output, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM برای فارسی

    fputcsv($output, ['نوع بسته', 'نام گیرنده', 'شماره تلفن', 'تاریخ رسیدن (شمسی)'], ',', '"');

    foreach ($rows as $row) {
        fputcsv($output, [
            $row['type_name'],
            $row['receiver_name'],
            $row['receiver_phone'],
            toJalali($row['receive_date'])
        ], ',', '"');
    }

    fclose($output);
    exit;
}
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>خروجی گرفتن پیشرفته از بسته‌ها</title>
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
        <div class="card-header bg-warning text-dark">
            <h4 class="mb-0">خروجی گرفتن از بسته‌ها (CSV)</h4>
        </div>
        <div class="card-body">
            <form method="GET" class="row g-3">
                <input type="hidden" name="download" value="1">

                <div class="col-md-6">
                    <label class="form-label">نوع بسته</label>
                    <select name="type_id" class="form-select">
                        <option value="">همه انواع</option>
                        <?php foreach ($types as $type): ?>
                            <option value="<?= $type['id'] ?>"><?= htmlspecialchars($type['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">نام یا شماره فرد خاص</label>
                    <input type="text" name="search_term" class="form-control" placeholder="اختیاری...">
                </div>

                <div class="col-md-6">
                    <label class="form-label">تاریخ شروع (شمسی مثل ۱۴۰۴/۰۱/۰۱)</label>
                    <input type="text" name="start_date" class="form-control" placeholder="اختیاری...">
                </div>

                <div class="col-md-6">
                    <label class="form-label">تاریخ پایان (شمسی مثل ۱۴۰۴/۱۲/۲۹)</label>
                    <input type="text" name="end_date" class="form-control" placeholder="اختیاری...">
                </div>

                <div class="col-12 text-center mt-3">
                    <button type="submit" class="btn btn-primary btn-lg">دانلود CSV</button>
                </div>
            </form>

            <div class="mt-4 text-center">
                <a href="dashboard.php" class="btn btn-outline-secondary">بازگشت به داشبورد</a>
            </div>
        </div>
    </div>
</div>

</body>
</html>
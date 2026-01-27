<?php
require_once '../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'employee') {
    header('Location: ../login.php');
    exit;
}

$search_results = [];
$search_term = $_GET['search'] ?? '';
$type_id = $_GET['type_id'] ?? '';
$status = $_GET['status'] ?? 'رسیده'; // پیش‌فرض رسیده

$stmt_types = $pdo->query("SELECT id, name FROM package_types ORDER BY name");
$types = $stmt_types->fetchAll(PDO::FETCH_ASSOC);

if (!empty($search_term) || !empty($type_id)) {
    $query = "SELECT p.id, pt.name AS type_name, p.receiver_name, p.receiver_phone, p.receive_date, p.status, p.receipt_image, p.delivery_note
              FROM packages p
              JOIN package_types pt ON p.type_id = pt.id
              WHERE p.status = ?";

    $params = [$status];

    if (!empty($search_term)) {
        $like_term = '%' . $search_term . '%';
        $query .= " AND (p.receiver_name LIKE ? OR p.receiver_phone LIKE ?)";
        $params[] = $like_term;
        $params[] = $like_term;
    }

    if (!empty($type_id)) {
        $query .= " AND p.type_id = ?";
        $params[] = $type_id;
    }

    $query .= " ORDER BY p.receive_date DESC LIMIT 50";

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $search_results = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>جستجو بین بسته‌ها</title>
    <link href="/post_daftar/assets/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/post_daftar/assets/css/style.css">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="card shadow">
        <div class="card-header bg-secondary text-white">
            <h4 class="mb-0">جستجو بسته‌ها</h4>
        </div>
        <div class="card-body">
            <form method="GET" class="row g-3 mb-4">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="نام یا شماره تلفن..." value="<?= htmlspecialchars($search_term) ?>">
                </div>
                <div class="col-md-3">
                    <select name="type_id" class="form-select">
                        <option value="">همه انواع</option>
                        <?php foreach ($types as $type): ?>
                            <option value="<?= $type['id'] ?>" <?= $type_id == $type['id'] ? 'selected' : '' ?>><?= htmlspecialchars($type['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="رسیده" <?= $status == 'رسیده' ? 'selected' : '' ?>>فقط رسیده</option>
                        <option value="تحویل شده" <?= $status == 'تحویل شده' ? 'selected' : '' ?>>تحویل شده</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">جستجو</button>
                </div>
            </form>

            <?php if (empty($search_results)): ?>
                <div class="alert alert-info">هیچ نتیجه‌ای پیدا نشد.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>نوع بسته</th>
                                <th>نام گیرنده</th>
                                <th>شماره تلفن</th>
                                <th>تاریخ رسیدن</th>
                                <?php if ($status == 'تحویل شده'): ?>
                                    <th>مدرک تحویل</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($search_results as $row): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['type_name']) ?></td>
                                    <td><?= htmlspecialchars($row['receiver_name']) ?></td>
                                    <td><?= htmlspecialchars($row['receiver_phone']) ?></td>
                                    <td><?= toJalali($row['receive_date']) ?></td>
                                    <?php if ($status == 'تحویل شده'): ?>
                                        <td>
                                            <?php if (!empty($row['receipt_image'])): ?>
                                                <a href="/post_daftar/<?= htmlspecialchars($row['receipt_image']) ?>" target="_blank" class="btn btn-primary btn-sm me-1">دیدن رسید</a>
                                                <a href="/post_daftar/<?= htmlspecialchars($row['receipt_image']) ?>" download class="btn btn-outline-primary btn-sm">دانلود</a>
                                            <?php elseif (!empty($row['delivery_note'])): ?>
                                                <div class="text-info small">
                                                    <strong>تحویل دستی:</strong><br>
                                                    <?= nl2br(htmlspecialchars($row['delivery_note'])) ?>
                                                </div>
                                            <?php else: ?>
                                                بدون مدرک
                                            <?php endif; ?>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

            <div class="mt-4 text-center">
                <a href="dashboard.php" class="btn btn-secondary">بازگشت به داشبورد</a>
            </div>
        </div>
    </div>
</div>
</body>
</html>
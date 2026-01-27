<?php
require_once '../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$error = '';
$success = '';

// عملیات برگشت به رسیده
if (isset($_POST['revert_package'])) {
    $package_id = $_POST['package_id'] ?? 0;
    if ($package_id > 0) {
        $stmt = $pdo->prepare("UPDATE packages SET status = 'رسیده', receipt_image = NULL, delivery_note = NULL WHERE id = ?");
        $stmt->execute([$package_id]);
        $success = 'بسته به حالت رسیده بازگشت و مدرک حذف شد.';
    }
}

// لیست تحویل شده‌ها
$packages = $pdo->query("SELECT p.id, pt.name AS type_name, p.receiver_name, p.receiver_phone, p.receive_date, p.receipt_image, p.delivery_note
                         FROM packages p
                         JOIN package_types pt ON p.type_id = pt.id
                         WHERE p.status = 'تحویل شده'
                         ORDER BY p.receive_date DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>بسته‌های تحویل شده</title>
    <link href="/post_daftar/assets/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/post_daftar/assets/css/style.css">
</head>
<body class="bg-light">

<div class="container mt-5">
    <h2 class="text-center mb-4">بسته‌های تحویل شده</h2>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead class="table-dark">
                <tr>
                    <th>نوع بسته</th>
                    <th>نام گیرنده</th>
                    <th>شماره تلفن</th>
                    <th>تاریخ رسیدن</th>
                    <th>رسید تحویل</th>
                    <th>عملیات</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($packages)): ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">هیچ بسته تحویل شده‌ای پیدا نشد.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($packages as $p): ?>
                        <tr>
                            <td><?= htmlspecialchars($p['type_name'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($p['receiver_name'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($p['receiver_phone'] ?? '-') ?></td>
                            <td><?= toJalali($p['receive_date'] ?? '') ?></td>
                            <td>
                                <?php if (!empty($p['receipt_image'])): ?>
                                    <a href="<?= htmlspecialchars($p['receipt_image']) ?>" target="_blank" class="btn btn-primary btn-sm me-1">دیدن رسید</a>
                                    <a href="<?= htmlspecialchars($p['receipt_image']) ?>" download class="btn btn-outline-primary btn-sm">دانلود</a>
                                    <br><small><?= htmlspecialchars(basename($p['receipt_image'])) ?></small>
                                <?php elseif (!empty($p['delivery_note'])): ?>
                                    <div class="text-info small">
                                        <strong>تحویل دستی:</strong><br>
                                        <?= nl2br(htmlspecialchars($p['delivery_note'])) ?>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted">بدون مدرک</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="package_id" value="<?= $p['id'] ?>">
                                    <button type="submit" name="revert_package" class="btn btn-warning btn-sm" onclick="return confirm('مطمئن هستید بسته به حالت رسیده برگرده؟ تمام مدارک حذف می‌شود.')">
                                        برگشت به رسیده
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="text-center mt-4">
        <a href="dashboard.php" class="btn btn-secondary">بازگشت به داشبورد</a>
    </div>
</div>

</body>
</html>
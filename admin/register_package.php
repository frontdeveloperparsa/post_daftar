<?php
require_once '../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$error = '';
$success = '';

// گرفتن انواع بسته برای select
$stmt_types = $pdo->query("SELECT id, name FROM package_types ORDER BY name");
$types = $stmt_types->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type_id = $_POST['type_id'] ?? '';
    $receivers = $_POST['receivers'] ?? [];  // آرایه نام و شماره‌ها

    if (empty($type_id) || empty($receivers) || !is_numeric($type_id)) {
        $error = 'نوع بسته و حداقل یک گیرنده الزامی است.';
    } else {
        $today = date('Y-m-d');
        $inserted_count = 0;

        // حلقه برای ثبت همه بسته‌ها
        $stmt = $pdo->prepare("
            INSERT INTO packages (type_id, receiver_name, receiver_phone, receive_date)
            VALUES (?, ?, ?, ?)
        ");

        foreach ($receivers as $receiver) {
            $name = trim($receiver['name'] ?? '');
            $phone = trim($receiver['phone'] ?? '');

            if (!empty($name) && !empty($phone)) {
                $stmt->execute([$type_id, $name, $phone, $today]);
                $inserted_count++;
            }
        }

        if ($inserted_count > 0) {
            $success = "{$inserted_count} بسته با موفقیت ثبت شد! تاریخ: " . toJalali($today);
        } else {
            $error = 'هیچ بسته معتبر ثبت نشد.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ثبت چندین بسته جدید</title>
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
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">ثبت چندین بسته جدید</h4>
        </div>
        <div class="card-body">

            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <form method="POST" id="multi-register-form">
                <div class="mb-4">
                    <label class="form-label">نوع بسته (مشترک برای همه)</label>
                    <select name="type_id" class="form-select" required>
                        <option value="">انتخاب کنید...</option>
                        <?php foreach ($types as $type): ?>
                            <option value="<?= $type['id'] ?>"><?= htmlspecialchars($type['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div id="receivers-list">
                    <!-- ردیف اولیه -->
                    <div class="row mb-3 receiver-row">
                        <div class="col-md-5">
                            <input type="text" name="receivers[0][name]" class="form-control" placeholder="نام گیرنده" required>
                        </div>
                        <div class="col-md-5">
                            <input type="text" name="receivers[0][phone]" class="form-control" placeholder="شماره تلفن (0912xxxxxxx)" required>
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-danger remove-row">حذف</button>
                        </div>
                    </div>
                </div>

                <button type="button" id="add-receiver" class="btn btn-secondary mb-4">اضافه کردن گیرنده جدید</button>

                <button type="submit" class="btn btn-primary w-100">ثبت همه بسته‌ها</button>
            </form>

            <div class="mt-4 text-center">
                <a href="dashboard.php" class="btn btn-secondary">بازگشت به داشبورد</a>
            </div>
        </div>
    </div>
</div>

<script>
let rowIndex = 1;  // شروع از 1 چون ردیف 0 اولیه است

document.getElementById('add-receiver').addEventListener('click', function() {
    const newRow = `
        <div class="row mb-3 receiver-row">
            <div class="col-md-5">
                <input type="text" name="receivers[${rowIndex}][name]" class="form-control" placeholder="نام گیرنده" required>
            </div>
            <div class="col-md-5">
                <input type="text" name="receivers[${rowIndex}][phone]" class="form-control" placeholder="شماره تلفن (0912xxxxxxx)" required>
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-danger remove-row">حذف</button>
            </div>
        </div>
    `;
    document.getElementById('receivers-list').insertAdjacentHTML('beforeend', newRow);
    rowIndex++;
});

// حذف ردیف
document.getElementById('receivers-list').addEventListener('click', function(e) {
    if (e.target.classList.contains('remove-row')) {
        const row = e.target.closest('.receiver-row');
        if (document.querySelectorAll('.receiver-row').length > 1) {
            row.remove();
        }
    }
});
</script>

</body>
</html>
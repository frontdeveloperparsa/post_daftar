<?php
require_once '../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$error = '';
$success = '';

// گرفتن انواع بسته
$stmt_types = $pdo->query("SELECT id, name FROM package_types ORDER BY name");
$types = $stmt_types->fetchAll(PDO::FETCH_ASSOC);

// تنظیمات pagination
$per_page = 50;
$current_page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($current_page - 1) * $per_page;

// فیلترها
$filter_type = $_GET['filter_type'] ?? '';
$filter_person = $_GET['filter_person'] ?? '';
$filter_date = $_GET['filter_date'] ?? '';

// query برای رسیده‌ها
$query = "SELECT p.id, pt.name AS type_name, p.receiver_name, p.receiver_phone, p.receive_date, p.status
          FROM packages p
          JOIN package_types pt ON p.type_id = pt.id
          WHERE p.status = 'رسیده'";

$params = [];

if (!empty($filter_type)) {
    $query .= " AND p.type_id = ?";
    $params[] = $filter_type;
}

if (!empty($filter_person)) {
    $like = '%' . $filter_person . '%';
    $query .= " AND (p.receiver_name LIKE ? OR p.receiver_phone LIKE ?)";
    $params[] = $like;
    $params[] = $like;
}

if (!empty($filter_date)) {
    $query .= " AND p.receive_date = ?";
    $params[] = $filter_date;
}

// تعداد کل
$total_query = "SELECT COUNT(*) FROM packages p WHERE p.status = 'رسیده'";
if (!empty($filter_type)) $total_query .= " AND p.type_id = ?";
if (!empty($filter_person)) $total_query .= " AND (p.receiver_name LIKE ? OR p.receiver_phone LIKE ?)";
if (!empty($filter_date)) $total_query .= " AND p.receive_date = ?";

$total_stmt = $pdo->prepare($total_query);
$total_stmt->execute($params);
$total_packages = $total_stmt->fetchColumn();
$total_pages = ceil($total_packages / $per_page);

// اضافه کردن LIMIT و OFFSET مستقیم
$query .= " ORDER BY p.receive_date DESC LIMIT $per_page OFFSET $offset";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$packages = $stmt->fetchAll(PDO::FETCH_ASSOC);

// عملیات حذف تک
if (isset($_POST['delete_package'])) {
    $delete_id = $_POST['delete_id'] ?? 0;
    if ($delete_id > 0) {
        $stmt = $pdo->prepare("DELETE FROM packages WHERE id = ?");
        $stmt->execute([$delete_id]);
        $success = 'بسته حذف شد.';
    }
}

// عملیات حذف گروهی - اصلاح‌شده
if (isset($_POST['delete_group'])) {
    $delete_type = $_POST['delete_type'] ?? '';
    $delete_person = $_POST['delete_person'] ?? '';
    $delete_date = $_POST['delete_date'] ?? '';

    // اگر هیچ فیلتری انتخاب نشده، حذف نکن
    if (empty($delete_type) && empty($delete_person) && empty($delete_date)) {
        $error = 'حداقل یک فیلتر انتخاب کنید تا حذف انجام شود.';
    } else {
        $query_delete = "DELETE FROM packages WHERE status = 'رسیده'";
        $params_delete = [];

        if (!empty($delete_type)) {
            $query_delete .= " AND type_id = ?";
            $params_delete[] = $delete_type;
        }

        if (!empty($delete_person)) {
            $like = '%' . $delete_person . '%';
            $query_delete .= " AND (receiver_name LIKE ? OR receiver_phone LIKE ?)";
            $params_delete[] = $like;
            $params_delete[] = $like;
        }

        if (!empty($delete_date)) {
            $query_delete .= " AND receive_date = ?";
            $params_delete[] = $delete_date;
        }

        // برای اطمینان، همیشه شرط status = 'رسیده' رو نگه می‌داریم
        $stmt = $pdo->prepare($query_delete);
        $stmt->execute($params_delete);
        $deleted = $stmt->rowCount();

        if ($deleted > 0) {
            $success = "$deleted بسته حذف شد.";
        } else {
            $error = 'هیچ بسته‌ای با این فیلترها پیدا نشد.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>حذف بسته‌ها - پنل مدیر</title>
    <link href="/post_daftar/assets/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/post_daftar/assets/css/style.css">
</head>
<body>

<div class="container mt-5">
    <h2 class="text-center mb-4">حذف بسته‌ها</h2>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <!-- فرم حذف گروهی -->
    <form method="POST" class="mb-4">
        <div class="row g-3">
            <div class="col-md-4">
                <select name="delete_type" class="form-select">
                    <option value="">نوع بسته برای حذف</option>
                    <?php foreach ($types as $type): ?>
                        <option value="<?= $type['id'] ?>"><?= htmlspecialchars($type['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <input type="text" name="delete_person" class="form-control" placeholder="نام یا شماره شخص برای حذف">
            </div>
            <div class="col-md-4">
                <input type="date" name="delete_date" class="form-control">
            </div>
            <div class="col-12">
                <button type="submit" name="delete_group" class="btn btn-danger w-100" onclick="return confirm('مطمئن هستید تمام بسته‌های فیلتر شده حذف شوند؟')">حذف گروهی</button>
            </div>
        </div>
    </form>

    <!-- فیلتر برای لیست -->
    <form method="GET" class="mb-4">
        <div class="row g-3">
            <div class="col-md-3">
                <select name="filter_type" class="form-select">
                    <option value="">نوع بسته</option>
                    <?php foreach ($types as $type): ?>
                        <option value="<?= $type['id'] ?>" <?= $filter_type == $type['id'] ? 'selected' : '' ?>><?= htmlspecialchars($type['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <input type="text" name="filter_person" class="form-control" placeholder="نام یا شماره شخص" value="<?= htmlspecialchars($filter_person) ?>">
            </div>
            <div class="col-md-3">
                <input type="date" name="filter_date" class="form-control" value="<?= htmlspecialchars($filter_date) ?>">
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary w-100">فیلتر لیست</button>
            </div>
        </div>
    </form>

    <!-- لیست رسیده‌ها برای حذف تک -->
    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th>نوع</th>
                    <th>نام گیرنده</th>
                    <th>شماره</th>
                    <th>تاریخ رسیدن</th>
                    <th>عملیات حذف</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($packages)): ?>
                    <tr>
                        <td colspan="5" class="text-center">هیچ بسته‌ای پیدا نشد.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($packages as $package): ?>
                        <tr>
                            <td><?= htmlspecialchars($package['type_name']) ?></td>
                            <td><?= htmlspecialchars($package['receiver_name']) ?></td>
                            <td><?= htmlspecialchars($package['receiver_phone']) ?></td>
                            <td><?= toJalali($package['receive_date']) ?></td>
                            <td>
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="delete_id" value="<?= $package['id'] ?>">
                                    <button type="submit" name="delete_package" class="btn btn-danger btn-sm" onclick="return confirm('مطمئن هستید این بسته حذف شود؟')">حذف</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- pagination -->
    <?php if ($total_pages > 1): ?>
        <nav aria-label="Pagination">
            <ul class="pagination justify-content-center mt-4">
                <?php if ($current_page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $current_page - 1 ?>&filter_type=<?= $filter_type ?>&filter_person=<?= $filter_person ?>&filter_date=<?= $filter_date ?>">قبلی</a>
                    </li>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?= $i == $current_page ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>&filter_type=<?= $filter_type ?>&filter_person=<?= $filter_person ?>&filter_date=<?= $filter_date ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>

                <?php if ($current_page < $total_pages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $current_page + 1 ?>&filter_type=<?= $filter_type ?>&filter_person=<?= $filter_person ?>&filter_date=<?= $filter_date ?>">بعدی</a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    <?php endif; ?>

    <a href="dashboard.php" class="btn btn-secondary mt-3">بازگشت به داشبورد</a>
</div>

</body>
</html>
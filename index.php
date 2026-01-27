<?php
require_once 'includes/config.php';

// عنوان صفحه
$page_title = 'استعلام وضعیت بسته پستی - دفتر پیشخوان';
include 'includes/header.php';

$search_results = [];
$search_term = '';
$message = '';
$status = $_GET['status'] ?? 'رسیده'; // پیش‌فرض رسیده

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['search'])) {
    $search_term = trim($_GET['search'] ?? '');

    if (!empty($search_term)) {
        $stmt = $pdo->prepare("
            SELECT p.id, pt.name AS type_name, p.receiver_name, p.receiver_phone, p.receive_date, p.status, p.receipt_image, p.delivery_note
            FROM packages p
            JOIN package_types pt ON p.type_id = pt.id
            WHERE p.status = ?
              AND (p.receiver_name LIKE ? OR p.receiver_phone LIKE ?)
            ORDER BY p.receive_date DESC
            LIMIT 10
        ");
        $like_term = '%' . $search_term . '%';
        $stmt->execute([$status, $like_term, $like_term]);
        $search_results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($search_results)) {
            $message = 'هیچ بسته‌ای با این اطلاعات پیدا نشد.';
        }
    } else {
        $message = 'لطفاً نام یا شماره تلفن را وارد کنید.';
    }
}
?>

<div class="row justify-content-center">
    <div class="col-lg-10 col-xl-8">
        <div class="text-center mb-5 py-5 bg-primary text-white rounded-4 shadow">
            <h1 class="display-5 fw-bold mb-3">استعلام وضعیت بسته پستی</h1>
            <p class="lead mb-4">نام گیرنده یا شماره تلفن خود را وارد کنید تا وضعیت بسته را ببینید</p>
        </div>

        <div class="card shadow border-0">
            <div class="card-body p-4 p-md-5">
                <form method="GET" class="d-flex flex-column flex-md-row gap-3 justify-content-center">
                    <input
                        type="text"
                        name="search"
                        class="form-control form-control-lg"
                        placeholder="نام گیرنده یا شماره تلفن..."
                        value="<?= htmlspecialchars($search_term) ?>"
                        required
                        autofocus
                    >
                    <select name="status" class="form-select form-control-lg">
                        <option value="رسیده" <?= $status == 'رسیده' ? 'selected' : '' ?>>فقط رسیده</option>
                        <option value="تحویل شده" <?= $status == 'تحویل شده' ? 'selected' : '' ?>>تحویل شده</option>
                    </select>
                    <button type="submit" class="btn btn-primary btn-lg px-5">
                        جستجو
                    </button>
                </form>

                <?php if ($message): ?>
                    <div class="alert alert-info mt-4 text-center">
                        <?= htmlspecialchars($message) ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($search_results)): ?>
                    <div class="table-responsive mt-5">
                        <table class="table table-bordered table-hover">
                            <thead class="table-primary">
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
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
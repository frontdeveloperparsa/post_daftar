<?php
require_once '../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$error = '';
$success = '';

// مسیر دقیق ذخیره رسیدها (public_html/post_daftar/assets/uploads/receipts/)
$upload_base = $_SERVER['DOCUMENT_ROOT'] . '/post_daftar/assets/uploads/receipts/';
$upload_web = '/post_daftar/assets/uploads/receipts/';

if (!is_dir($upload_base)) {
    mkdir($upload_base, 0755, true);
}

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

// اضافه کردن LIMIT و OFFSET
$query .= " ORDER BY p.receive_date DESC LIMIT $per_page OFFSET $offset";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$packages = $stmt->fetchAll(PDO::FETCH_ASSOC);

// عملیات تحویل با عکس
if (isset($_POST['deliver_with_photo'])) {
    $deliver_ids = explode(',', $_POST['deliver_ids'] ?? '');
    if (!empty($deliver_ids) && isset($_FILES['receipt_image']) && $_FILES['receipt_image']['error'] == 0) {
        $file = $_FILES['receipt_image'];
        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($file_ext, ['jpg', 'jpeg', 'png'])) {
            $error = 'فقط jpg, jpeg, png مجاز است.';
        } else {
            $uploaded = false;
            foreach ($deliver_ids as $id) {
                $id = trim($id);
                if (empty($id)) continue;
                $file_name = 'receipt_' . $id . '_' . time() . '.' . $file_ext;
                $full_path = $upload_base . $file_name;
                $db_path = $upload_web . $file_name;
                if (move_uploaded_file($file['tmp_name'], $full_path)) {
                    $stmt = $pdo->prepare("UPDATE packages SET status = 'تحویل شده', receipt_image = ? WHERE id = ?");
                    $stmt->execute([$db_path, $id]);
                    $uploaded = true;
                }
            }
            if ($uploaded) {
                $success = 'بسته(ها) با عکس تحویل شد.';
            } else {
                $error = 'خطا در آپلود عکس.';
            }
        }
    } else {
        $error = 'عکس الزامی است.';
    }
}

// عملیات تحویل با امضا
if (isset($_POST['deliver_with_signature'])) {
    $deliver_ids = explode(',', $_POST['deliver_ids'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $family = trim($_POST['family'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $signature_data = $_POST['signature_data'] ?? '';

    if (!empty($deliver_ids) && !empty($signature_data) && !empty($name) && !empty($family) && !empty($phone)) {
        $note = "نام: $name | فامیل: $family | شماره: $phone";
        $uploaded = false;
        foreach ($deliver_ids as $id) {
            $id = trim($id);
            if (empty($id)) continue;
            // نام فایل با اطلاعات گیرنده
            $file_name = 'signature_' . $id . '_' . str_replace(' ', '_', $name) . '_' . str_replace(' ', '_', $family) . '_' . $phone . '_' . time() . '.png';
            $full_path = $upload_base . $file_name;
            $db_path = $upload_web . $file_name;

            $signature_data_clean = str_replace('data:image/png;base64,', '', $signature_data);
            $signature_data_clean = str_replace(' ', '+', $signature_data_clean);
            $signature_file = base64_decode($signature_data_clean);

            if (file_put_contents($full_path, $signature_file)) {
                $stmt = $pdo->prepare("UPDATE packages SET status = 'تحویل شده', receipt_image = ?, delivery_note = ? WHERE id = ?");
                $stmt->execute([$db_path, $note, $id]);
                $uploaded = true;
            }
        }
        if ($uploaded) {
            $success = 'بسته(ها) با امضا تحویل شد.';
        } else {
            $error = 'خطا در ذخیره امضا.';
        }
    } else {
        $error = 'اطلاعات و امضا الزامی است.';
    }
}

// رفرش صفحه بعد از موفقیت یا خطا
if (!empty($success)) {
    header("Location: deliver_packages.php?success=" . urlencode($success));
    exit;
}
if (!empty($error)) {
    header("Location: deliver_packages.php?error=" . urlencode($error));
    exit;
}
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تحویل بسته‌ها</title>
    <link href="/post_daftar/assets/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/post_daftar/assets/css/style.css">
    <script src="/post_daftar/assets/js/bootstrap.bundle.min.js"></script>
</head>
<body class="bg-light">

<div class="container mt-5">
    <h2 class="text-center mb-4">تحویل بسته‌ها</h2>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_GET['success']) ?></div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_GET['error']) ?></div>
    <?php endif; ?>

    <!-- فیلتر -->
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
                <button type="submit" class="btn btn-primary w-100">فیلتر</button>
            </div>
        </div>
    </form>

    <!-- لیست با checkbox -->
    <form method="POST" id="deliverForm">
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="selectAll"></th>
                        <th>نوع</th>
                        <th>نام گیرنده</th>
                        <th>شماره</th>
                        <th>تاریخ رسیدن</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($packages)): ?>
                        <tr>
                            <td colspan="5" class="text-center">هیچ بسته رسیده‌ای پیدا نشد.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($packages as $package): ?>
                            <tr>
                                <td>
                                    <input type="checkbox" name="deliver_ids[]" value="<?= $package['id'] ?>" class="form-check-input">
                                </td>
                                <td><?= htmlspecialchars($package['type_name']) ?></td>
                                <td><?= htmlspecialchars($package['receiver_name']) ?></td>
                                <td><?= htmlspecialchars($package['receiver_phone']) ?></td>
                                <td><?= toJalali($package['receive_date']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- دکمه شناور پایین وسط -->
        <button type="button" id="deliverSelected" class="fab d-none" data-bs-toggle="modal" data-bs-target="#deliverTypeModal">
            تحویل انتخابی‌ها
        </button>
    </form>

    <!-- Modal انتخاب نوع تحویل -->
    <div class="modal fade" id="deliverTypeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">نوع تحویل</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <button type="button" class="btn btn-success w-100 mb-3" data-bs-toggle="modal" data-bs-target="#photoModal" data-bs-dismiss="modal">با عکس</button>
                    <button type="button" class="btn btn-info w-100" data-bs-toggle="modal" data-bs-target="#signatureInfoModal" data-bs-dismiss="modal">با امضا</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal عکس -->
    <div class="modal fade" id="photoModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">تحویل با عکس</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" enctype="multipart/form-data" id="photoForm">
                    <div class="modal-body">
                        <input type="hidden" name="deliver_ids" value="" id="deliverIdsPhoto">
                        <input type="file" name="receipt_image" required capture="camera" accept="image/*" class="form-control mb-3">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">لغو</button>
                        <button type="submit" name="deliver_with_photo" class="btn btn-primary">ارسال</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal اطلاعات امضا -->
    <div class="modal fade" id="signatureInfoModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">اطلاعات گیرنده</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" id="signatureForm">
                    <div class="modal-body">
                        <input type="hidden" name="deliver_ids" value="" id="deliverIdsSignature">
                        <input type="text" name="name" class="form-control mb-3" placeholder="نام" required>
                        <input type="text" name="family" class="form-control mb-3" placeholder="فامیل" required>
                        <input type="text" name="phone" class="form-control mb-3" placeholder="شماره" required>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">لغو</button>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#signatureModal" data-bs-dismiss="modal">بعدی (امضا)</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal امضا -->
    <div class="modal fade" id="signatureModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">امضا</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <canvas id="signatureCanvas" class="border border-dark w-100" width="600" height="250" style="background:#fff; touch-action:none; cursor:crosshair;"></canvas>
                    <button type="button" class="btn btn-warning mt-2" onclick="clearCanvas()">پاک کردن</button>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">لغو</button>
                    <button type="button" class="btn btn-primary" onclick="submitSignatureForm()">ارسال</button>
                </div>
            </div>
        </div>
    </div>

    <!-- دکمه بازگشت به داشبورد -->
    <div class="text-center mt-5 mb-5">
        <a href="dashboard.php" class="btn btn-secondary btn-lg px-5 py-3">بازگشت به داشبورد</a>
    </div>
</div>

<!-- استایل دکمه شناور -->
<style>
.fab {
  position: fixed;
  bottom: 40px;
  left: 50%;
  transform: translateX(-50%);
  z-index: 1050;
  min-width: 220px;
  height: 60px;
  padding: 0 30px;
  border-radius: 50px;
  background: rgba(30, 64, 175, 0.25);
  backdrop-filter: blur(16px);
  -webkit-backdrop-filter: blur(16px);
  color: white;
  font-size: 1.15rem;
  font-weight: 600;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 10px 30px rgba(0,0,0,0.25);
  border: 1px solid rgba(255,255,255,0.3);
  transition: all 0.4s ease;
  overflow: hidden;
  cursor: pointer;
}

.fab::before {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(135deg, rgba(30, 64, 175, 0.95), rgba(96, 165, 250, 0.95));
  opacity: 0;
  transition: opacity 0.4s ease;
  z-index: -1;
}

.fab:hover::before {
  opacity: 1;
}

.fab:hover {
  transform: translateX(-50%) scale(1.05);
  box-shadow: 0 15px 40px rgba(0,0,0,0.35);
}

.fab.d-none {
  display: none;
}
</style>

<!-- JS کامل -->
<script>
// JS گروهی و دکمه شناور
document.addEventListener('DOMContentLoaded', () => {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('input[name="deliver_ids[]"]');
    const deliverBtn = document.getElementById('deliverSelected');

    if (selectAll) {
        selectAll.addEventListener('change', () => {
            checkboxes.forEach(cb => cb.checked = selectAll.checked);
            checkAndShowButton();
        });
    }

    checkboxes.forEach(cb => {
        cb.addEventListener('change', checkAndShowButton);
    });

    function checkAndShowButton() {
        const checkedCount = Array.from(checkboxes).filter(cb => cb.checked).length;
        if (checkedCount > 0) {
            deliverBtn.classList.remove('d-none');
            const ids = Array.from(checkboxes).filter(cb => cb.checked).map(cb => cb.value).join(',');
            document.getElementById('deliverIdsPhoto').value = ids;
            document.getElementById('deliverIdsSignature').value = ids;
        } else {
            deliverBtn.classList.add('d-none');
        }
    }

    checkAndShowButton();
});

// JS امضا
let isDrawing = false;
const canvas = document.getElementById('signatureCanvas');
const ctx = canvas ? canvas.getContext('2d') : null;

if (ctx) {
    function resize() {
        canvas.width = canvas.offsetWidth;
        canvas.height = 250;
        ctx.fillStyle = '#fff';
        ctx.fillRect(0, 0, canvas.width, canvas.height);
    }
    resize();
    window.addEventListener('resize', resize);

    const modal = document.getElementById('signatureModal');
    if (modal) {
        modal.addEventListener('shown.bs.modal', resize);
    }

    function start(e) {
        isDrawing = true;
        draw(e);
    }

    function end() {
        isDrawing = false;
        ctx.beginPath();
    }

    function draw(e) {
        if (!isDrawing) return;
        e.preventDefault();

        const rect = canvas.getBoundingClientRect();
        let x = (e.clientX || (e.touches && e.touches[0].clientX)) - rect.left;
        let y = (e.clientY || (e.touches && e.touches[0].clientY)) - rect.top;

        ctx.lineWidth = 5;
        ctx.lineCap = 'round';
        ctx.strokeStyle = '#000';

        ctx.lineTo(x, y);
        ctx.stroke();
        ctx.beginPath();
        ctx.moveTo(x, y);
    }

    canvas.addEventListener('mousedown', start);
    canvas.addEventListener('mousemove', draw);
    canvas.addEventListener('mouseup', end);
    canvas.addEventListener('mouseleave', end);

    canvas.addEventListener('touchstart', start);
    canvas.addEventListener('touchmove', draw);
    canvas.addEventListener('touchend', end);
    canvas.addEventListener('touchcancel', end);
}

function clearCanvas() {
    if (ctx) {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        ctx.fillStyle = '#fff';
        ctx.fillRect(0, 0, canvas.width, canvas.height);
    }
}

// تابع ارسال فرم امضا (اصلاح نهایی)
function submitSignatureForm() {
    if (canvas) {
        const dataURL = canvas.toDataURL('image/png');
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'signature_data';
        input.value = dataURL;
        document.getElementById('signatureForm').appendChild(input);
    }

    // مطمئن شو که deliver_with_signature ارسال بشه
    const submitInput = document.createElement('input');
    submitInput.type = 'hidden';
    submitInput.name = 'deliver_with_signature';
    submitInput.value = '1';
    document.getElementById('signatureForm').appendChild(submitInput);

    document.getElementById('signatureForm').submit();
}
</script>

</body>
</html>
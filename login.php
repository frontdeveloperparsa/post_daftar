<?php
require_once 'includes/config.php';

// اگر قبلاً لاگین کرده، هدایت به پنل مربوطه
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header('Location: admin/dashboard.php');
    } else {
        header('Location: employee/dashboard.php');
    }
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'نام کاربری و رمز عبور را وارد کنید';
    } else {
        $stmt = $pdo->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            if ($user['role'] === 'admin') {
                header('Location: admin/dashboard.php');
            } else {
                header('Location: employee/dashboard.php');
            }
            exit;
        } else {
            $error = 'نام کاربری یا رمز عبور اشتباه است';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ورود به سیستم - دفتر پیشخوان</title>
    <!-- Bootstrap CSS محلی -->
<link href="/post_daftar/assets/css/bootstrap.min.css" rel="stylesheet">

<!-- Bootstrap Icons محلی -->
<link rel="stylesheet" href="/post_daftar/assets/css/bootstrap-icons.min.css">

<!-- فونت Vazirmatn محلی (که خودت اضافه کردی) -->
<link href="/post_daftar/assets/css/vazirmatn.css" rel="stylesheet">

<!-- استایل سفارشی سایت -->
<link rel="stylesheet" href="/post_daftar/assets/css/style.css">

<!-- اگر AOS استفاده می‌کنی (انیمیشن اسکرول) -->
<link href="/post_daftar/assets/css/aos.css" rel="stylesheet">  <!-- اگر دانلود کردی -->
    <style>
        body { background: #f8f9fa; font-family: 'Tahoma', sans-serif; }
        .login-box { max-width: 400px; margin: 100px auto; padding: 30px; background: white; border-radius: 10px; box-shadow: 0 0 20px rgba(0,0,0,0.1); }
    </style>
</head>
<body>

<div class="container">
    <div class="login-box">
        <h3 class="text-center mb-4">ورود به پنل مدیریت پست</h3>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">نام کاربری</label>
                <input type="text" name="username" class="form-control" required autofocus>
            </div>
            <div class="mb-3">
                <label class="form-label">رمز عبور</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">ورود</button>
        </form>
    </div>
</div>

</body>
</html>
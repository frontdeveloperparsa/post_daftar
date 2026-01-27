<?php
// این فایل رو در ابتدای هر صفحه include کن (بعد از config اگر لازم بود)
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title ?? 'دفتر پیشخوان باباعلی') ?></title>

    <!-- فونت Vazirmatn محلی -->
    <!-- Bootstrap CSS محلی -->
<link href="/post_daftar/assets/css/bootstrap.min.css" rel="stylesheet">


<!-- استایل سفارشی سایت -->
<link rel="stylesheet" href="/post_daftar/assets/css/style.css">

</head>
<body>

<!-- نوار بالا (Navbar) برای صفحات عمومی -->
<?php
// این فایل رو در ابتدای هر صفحه include کن (بعد از config اگر لازم بود)
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? 'دفتر پیشخوان - بخش پست' ?></title>

    <!-- استایل سفارشی ما -->
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
 
</head>
<body>

<!-- نوار بالا (Navbar) برای صفحات عمومی -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand fw-bold" href="<?= BASE_URL ?>index.php">
            <i class="bi bi-envelope-paper me-2"></i> دفتر پیشخوان - بخش پست
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="<?= BASE_URL ?>login.php">ورود به پنل</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<main class="container my-4">

<main class="container my-4">
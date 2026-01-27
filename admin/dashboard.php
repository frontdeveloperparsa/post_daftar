<?php
require_once '../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$username = $_SESSION['username'] ?? 'مدیر';

// آمار (از دیتابیس واقعی)
$total_packages = $pdo->query("SELECT COUNT(*) FROM packages")->fetchColumn();
$total_delivered = $pdo->query("SELECT COUNT(*) FROM packages WHERE status = 'تحویل شده'")->fetchColumn();
$today_delivered = $pdo->query("SELECT COUNT(*) FROM packages WHERE status = 'تحویل شده' AND DATE(created_at) = CURDATE()")->fetchColumn();

// داده چارت کل بسته‌ها (۳۰ روز اخیر)
$chart_total = $pdo->query("
    SELECT DATE(created_at) as date, COUNT(*) as count
    FROM packages 
    WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
    GROUP BY DATE(created_at)
    ORDER BY DATE(created_at) ASC
")->fetchAll(PDO::FETCH_ASSOC);

$labels_total = $values_total = [];
foreach ($chart_total as $row) {
    $labels_total[] = toJalali($row['date']); // اگر تابع toJalali داری
    $values_total[] = $row['count'];
}

// داده چارت تحویل‌شده‌ها
$chart_delivered = $pdo->query("
    SELECT DATE(created_at) as date, COUNT(*) as count
    FROM packages 
    WHERE status = 'تحویل شده' 
    AND created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
    GROUP BY DATE(created_at)
    ORDER BY DATE(created_at) ASC
")->fetchAll(PDO::FETCH_ASSOC);

$labels_delivered = $values_delivered = [];
foreach ($chart_delivered as $row) {
    $labels_delivered[] = toJalali($row['date']);
    $values_delivered[] = $row['count'];
}
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>داشبورد مدیر - <?= htmlspecialchars($username) ?></title>

    <!-- Bootstrap RTL محلی -->
    <link href="/post_daftar/assets/css/bootstrap.rtl.min.css" rel="stylesheet">

    <!-- فونت Vazirmatn محلی -->
    <link href="/post_daftar/assets/fonts/vazirmatn.css" rel="stylesheet">

    <!-- Chart.js محلی -->
    <script src="/post_daftar/assets/js/chart.min.js"></script>

    <!-- Feather Icons محلی (اگر داری) یا CDN سبک -->
    <script src="https://unpkg.com/feather-icons"></script>

    <style>
        :root {
            --background: #f9fafb;
            --foreground: #111827;
            --card: #ffffff;
            --primary: #3b82f6;
            --primary-dark: #1d4ed8;
            --accent: #f59e0b;
            --accent-dark: #d97706;
            --muted: #6b7280;
            --border: #e5e7eb;
            --sidebar: #1e293b;
            --sidebar-foreground: #f1f5f9;
        }

        body {
            font-family: 'Vazirmatn', system-ui, sans-serif;
            background: var(--background);
            color: var(--foreground);
            margin: 0;
        }

        .sidebar {
            position: fixed;
            right: 0;
            top: 0;
            bottom: 0;
            width: 16rem;
            background: var(--sidebar);
            color: var(--sidebar-foreground);
            padding: 1.5rem 1rem;
            overflow-y: auto;
            box-shadow: -2px 0 10px rgba(0,0,0,0.1);
        }

        .sidebar-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .sidebar a {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            color: rgba(241,245,249,0.8);
            text-decoration: none;
            border-radius: 0.5rem;
            transition: all 0.2s;
        }

        .sidebar a:hover, .sidebar a.active {
            background: rgba(255,255,255,0.1);
            color: var(--accent);
        }

        main {
            margin-right: 16rem;
            padding: 2rem;
        }

        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .stat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 1rem;
            padding: 1.5rem;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
        }

        .chart-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
            gap: 1.5rem;
        }

        .chart-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 1rem;
            padding: 1.5rem;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
        }

        .icon {
            width: 20px;
            height: 20px;
            stroke: currentColor;
            stroke-width: 2;
        }
    </style>
</head>
<body>

<div class="d-flex">
    <!-- سایدبار -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <svg class="icon" viewBox="0 0 24 24"><path d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125.504 1.125 1.125v1.5c0 .621-.504 1.125-1.125 1.125H3.375c-.621 0-1.125-.504-1.125-1.125v-1.5C2.25 8.004 2.754 7.5 3.375 7.5z"/></svg>
            <span class="text-lg font-medium">سامانه بسته‌ها</span>
        </div>

        <nav class="mt-6 space-y-1">
            <a href="dashboard.php" class="active"><i data-feather="home" class="icon"></i> داشبورد</a>
            <a href="register_package.php"><i data-feather="plus-square" class="icon"></i> ثبت بسته جدید</a>
            <a href="upload_packages.php"><i data-feather="upload-cloud" class="icon"></i> آپلود فایل</a>
            <a href="add_type.php"><i data-feather="tag" class="icon"></i> مدیریت انواع</a>
            <a href="search.php"><i data-feather="search" class="icon"></i> جستجو</a>
            <a href="deliver_packages.php"><i data-feather="truck" class="icon"></i> تحویل</a>
            <a href="delete_packages.php"><i data-feather="trash-2" class="icon"></i> حذف</a>
            <a href="view_delivered.php"><i data-feather="check-circle" class="icon"></i> تحویل‌شده‌ها</a>
            <a href="export.php"><i data-feather="download" class="icon"></i> خروجی</a>
            <a href="../logout.php" class="mt-8 text-danger"><i data-feather="log-out" class="icon"></i> خروج</a>
        </nav>
    </aside>

    <!-- محتوا -->
    <main>
        <header>
            <div>
                <h1 class="text-2xl font-bold">داشبورد</h1>
                <p class="text-muted">خوش آمدید، <?= htmlspecialchars($username) ?></p>
            </div>
        </header>

        <!-- کارت‌های آمار -->
        <div class="stat-grid">
            <div class="stat-card">
                <div class="d-flex align-items-center gap-3">
                    <i data-feather="package" class="icon text-primary"></i>
                    <div>
                        <div class="text-sm text-muted">کل بسته‌ها</div>
                        <div class="text-2xl font-bold"><?= number_format($total_packages) ?></div>
                    </div>
                </div>
            </div>

            <div class="stat-card">
                <div class="d-flex align-items-center gap-3">
                    <i data-feather="truck" class="icon text-accent"></i>
                    <div>
                        <div class="text-sm text-muted">تحویل امروز</div>
                        <div class="text-2xl font-bold"><?= number_format($today_delivered) ?></div>
                    </div>
                </div>
            </div>

            <div class="stat-card">
                <div class="d-flex align-items-center gap-3">
                    <i data-feather="check-circle" class="icon text-success"></i>
                    <div>
                        <div class="text-sm text-muted">کل تحویل‌شده</div>
                        <div class="text-2xl font-bold"><?= number_format($total_delivered) ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- چارت‌ها -->
        <div class="chart-grid">
            <div class="chart-card">
                <h6 class="mb-3">روند کل بسته‌ها (۳۰ روز)</h6>
                <canvas id="totalChart" class="chart"></canvas>
            </div>

            <div class="chart-card">
                <h6 class="mb-3">تحویل‌شده‌ها (۳۰ روز)</h6>
                <canvas id="deliveredChart" class="chart"></canvas>
            </div>
        </div>
    </main>
</div>

<script>
feather.replace(); // فعال کردن آیکون‌های Feather

// چارت کل بسته‌ها
new Chart(document.getElementById('totalChart'), {
    type: 'line',
    data: {
        labels: <?= json_encode($labels_total) ?>,
        datasets: [{
            label: 'کل بسته‌ها',
            data: <?= json_encode($values_total) ?>,
            borderColor: '#3b82f6',
            backgroundColor: 'rgba(59,130,246,0.1)',
            tension: 0.4,
            fill: true,
            pointRadius: 0,
            pointHoverRadius: 5
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true, grid: { color: '#e5e7eb' } },
            x: { grid: { display: false } }
        }
    }
});

// چارت تحویل‌شده‌ها
new Chart(document.getElementById('deliveredChart'), {
    type: 'bar',
    data: {
        labels: <?= json_encode($labels_delivered) ?>,
        datasets: [{
            label: 'تحویل‌شده',
            data: <?= json_encode($values_delivered) ?>,
            backgroundColor: '#fbbf24',
            borderColor: '#d97706',
            borderWidth: 1,
            borderRadius: 6
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true, grid: { color: '#e5e7eb' } },
            x: { grid: { display: false } }
        }
    }
});
</script>

</body>
</html>
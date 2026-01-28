<?php
session_start();

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/Auth.php';
require_once __DIR__ . '/../includes/Security.php';
require_once __DIR__ . '/../includes/Package.php';
require_once __DIR__ . '/../includes/Shamsi.php';

Auth::requireLogin();

$stats = Package::getStats();
$chartData = Package::getLast30DaysStats();
$pageTitle = 'داشبورد';

// آخرین بسته‌های ثبت شده
$db = Database::getInstance();
$recentPackages = $db->query("
    SELECT p.*, pt.name as type_name 
    FROM packages p 
    LEFT JOIN package_types pt ON p.package_type_id = pt.id 
    ORDER BY p.registered_at DESC 
    LIMIT 5
")->fetchAll();

// آماده‌سازی داده چارت
$chartLabels = [];
$chartRegistered = [];
$chartDelivered = [];

foreach ($chartData as $day) {
    $chartLabels[] = Shamsi::date($day['date']);
    $chartRegistered[] = (int)$day['registered'];
    $chartDelivered[] = (int)$day['delivered'];
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> - <?= SITE_NAME ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css" rel="stylesheet">
    <style>
        * { font-family: 'Vazirmatn', sans-serif; }
        .card-hover { transition: all 0.2s ease; }
        .card-hover:hover { transform: translateY(-2px); box-shadow: 0 8px 30px rgba(0,0,0,0.08); }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .animate-in { animation: fadeIn 0.4s ease forwards; }
        
        /* اسکرول‌بار مینیمال - تم تیره */
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: #e2e8f0; border-radius: 10px; }
        ::-webkit-scrollbar-thumb { background: #64748b; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #475569; }
        html { scrollbar-width: thin; scrollbar-color: #64748b #e2e8f0; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen">

<div class="flex flex-col lg:flex-row">
    <?php include __DIR__ . '/../includes/templates/sidebar.php'; ?>
    
    <main class="flex-1 lg:mr-80 p-4 lg:p-6">
        <!-- هدر -->
        <div class="mb-6 lg:mb-8 animate-in">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-xl lg:text-2xl font-semibold text-slate-800">سلام، <?= Security::escape(Auth::getFullName()) ?></h1>
                    <p class="text-slate-500 mt-1 text-sm lg:text-base"><?= Shamsi::date() ?> - <?= Shamsi::time() ?></p>
                </div>
                <a href="/admin/register.php" class="flex items-center justify-center gap-2 px-4 py-2.5 bg-slate-900 text-white rounded-xl hover:bg-slate-800 transition text-sm lg:text-base">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span class="font-medium">ثبت بسته جدید</span>
                </a>
            </div>
        </div>
        
        <!-- کارت‌های آمار -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 lg:gap-5 mb-6 lg:mb-8">
            <div class="bg-white rounded-xl lg:rounded-2xl border border-slate-200/60 p-4 lg:p-6 card-hover animate-in">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs lg:text-sm font-medium text-slate-500">کل بسته‌ها</p>
                        <p class="text-2xl lg:text-3xl font-semibold text-slate-800 mt-1 lg:mt-2"><?= number_format($stats['total'] ?? 0) ?></p>
                    </div>
                    <div class="w-10 h-10 lg:w-12 lg:h-12 bg-slate-100 rounded-lg lg:rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5 lg:w-6 lg:h-6 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl lg:rounded-2xl border border-slate-200/60 p-4 lg:p-6 card-hover animate-in">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs lg:text-sm font-medium text-slate-500">در انتظار</p>
                        <p class="text-2xl lg:text-3xl font-semibold text-amber-600 mt-1 lg:mt-2"><?= number_format($stats['pending'] ?? 0) ?></p>
                    </div>
                    <div class="w-10 h-10 lg:w-12 lg:h-12 bg-amber-50 rounded-lg lg:rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5 lg:w-6 lg:h-6 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl lg:rounded-2xl border border-slate-200/60 p-4 lg:p-6 card-hover animate-in">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs lg:text-sm font-medium text-slate-500">تحویل شده</p>
                        <p class="text-2xl lg:text-3xl font-semibold text-emerald-600 mt-1 lg:mt-2"><?= number_format($stats['delivered'] ?? 0) ?></p>
                    </div>
                    <div class="w-10 h-10 lg:w-12 lg:h-12 bg-emerald-50 rounded-lg lg:rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5 lg:w-6 lg:h-6 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl lg:rounded-2xl border border-slate-200/60 p-4 lg:p-6 card-hover animate-in">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs lg:text-sm font-medium text-slate-500">ثبت امروز</p>
                        <p class="text-2xl lg:text-3xl font-semibold text-blue-600 mt-1 lg:mt-2"><?= number_format($stats['today_registered'] ?? 0) ?></p>
                    </div>
                    <div class="w-10 h-10 lg:w-12 lg:h-12 bg-blue-50 rounded-lg lg:rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5 lg:w-6 lg:h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- چارت -->
        <div class="bg-white rounded-xl lg:rounded-2xl border border-slate-200/60 p-4 lg:p-6 mb-6 lg:mb-8 animate-in">
            <h2 class="font-semibold text-slate-800 mb-4 text-sm lg:text-base">نمودار ۳۰ روز اخیر</h2>
            <div class="h-52 lg:h-64">
                <canvas id="statsChart"></canvas>
            </div>
        </div>
        
        <!-- بخش پایین -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 lg:gap-6">
            <!-- دسترسی سریع -->
            <div class="bg-white rounded-xl lg:rounded-2xl border border-slate-200/60 p-4 lg:p-6 animate-in">
                <h2 class="font-semibold text-slate-800 mb-4 text-sm lg:text-base">دسترسی سریع</h2>
                <div class="space-y-2 lg:space-y-3">
                    <a href="/admin/register.php" class="flex items-center gap-3 lg:gap-4 p-3 lg:p-4 rounded-xl bg-slate-900 text-white hover:bg-slate-800 transition">
                        <div class="w-9 h-9 lg:w-10 lg:h-10 bg-white/10 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 lg:w-5 lg:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v16m8-8H4"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="font-medium text-sm lg:text-base">ثبت بسته جدید</p>
                        </div>
                    </a>
                    
                    <a href="/admin/deliver.php" class="flex items-center gap-3 lg:gap-4 p-3 lg:p-4 rounded-xl bg-amber-50 text-slate-800 hover:bg-amber-100 transition border border-amber-200/50">
                        <div class="w-9 h-9 lg:w-10 lg:h-10 bg-amber-500 rounded-lg flex items-center justify-center text-white">
                            <svg class="w-4 h-4 lg:w-5 lg:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="font-medium text-sm lg:text-base">تحویل بسته</p>
                        </div>
                    </a>
                    
                    <a href="/admin/search.php" class="flex items-center gap-3 lg:gap-4 p-3 lg:p-4 rounded-xl bg-slate-50 text-slate-800 hover:bg-slate-100 transition border border-slate-200/50">
                        <div class="w-9 h-9 lg:w-10 lg:h-10 bg-slate-200 rounded-lg flex items-center justify-center text-slate-600">
                            <svg class="w-4 h-4 lg:w-5 lg:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="font-medium text-sm lg:text-base">جستجو و گزارش</p>
                        </div>
                    </a>
                </div>
            </div>
            
            <!-- آخرین بسته‌ها -->
            <div class="lg:col-span-2 bg-white rounded-xl lg:rounded-2xl border border-slate-200/60 p-4 lg:p-6 animate-in">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="font-semibold text-slate-800 text-sm lg:text-base">آخرین بسته‌ها</h2>
                    <a href="/admin/search.php" class="text-xs lg:text-sm text-slate-500 hover:text-slate-700">مشاهده همه</a>
                </div>
                
                <?php if (empty($recentPackages)): ?>
                    <div class="text-center py-8 text-slate-400">
                        <p class="text-sm">هنوز بسته‌ای ثبت نشده</p>
                    </div>
                <?php else: ?>
                    <div class="overflow-x-auto -mx-4 lg:mx-0">
                        <table class="w-full min-w-[500px]">
                            <thead>
                                <tr class="text-slate-500 text-xs lg:text-sm">
                                    <th class="text-right pb-3 px-4 lg:px-0 font-medium">کد رهگیری</th>
                                    <th class="text-right pb-3 font-medium">گیرنده</th>
                                    <th class="text-right pb-3 font-medium">وضعیت</th>
                                    <th class="text-right pb-3 font-medium">تاریخ</th>
                                </tr>
                            </thead>
                            <tbody class="text-xs lg:text-sm">
                                <?php foreach ($recentPackages as $pkg): ?>
                                    <tr class="border-t border-slate-100">
                                        <td class="py-3 px-4 lg:px-0 font-mono text-slate-600"><?= $pkg['tracking_code'] ?></td>
                                        <td class="py-3 text-slate-800"><?= Security::escape($pkg['receiver_name']) ?></td>
                                        <td class="py-3">
                                            <?php if ($pkg['status'] === 'delivered'): ?>
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-emerald-50 text-emerald-600 rounded text-xs font-medium">تحویل شده</span>
                                            <?php else: ?>
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-amber-50 text-amber-600 rounded text-xs font-medium">در انتظار</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-3 text-slate-400"><?= Shamsi::date($pkg['registered_at']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

<script>
Chart.defaults.font.family = 'Vazirmatn';

const chartLabels = <?= json_encode(array_values($chartLabels)) ?>;
const chartRegistered = <?= json_encode(array_values($chartRegistered)) ?>;
const chartDelivered = <?= json_encode(array_values($chartDelivered)) ?>;

const ctx = document.getElementById('statsChart');

if (chartLabels.length === 0) {
    ctx.parentElement.innerHTML = '<div class="flex items-center justify-center h-full text-slate-400"><p>هنوز داده‌ای برای نمایش وجود ندارد</p></div>';
} else {
    new Chart(ctx.getContext('2d'), {
        type: 'line',
        data: {
            labels: chartLabels,
            datasets: [
                {
                    label: 'ثبت شده',
                    data: chartRegistered,
                    borderColor: '#1e293b',
                    backgroundColor: 'rgba(30, 41, 59, 0.05)',
                    fill: true,
                    tension: 0.4,
                    borderWidth: 2,
                    pointRadius: 4,
                    pointBackgroundColor: '#1e293b',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointHoverRadius: 6
                },
                {
                    label: 'تحویل شده',
                    data: chartDelivered,
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.05)',
                    fill: true,
                    tension: 0.4,
                    borderWidth: 2,
                    pointRadius: 4,
                    pointBackgroundColor: '#10b981',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointHoverRadius: 6
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                    align: 'end',
                    rtl: true,
                    labels: {
                        boxWidth: 12,
                        padding: 20,
                        usePointStyle: true,
                        pointStyle: 'circle'
                    }
                },
                tooltip: {
                    rtl: true,
                    titleAlign: 'right',
                    bodyAlign: 'right',
                    backgroundColor: '#1e293b',
                    titleColor: '#fff',
                    bodyColor: '#fff',
                    borderColor: '#334155',
                    borderWidth: 1,
                    padding: 12,
                    displayColors: true,
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': ' + context.parsed.y + ' بسته';
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { 
                        maxTicksLimit: 7,
                        maxRotation: 0
                    },
                    border: { display: false }
                },
                y: {
                    beginAtZero: true,
                    grid: { 
                        color: '#f1f5f9',
                        drawBorder: false
                    },
                    ticks: { 
                        stepSize: 1,
                        padding: 10
                    },
                    border: { display: false }
                }
            },
            interaction: {
                intersect: false,
                mode: 'index'
            },
            elements: {
                line: {
                    capBezierPoints: true
                }
            }
        }
    });
}
</script>

</body>
</html>

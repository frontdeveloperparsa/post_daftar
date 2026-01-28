<?php
session_start();

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/Auth.php';
require_once __DIR__ . '/../includes/Security.php';
require_once __DIR__ . '/../includes/Package.php';
require_once __DIR__ . '/../includes/PackageType.php';
require_once __DIR__ . '/../includes/Shamsi.php';

Auth::requireLogin();

$types = PackageType::getAll();
$message = '';
$messageType = '';

// ثبت دسته‌ای یا CSV
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Security::validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $message = 'خطای امنیتی';
        $messageType = 'error';
    } else {
        $action = $_POST['action'] ?? 'bulk';
        $typeId = (int)($_POST['package_type_id'] ?? 0);
        
        // آپلود CSV
        if ($action === 'csv' && isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] === UPLOAD_ERR_OK) {
            if ($typeId <= 0) {
                $message = 'نوع بسته را انتخاب کنید';
                $messageType = 'error';
            } else {
                $file = $_FILES['csv_file']['tmp_name'];
                $success = 0;
                $failed = 0;
                
                if (($handle = fopen($file, 'r')) !== false) {
                    $firstRow = true;
                    while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                        // رد کردن هدر
                        if ($firstRow) {
                            $firstRow = false;
                            // اگه ردیف اول شماره نداره، یعنی هدره
                            if (!preg_match('/^0?9[0-9]{9}$/', preg_replace('/[^0-9]/', '', $data[1] ?? ''))) {
                                continue;
                            }
                        }
                        
                        $name = trim($data[0] ?? '');
                        $phone = preg_replace('/[^0-9]/', '', $data[1] ?? '');
                        
                        if (strlen($phone) == 10 && $phone[0] == '9') {
                            $phone = '0' . $phone;
                        }
                        
                        if (empty($name) || strlen($phone) != 11) {
                            $failed++;
                            continue;
                        }
                        
                        $trackingCode = Shamsi::generateTrackingCode();
                        
                        $result = Package::create([
                            'tracking_code' => $trackingCode,
                            'receiver_name' => $name,
                            'receiver_phone' => $phone,
                            'package_type_id' => $typeId,
                            'description' => null,
                            'image_path' => null,
                            'registered_by' => Auth::getUserId()
                        ]);
                        
                        if ($result['success']) {
                            $success++;
                        } else {
                            $failed++;
                        }
                        
                        usleep(1000);
                    }
                    fclose($handle);
                    
                    if ($success > 0) {
                        $message = "$success بسته از فایل CSV ثبت شد";
                        if ($failed > 0) {
                            $message .= " ($failed خطا)";
                        }
                        $messageType = 'success';
                    } else {
                        $message = 'هیچ بسته‌ای از فایل ثبت نشد. فرمت فایل را بررسی کنید.';
                        $messageType = 'error';
                    }
                } else {
                    $message = 'خطا در خواندن فایل';
                    $messageType = 'error';
                }
            }
        }
        // ثبت دسته‌ای معمولی
        else {
            $names = $_POST['names'] ?? [];
            $phones = $_POST['phones'] ?? [];
        
        if ($typeId <= 0) {
            $message = 'نوع بسته را انتخاب کنید';
            $messageType = 'error';
        } else {
            $success = 0;
            $failed = 0;
            
            for ($i = 0; $i < count($names); $i++) {
                $name = trim($names[$i] ?? '');
                $phone = trim($phones[$i] ?? '');
                
                // نرمال کردن شماره
                $phone = preg_replace('/[^0-9]/', '', $phone);
                if (strlen($phone) == 10 && $phone[0] == '9') {
                    $phone = '0' . $phone;
                }
                
                if (empty($name) || strlen($phone) != 11) {
                    continue;
                }
                
                $trackingCode = Shamsi::generateTrackingCode();
                
                $result = Package::create([
                    'tracking_code' => $trackingCode,
                    'receiver_name' => $name,
                    'receiver_phone' => $phone,
                    'package_type_id' => $typeId,
                    'description' => null,
                    'image_path' => null,
                    'registered_by' => Auth::getUserId()
                ]);
                
                if ($result['success']) {
                    $success++;
                } else {
                    $failed++;
                }
                
                usleep(1000);
            }
            
            if ($success > 0) {
                    $message = "$success بسته با موفقیت ثبت شد";
                    if ($failed > 0) {
                        $message .= " ($failed خطا)";
                    }
                    $messageType = 'success';
                } else {
                    $message = 'هیچ بسته‌ای ثبت نشد. لطفا اطلاعات را بررسی کنید.';
                    $messageType = 'error';
                }
            }
        }
    }
}

$csrfToken = Security::generateCSRFToken();
$pageTitle = 'ثبت بسته';
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ثبت بسته - پنل مدیریت</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css" rel="stylesheet">
    <style>
        * { font-family: 'Vazirmatn', sans-serif; }
        .input-cell {
            border: none;
            background: transparent;
            padding: 10px 12px;
            width: 100%;
            outline: none;
            transition: background 0.15s;
        }
        .input-cell:focus {
            background: #dbeafe;
        }
        .table-row:hover {
            background: #f8fafc;
        }
        .table-row:nth-child(even) {
            background: #fafafa;
        }
        .table-row:nth-child(even):hover {
            background: #f1f5f9;
        }
    </style>
</head>
<body class="bg-slate-100 min-h-screen">
    <div class="flex">
        <?php include __DIR__ . '/../includes/templates/sidebar.php'; ?>
        
        <main class="flex-1 mr-64 p-8">
            <div class="mb-6 flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-medium text-slate-800">ثبت بسته</h1>
                    <p class="text-slate-500 text-sm mt-1"><?= Shamsi::fullDate() ?> - <?= Shamsi::format(null, 'H:i') ?></p>
                </div>
            </div>
            
            <?php if ($message): ?>
            <div class="mb-6 p-4 rounded-lg <?= $messageType === 'success' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-red-50 text-red-700 border border-red-200' ?>">
                <?= $message ?>
            </div>
            <?php endif; ?>
            
            <form method="POST" id="bulkForm">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                
                <!-- انتخاب نوع بسته -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 mb-6">
                    <label class="block text-sm font-medium text-slate-700 mb-2">نوع بسته را انتخاب کنید</label>
                    <select name="package_type_id" required class="w-72 px-4 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white">
                        <option value="">-- انتخاب نوع بسته --</option>
                        <?php foreach ($types as $type): ?>
                            <option value="<?= $type['id'] ?>"><?= htmlspecialchars($type['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <!-- جدول ورود اطلاعات -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="px-5 py-4 border-b border-slate-200 flex justify-between items-center bg-slate-50">
                        <div>
                            <h2 class="font-medium text-slate-800">ورود اطلاعات بسته‌ها</h2>
                            <p class="text-xs text-slate-500 mt-1">با Enter به فیلد بعدی بروید. کد رهگیری خودکار تولید می‌شود.</p>
                        </div>
                        <div class="flex gap-2">
                            <button type="button" onclick="addRows(5)" class="px-3 py-1.5 text-sm bg-white border border-slate-300 text-slate-600 rounded-lg hover:bg-slate-50">+۵ ردیف</button>
                            <button type="button" onclick="addRows(10)" class="px-3 py-1.5 text-sm bg-white border border-slate-300 text-slate-600 rounded-lg hover:bg-slate-50">+۱۰ ردیف</button>
                            <button type="button" onclick="clearAll()" class="px-3 py-1.5 text-sm bg-white border border-slate-300 text-slate-600 rounded-lg hover:bg-slate-50">پاک کردن</button>
                        </div>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-slate-100 text-slate-600 text-sm">
                                <tr>
                                    <th class="py-3 px-4 text-right font-medium w-16">#</th>
                                    <th class="py-3 px-4 text-right font-medium border-r border-slate-200">نام گیرنده</th>
                                    <th class="py-3 px-4 text-right font-medium border-r border-slate-200 w-48">شماره موبایل</th>
                                </tr>
                            </thead>
                            <tbody id="tableBody">
                                <!-- ردیف‌ها با جاوا اسکریپت اضافه می‌شن -->
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- دکمه ثبت -->
                <input type="hidden" name="action" value="bulk">
                <div class="mt-6 flex gap-4">
                    <button type="submit" class="px-8 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium transition">
                        ثبت همه بسته‌ها
                    </button>
                    <span class="text-slate-500 text-sm self-center" id="rowCounter">۰ ردیف پر شده</span>
                </div>
            </form>
            
            <!-- بخش آپلود CSV -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 mt-8">
                <h2 class="font-medium text-slate-800 mb-4">ثبت با فایل CSV</h2>
                <p class="text-sm text-slate-500 mb-4">فایل CSV با دو ستون آپلود کنید: <span class="font-mono bg-slate-100 px-2 py-0.5 rounded">نام گیرنده</span> و <span class="font-mono bg-slate-100 px-2 py-0.5 rounded">شماره موبایل</span></p>
                
                <form method="POST" enctype="multipart/form-data" class="flex flex-wrap items-end gap-4">
                    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                    <input type="hidden" name="action" value="csv">
                    
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">نوع بسته</label>
                        <select name="package_type_id" required class="px-4 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 bg-white">
                            <option value="">-- انتخاب --</option>
                            <?php foreach ($types as $type): ?>
                                <option value="<?= $type['id'] ?>"><?= htmlspecialchars($type['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">فایل CSV</label>
                        <input type="file" name="csv_file" accept=".csv,.txt" required class="block w-full text-sm text-slate-500 file:ml-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer">
                    </div>
                    
                    <button type="submit" class="px-6 py-2.5 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 font-medium transition">
                        آپلود و ثبت
                    </button>
                </form>
                
                <div class="mt-4 p-4 bg-slate-50 rounded-lg">
                    <p class="text-sm text-slate-600 font-medium mb-2">نمونه فرمت فایل CSV:</p>
                    <code class="text-xs text-slate-700 font-mono block bg-white p-3 rounded border border-slate-200">
                        نام گیرنده,شماره موبایل<br>
                        علی محمدی,09121234567<br>
                        رضا احمدی,09351234567
                    </code>
                </div>
            </div>
        </main>
    </div>
    
    <script>
        let rowId = 0;
        
        function createRow() {
            rowId++;
            const tr = document.createElement('tr');
            tr.className = 'table-row border-b border-slate-100';
            tr.id = 'row-' + rowId;
            tr.innerHTML = `
                <td class="py-1 px-4 text-slate-400 text-sm">${rowId}</td>
                <td class="border-r border-slate-100">
                    <input type="text" name="names[]" class="input-cell" placeholder="نام و نام خانوادگی" data-row="${rowId}" data-col="name" autocomplete="off">
                </td>
                <td class="border-r border-slate-100">
                    <input type="text" name="phones[]" class="input-cell" placeholder="۰۹۱۲۳۴۵۶۷۸۹" data-row="${rowId}" data-col="phone" dir="ltr" maxlength="11" autocomplete="off">
                </td>
            `;
            return tr;
        }
        
        function addRows(count) {
            const tbody = document.getElementById('tableBody');
            for (let i = 0; i < count; i++) {
                tbody.appendChild(createRow());
            }
            setupInputHandlers();
        }
        
        function clearAll() {
            if (confirm('همه ردیف‌ها پاک شوند؟')) {
                document.getElementById('tableBody').innerHTML = '';
                rowId = 0;
                addRows(15);
                document.querySelector('#tableBody input').focus();
            }
        }
        
        function setupInputHandlers() {
            document.querySelectorAll('.input-cell').forEach(input => {
                input.onkeydown = function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        const row = parseInt(this.dataset.row);
                        const col = this.dataset.col;
                        
                        if (col === 'name') {
                            // برو به شماره همین ردیف
                            const phone = document.querySelector(`input[data-row="${row}"][data-col="phone"]`);
                            if (phone) phone.focus();
                        } else {
                            // برو به نام ردیف بعدی
                            let nextName = document.querySelector(`input[data-row="${row + 1}"][data-col="name"]`);
                            if (!nextName) {
                                addRows(5);
                                nextName = document.querySelector(`input[data-row="${row + 1}"][data-col="name"]`);
                            }
                            if (nextName) nextName.focus();
                        }
                    }
                    
                    // آپدیت شمارنده
                    setTimeout(updateCounter, 100);
                };
                
                input.onblur = updateCounter;
            });
        }
        
        function updateCounter() {
            let filled = 0;
            const rows = document.querySelectorAll('#tableBody tr');
            rows.forEach(row => {
                const name = row.querySelector('input[data-col="name"]').value.trim();
                const phone = row.querySelector('input[data-col="phone"]').value.trim();
                if (name && phone) filled++;
            });
            document.getElementById('rowCounter').textContent = toPersianNum(filled) + ' ردیف پر شده';
        }
        
        function toPersianNum(num) {
            const persianDigits = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
            return num.toString().replace(/\d/g, d => persianDigits[d]);
        }
        
        // شروع با ۱۵ ردیف
        addRows(15);
        document.querySelector('#tableBody input').focus();
    </script>
</body>
</html>

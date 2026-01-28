<?php
$pageTitle = 'تحویل بسته';
require_once __DIR__ . '/../includes/templates/header.php';
require_once __DIR__ . '/../includes/Package.php';
require_once __DIR__ . '/../includes/PackageType.php';

Auth::requireLogin();

$packages = [];
$message = '';
$messageType = '';
$searchQuery = '';
$typeFilter = '';
$statusFilter = 'pending';

// جستجو
if (!empty($_GET['q']) || isset($_GET['search'])) {
    $searchQuery = Security::sanitize($_GET['q'] ?? '');
    $typeFilter = $_GET['type'] ?? '';
    $statusFilter = $_GET['status'] ?? 'pending';
    
    $packages = Package::searchForDelivery($searchQuery, $typeFilter ? (int)$typeFilter : null, $statusFilter);
}

// تحویل بسته‌ها
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['deliver'])) {
    if (!Security::validateCSRFToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
        $message = 'خطای امنیتی';
        $messageType = 'error';
    } else {
        $packageIds = $_POST['package_ids'] ?? [];
        $deliveryMethod = $_POST['delivery_method'] ?? '';
        
        if (empty($packageIds)) {
            $message = 'لطفا حداقل یک بسته انتخاب کنید';
            $messageType = 'error';
        } elseif (empty($deliveryMethod)) {
            $message = 'لطفا روش تحویل را انتخاب کنید';
            $messageType = 'error';
        } else {
            $deliveryData = [
                'method' => $deliveryMethod
            ];
            
            if ($deliveryMethod === 'signature') {
                $deliveryData['receiver_name'] = Security::sanitize($_POST['receiver_name'] ?? '');
                $deliveryData['receiver_phone'] = Security::sanitize($_POST['receiver_phone'] ?? '');
                $deliveryData['receiver_national_code'] = Security::sanitize($_POST['receiver_national_code'] ?? '');
                $deliveryData['signature'] = $_POST['signature_data'] ?? '';
            } elseif ($deliveryMethod === 'photo') {
                // آپلود عکس
                if (!empty($_POST['photo_data'])) {
                    // عکس از دوربین (base64)
                    $photoData = $_POST['photo_data'];
                    $photoData = str_replace('data:image/jpeg;base64,', '', $photoData);
                    $photoData = str_replace('data:image/png;base64,', '', $photoData);
                    $photoData = base64_decode($photoData);
                    
                    $fileName = 'delivery_' . time() . '_' . rand(1000, 9999) . '.jpg';
                    $uploadPath = __DIR__ . '/../uploads/' . $fileName;
                    
                    if (file_put_contents($uploadPath, $photoData)) {
                        $deliveryData['photo'] = $fileName;
                    }
                } elseif (!empty($_FILES['delivery_photo']['tmp_name'])) {
                    // آپلود فایل
                    $file = $_FILES['delivery_photo'];
                    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                    
                    if (in_array($ext, ['jpg', 'jpeg', 'png'])) {
                        $fileName = 'delivery_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
                        $uploadPath = __DIR__ . '/../uploads/' . $fileName;
                        
                        if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                            $deliveryData['photo'] = $fileName;
                        }
                    }
                }
            }
            
            $count = Package::deliverMultiple($packageIds, Auth::getUserId(), $deliveryData);
            
            if ($count > 0) {
                $message = $count . ' بسته با موفقیت تحویل داده شد';
                $messageType = 'success';
                $packages = []; // پاک کردن لیست
            } else {
                $message = 'خطا در تحویل بسته‌ها';
                $messageType = 'error';
            }
        }
    }
}

$packageTypes = PackageType::getAll();
?>

<div class="flex">
    <?php include __DIR__ . '/../includes/templates/sidebar.php'; ?>
    
    <main class="flex-1 mr-64 p-8">
        <div class="max-w-6xl">
            <h1 class="text-2xl font-medium text-slate-800 mb-8">تحویل بسته</h1>
            
            <!-- فرم جستجو -->
            <form method="GET" class="bg-white rounded-2xl border border-slate-200 p-6 mb-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-slate-700 mb-2">جستجو</label>
                        <input type="text" name="q" 
                               value="<?= Security::escape($searchQuery) ?>"
                               class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none"
                               placeholder="کد رهگیری، نام یا شماره تلفن">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">نوع بسته</label>
                        <select name="type" class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none">
                            <option value="">همه انواع</option>
                            <?php foreach ($packageTypes as $type): ?>
                            <option value="<?= $type['id'] ?>" <?= $typeFilter == $type['id'] ? 'selected' : '' ?>>
                                <?= Security::escape($type['name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">وضعیت</label>
                        <select name="status" class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none">
                            <option value="pending" <?= $statusFilter === 'pending' ? 'selected' : '' ?>>در انتظار</option>
                            <option value="delivered" <?= $statusFilter === 'delivered' ? 'selected' : '' ?>>تحویل شده</option>
                            <option value="all" <?= $statusFilter === 'all' ? 'selected' : '' ?>>همه</option>
                        </select>
                    </div>
                </div>
                
                <div class="mt-4">
                    <button type="submit" name="search" value="1"
                            class="px-6 py-3 bg-slate-800 text-white rounded-xl font-medium hover:bg-slate-700 transition">
                        جستجو
                    </button>
                </div>
            </form>
            
            <?php if ($message): ?>
            <div class="mb-6 p-4 rounded-xl <?php
                if ($messageType === 'success') echo 'bg-green-50 border border-green-200 text-green-700';
                elseif ($messageType === 'warning') echo 'bg-amber-50 border border-amber-200 text-amber-700';
                else echo 'bg-red-50 border border-red-200 text-red-700';
            ?>">
                <?= Security::escape($message) ?>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($packages)): ?>
            <form method="POST" enctype="multipart/form-data" id="deliverForm">
                <?= Security::csrfField() ?>
                
                <!-- لیست بسته‌ها -->
                <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden mb-6">
                    <div class="p-4 border-b border-slate-200 bg-slate-50 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <input type="checkbox" id="selectAll" class="w-5 h-5 rounded border-slate-300">
                            <label for="selectAll" class="text-sm font-medium text-slate-700">انتخاب همه</label>
                        </div>
                        <span class="text-sm text-slate-500"><?= count($packages) ?> بسته یافت شد</span>
                    </div>
                    
                    <div class="divide-y divide-slate-100 max-h-96 overflow-y-auto">
                        <?php foreach ($packages as $pkg): ?>
                        <label class="flex items-center gap-4 p-4 hover:bg-slate-50 cursor-pointer">
                            <input type="checkbox" name="package_ids[]" value="<?= $pkg['id'] ?>" 
                                   class="package-checkbox w-5 h-5 rounded border-slate-300"
                                   <?= $pkg['status'] === 'delivered' ? 'disabled' : '' ?>>
                            <div class="flex-1 grid grid-cols-5 gap-4 items-center">
                                <div>
                                    <span class="text-xs text-slate-400">کد رهگیری</span>
                                    <p class="font-medium text-slate-800"><?= Security::escape($pkg['tracking_code']) ?></p>
                                </div>
                                <div>
                                    <span class="text-xs text-slate-400">گیرنده</span>
                                    <p class="font-medium text-slate-800"><?= Security::escape($pkg['receiver_name']) ?></p>
                                </div>
                                <div>
                                    <span class="text-xs text-slate-400">شماره</span>
                                    <p class="font-medium text-slate-800" dir="ltr"><?= Security::escape($pkg['receiver_phone']) ?></p>
                                </div>
                                <div>
                                    <span class="text-xs text-slate-400">نوع</span>
                                    <p class="font-medium text-slate-800"><?= Security::escape($pkg['type_name']) ?></p>
                                </div>
                                <div>
                                    <?php if ($pkg['status'] === 'delivered'): ?>
                                    <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs">تحویل شده</span>
                                    <?php else: ?>
                                    <span class="px-3 py-1 bg-amber-100 text-amber-700 rounded-full text-xs">در انتظار</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- بخش تحویل -->
                <div class="bg-white rounded-2xl border border-slate-200 p-6" id="deliverySection" style="display: none;">
                    <h2 class="text-lg font-medium text-slate-800 mb-6">روش تحویل</h2>
                    
                    <!-- انتخاب روش -->
                    <div class="grid grid-cols-2 gap-4 mb-6">
                        <label class="relative cursor-pointer">
                            <input type="radio" name="delivery_method" value="signature" class="peer sr-only" id="methodSignature">
                            <div class="p-4 border-2 border-slate-200 rounded-xl text-center peer-checked:border-amber-500 peer-checked:bg-amber-50 transition">
                                <svg class="w-8 h-8 mx-auto mb-2 text-slate-400 peer-checked:text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                </svg>
                                <span class="font-medium text-slate-700">امضا</span>
                            </div>
                        </label>
                        
                        <label class="relative cursor-pointer">
                            <input type="radio" name="delivery_method" value="photo" class="peer sr-only" id="methodPhoto">
                            <div class="p-4 border-2 border-slate-200 rounded-xl text-center peer-checked:border-amber-500 peer-checked:bg-amber-50 transition">
                                <svg class="w-8 h-8 mx-auto mb-2 text-slate-400 peer-checked:text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                <span class="font-medium text-slate-700">عکس</span>
                            </div>
                        </label>
                    </div>
                    
                    <!-- فرم امضا -->
                    <div id="signatureForm" style="display: none;">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-2">نام و نام خانوادگی گیرنده</label>
                                <input type="text" name="receiver_name" 
                                       class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-2">شماره موبایل</label>
                                <input type="text" name="receiver_phone" maxlength="11"
                                       class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none"
                                       dir="ltr">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-2">کد ملی</label>
                                <input type="text" name="receiver_national_code" maxlength="10"
                                       class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none"
                                       dir="ltr">
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-slate-700 mb-2">امضای گیرنده</label>
                            <div class="border-2 border-dashed border-slate-300 rounded-xl p-2 bg-white">
                                <canvas id="signatureCanvas" width="600" height="200" class="w-full border border-slate-200 rounded-lg bg-white cursor-crosshair"></canvas>
                            </div>
                            <input type="hidden" name="signature_data" id="signatureData">
                            <button type="button" onclick="clearSignature()" class="mt-2 text-sm text-slate-500 hover:text-slate-700">
                                پاک کردن امضا
                            </button>
                        </div>
                    </div>
                    
                    <!-- فرم عکس -->
                    <div id="photoForm" style="display: none;">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-slate-700 mb-2">عکس تحویل</label>
                            
                            <div class="grid grid-cols-2 gap-4 mb-4">
                                <button type="button" onclick="openCamera()" 
                                        class="p-4 border-2 border-dashed border-slate-300 rounded-xl text-center hover:border-amber-500 hover:bg-amber-50 transition">
                                    <svg class="w-8 h-8 mx-auto mb-2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    <span class="text-sm text-slate-600">گرفتن عکس با دوربین</span>
                                </button>
                                
                                <label class="p-4 border-2 border-dashed border-slate-300 rounded-xl text-center hover:border-amber-500 hover:bg-amber-50 transition cursor-pointer">
                                    <svg class="w-8 h-8 mx-auto mb-2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    <span class="text-sm text-slate-600">آپلود از گالری</span>
                                    <input type="file" name="delivery_photo" accept="image/*" class="hidden" onchange="previewPhoto(this)">
                                </label>
                            </div>
                            
                            <input type="hidden" name="photo_data" id="photoData">
                            
                            <!-- پیش‌نمایش عکس -->
                            <div id="photoPreview" class="hidden">
                                <img id="previewImage" src="" alt="پیش‌نمایش" class="max-w-full h-48 object-contain rounded-xl border border-slate-200">
                                <button type="button" onclick="clearPhoto()" class="mt-2 text-sm text-slate-500 hover:text-slate-700">
                                    حذف عکس
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- دوربین -->
                    <div id="cameraModal" class="fixed inset-0 bg-black/80 z-50 hidden items-center justify-center">
                        <div class="bg-white rounded-2xl p-4 max-w-lg w-full mx-4">
                            <video id="cameraVideo" autoplay playsinline class="w-full rounded-xl mb-4"></video>
                            <div class="flex gap-3">
                                <button type="button" onclick="capturePhoto()" class="flex-1 py-3 bg-amber-500 text-white rounded-xl font-medium">
                                    گرفتن عکس
                                </button>
                                <button type="button" onclick="closeCamera()" class="px-6 py-3 bg-slate-200 text-slate-700 rounded-xl font-medium">
                                    لغو
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" name="deliver" value="1"
                            class="w-full py-4 bg-green-600 text-white rounded-xl font-medium hover:bg-green-500 transition text-lg">
                        تایید تحویل بسته‌ها
                    </button>
                </div>
            </form>
            <?php endif; ?>
        </div>
    </main>
</div>

<script>
// انتخاب همه
document.getElementById('selectAll')?.addEventListener('change', function() {
    document.querySelectorAll('.package-checkbox:not(:disabled)').forEach(cb => {
        cb.checked = this.checked;
    });
    toggleDeliverySection();
});

// نمایش بخش تحویل
document.querySelectorAll('.package-checkbox').forEach(cb => {
    cb.addEventListener('change', toggleDeliverySection);
});

function toggleDeliverySection() {
    const checked = document.querySelectorAll('.package-checkbox:checked').length;
    document.getElementById('deliverySection').style.display = checked > 0 ? 'block' : 'none';
}

// تغییر روش تحویل
document.querySelectorAll('input[name="delivery_method"]').forEach(radio => {
    radio.addEventListener('change', function() {
        document.getElementById('signatureForm').style.display = this.value === 'signature' ? 'block' : 'none';
        document.getElementById('photoForm').style.display = this.value === 'photo' ? 'block' : 'none';
    });
});

// امضا
let signatureCanvas = document.getElementById('signatureCanvas');
let signatureCtx = signatureCanvas?.getContext('2d');
let isDrawing = false;

if (signatureCanvas) {
    signatureCanvas.addEventListener('mousedown', startDrawing);
    signatureCanvas.addEventListener('mousemove', draw);
    signatureCanvas.addEventListener('mouseup', stopDrawing);
    signatureCanvas.addEventListener('mouseout', stopDrawing);
    
    signatureCanvas.addEventListener('touchstart', function(e) {
        e.preventDefault();
        startDrawing(e.touches[0]);
    });
    signatureCanvas.addEventListener('touchmove', function(e) {
        e.preventDefault();
        draw(e.touches[0]);
    });
    signatureCanvas.addEventListener('touchend', stopDrawing);
}

function startDrawing(e) {
    isDrawing = true;
    signatureCtx.beginPath();
    const rect = signatureCanvas.getBoundingClientRect();
    signatureCtx.moveTo(e.clientX - rect.left, e.clientY - rect.top);
}

function draw(e) {
    if (!isDrawing) return;
    const rect = signatureCanvas.getBoundingClientRect();
    signatureCtx.lineTo(e.clientX - rect.left, e.clientY - rect.top);
    signatureCtx.strokeStyle = '#1e293b';
    signatureCtx.lineWidth = 2;
    signatureCtx.lineCap = 'round';
    signatureCtx.stroke();
}

function stopDrawing() {
    isDrawing = false;
    document.getElementById('signatureData').value = signatureCanvas.toDataURL();
}

function clearSignature() {
    signatureCtx.clearRect(0, 0, signatureCanvas.width, signatureCanvas.height);
    document.getElementById('signatureData').value = '';
}

// دوربین
let cameraStream = null;

function openCamera() {
    const modal = document.getElementById('cameraModal');
    const video = document.getElementById('cameraVideo');
    
    navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } })
        .then(stream => {
            cameraStream = stream;
            video.srcObject = stream;
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        })
        .catch(err => {
            alert('دسترسی به دوربین ممکن نیست');
        });
}

function closeCamera() {
    const modal = document.getElementById('cameraModal');
    if (cameraStream) {
        cameraStream.getTracks().forEach(track => track.stop());
    }
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

function capturePhoto() {
    const video = document.getElementById('cameraVideo');
    const canvas = document.createElement('canvas');
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    canvas.getContext('2d').drawImage(video, 0, 0);
    
    const photoData = canvas.toDataURL('image/jpeg', 0.8);
    document.getElementById('photoData').value = photoData;
    document.getElementById('previewImage').src = photoData;
    document.getElementById('photoPreview').classList.remove('hidden');
    
    closeCamera();
}

function previewPhoto(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('previewImage').src = e.target.result;
            document.getElementById('photoPreview').classList.remove('hidden');
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function clearPhoto() {
    document.getElementById('photoData').value = '';
    document.getElementById('photoPreview').classList.add('hidden');
    document.querySelector('input[name="delivery_photo"]').value = '';
}
</script>

<?php require_once __DIR__ . '/../includes/templates/footer.php'; ?>

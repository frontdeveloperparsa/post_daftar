## توضیحات کامل پروژه سامانه مدیریت بسته‌های پست دفتر پیشخوان

سلام v0،  
این پروژه فعلی سایت منه که با PHP + Bootstrap 5 RTL + Chart.js + فونت Vazirmatn ساخته شده. حالا می‌خوام کل سایت رو به Next.js 14+ (app router) + Tailwind CSS 4+ + TypeScript + Shadcn/ui ارتقا بدم. لطفاً تمام صفحات و ساختار فعلی رو حفظ کن، فقط به تکنولوژی جدید تبدیلش کن. UI رو خودم بهت می‌گم چطور باشه، تو فقط ساختار و منطق رو درست پیاده کن.

### دیتابیس (MySQL/MariaDB)
- نام دیتابیس: `post_daftar`
- جدول `packages` (جدول اصلی بسته‌ها):
  - `id` → INT AUTO_INCREMENT PRIMARY KEY
  - `type_id` → INT (FOREIGN KEY به package_types)
  - `receiver_name` → VARCHAR(255) – نام گیرنده
  - `receiver_phone` → VARCHAR(50) – شماره تلفن گیرنده
  - `receive_date` → DATE – تاریخ رسیدن بسته
  - `status` → VARCHAR(50) – وضعیت (مثل 'رسیده', 'تحویل شده')
  - `receipt_image` → VARCHAR(255) – مسیر رسید (مثل /post_daftar/assets/uploads/receipts/signature_[id]_[name]_[family]_[phone]_[timestamp].png)
  - `delivery_note` → TEXT – یادداشت تحویل (مثل "نام: ... | فامیل: ... | شماره: ...")
  - `created_at` → TIMESTAMP DEFAULT CURRENT_TIMESTAMP

- جدول `package_types` (انواع بسته):
  - `id` → INT AUTO_INCREMENT PRIMARY KEY
  - `name` → VARCHAR(100) – نام نوع (مثل 'نامه', 'پاکت', 'بسته')

- جدول `users` (برای لاگین):
  - `id` → INT AUTO_INCREMENT PRIMARY KEY
  - `username` → VARCHAR(50)
  - `password` → VARCHAR(255) – هش شده
  - `role` → VARCHAR(50) – 'admin' یا 'user'

اتصال فعلی با PDO در `includes/config.php` هست. در Next.js، API routes بساز برای دسترسی به این جدول‌ها (مثل /api/packages, /api/deliver و ...).

### ساختار پوشه‌ها و فایل‌های فعلی (PHP)

- `assets/`
  - `css/`
    - `bootstrap.rtl.min.css`
    - `style.css` (استایل‌های سفارشی)
  - `js/`
    - `bootstrap.bundle.min.js`
    - `chart.min.js`
    - `chartjs-adapter-date-fns.bundle.min.js`
  - `fonts/`
    - `vazirmatn.css` (و فایل‌های woff2)
  - `uploads/receipts/` → رسیدهای تحویل (عکس JPG/PNG و امضا PNG – این مسیر رو حفظ کن)

- `includes/`
  - `config.php` → اتصال PDO به دیتابیس، session_start، تابع toJalali (تبدیل تاریخ به شمسی)

- صفحات اصلی (همه در ریشه یا admin/):
  - `login.php` → فرم لاگین (نام کاربری + پسورد، session ذخیره، چک نقش 'admin')
  - `logout.php` → session_destroy و redirect به login
  - `dashboard.php` → داشبورد ادمین:
    - آمار لحظه‌ای (کل بسته‌ها، تحویل امروز، کل تحویل‌شده)
    - ۲ چارت تعاملی (روند کل بسته‌ها و تحویل‌شده در ۳۰ روز اخیر با Chart.js)
    - سایدبار ثابت سمت راست با لینک به تمام صفحات
  - `register_package.php` → فرم ثبت بسته جدید (نوع بسته، نام گیرنده، شماره، تاریخ رسیدن، وضعیت 'رسیده')
  - `upload_packages.php` → آپلود فایل CSV/Excel برای ثبت انبوه بسته‌ها
  - `add_type.php` → مدیریت انواع بسته (اضافه، ویرایش، حذف)
  - `search.php` → جستجوی پیشرفته بسته‌ها (نام، شماره، نوع، تاریخ، وضعیت)
  - `deliver_packages.php` → لیست بسته‌های رسیده، انتخاب گروهی، تحویل با:
    - آپلود عکس رسید (JPG/PNG)
    - یا امضا دیجیتال (canvas PNG + اطلاعات گیرنده در نام فایل و delivery_note)
    - ذخیره رسید در assets/uploads/receipts/
  - `delete_packages.php` → حذف بسته‌ها (انتخابی یا گروهی)
  - `view_delivered.php` → لیست بسته‌های تحویل‌شده، نمایش رسید (لینک دیدن/دانلود)، دکمه بازگشت به 'رسیده'
  - `export.php` → خروجی CSV تمام بسته‌ها یا فیلترشده

### نکات مهم فعلی
- تمام صفحات فقط برای نقش 'admin' قابل دسترسی هستن (چک session)
- رسید تحویل: نام فایل شامل id + name + family + phone + timestamp
- فونت Vazirmatn برای تمام متن‌ها (RTL کامل)
- چارت‌ها با Chart.js محلی
- مسیر رسیدها: /post_daftar/assets/uploads/receipts/

### خواسته ارتقا
- کل سایت رو به Next.js 14+ (app router) + Tailwind CSS 4+ + TypeScript + Shadcn/ui تبدیل کن
- ساختار پوشه‌بندی استاندارد Next.js رو رعایت کن (app/, components/, lib/, api/ و ...)
- تمام صفحات بالا رو جداگانه بساز (/dashboard, /register-package, /deliver-packages و ...)
- سایدبار ثابت سمت راست با آیکون‌های lucide-react یا feather
- داشبورد با آمار واقعی و چارت‌ها (از API دیتابیس بگیر)
- رسید تحویل رو در مسیر فعلی ذخیره کن
- فونت Vazirmatn رو لود کن، RTL کامل
- از Shadcn/ui برای کامپوننت‌ها استفاده کن (Card, Button, Input, Table, Dialog و ...)
- داده‌ها رو از API فرضی بگیر (بعداً با PHP backend وصل می‌کنیم)

لطفاً پروژه کامل رو با ساختار پوشه‌بندی شده بده (app/layout.tsx, app/page.tsx, components/ و غیره) و استایل‌ها رو با Tailwind بنویس. شروع کن!

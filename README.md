# سیستم مدیریت بسته‌های پستی

یک سیستم کامل و حرفه‌ای برای مدیریت ثبت، رهگیری و تحویل بسته‌های پستی با رابط کاربری فارسی و طراحی مدرن.

---

## فهرست مطالب

1. [معرفی](#معرفی)
2. [ویژگی‌ها](#ویژگیها)
3. [نیازمندی‌ها](#نیازمندیها)
4. [نصب و راه‌اندازی](#نصب-و-راهاندازی)
5. [ساختار پروژه](#ساختار-پروژه)
6. [ساختار دیتابیس](#ساختار-دیتابیس)
7. [راهنمای استفاده](#راهنمای-استفاده)
8. [API و توابع](#api-و-توابع)
9. [امنیت](#امنیت)
10. [سفارشی‌سازی](#سفارشیسازی)

---

## معرفی

این سیستم برای سازمان‌ها و شرکت‌هایی طراحی شده که نیاز به مدیریت بسته‌های پستی دارند. با این سیستم می‌توانید:

- بسته‌ها را ثبت و کد رهگیری خودکار تولید کنید
- بسته‌ها را با امضای دیجیتال یا عکس تحویل دهید
- مشتریان بتوانند بسته‌های خود را رهگیری کنند
- گزارش و آمار کاملی از وضعیت بسته‌ها داشته باشید

---

## ویژگی‌ها

### پنل مدیریت

| ویژگی | توضیحات |
|-------|---------|
| داشبورد | نمایش آمار کلی با نمودار خطی ۳۰ روز اخیر |
| ثبت بسته | ثبت تکی، گروهی (مثل اکسل) و با فایل CSV |
| تحویل بسته | جستجو و تحویل با امضا یا عکس |
| تحویل شده‌ها | لیست بسته‌های تحویل شده با جزئیات |
| جستجو | جستجوی پیشرفته با فیلتر و خروجی CSV |
| مدیریت انواع | تعریف انواع مختلف بسته |

### پنل مشتری

| ویژگی | توضیحات |
|-------|---------|
| ثبت‌نام | با نام، کد ملی و شماره موبایل |
| ورود | با شماره موبایل و رمز عبور |
| داشبورد | مشاهده لیست بسته‌های شخصی |
| رهگیری | جستجوی بسته با کد رهگیری |

### صفحه اصلی

| ویژگی | توضیحات |
|-------|---------|
| رهگیری عمومی | جستجوی بسته بدون نیاز به ورود |
| نمایش وضعیت | نمایش وضعیت تحویل/در انتظار |

### ویژگی‌های فنی

- **تاریخ شمسی** در تمام صفحات
- **کد رهگیری خودکار** با فرمت: `YYYYMMDD + 6 رقم رندوم`
- **ریسپانسیو** برای موبایل، تبلت و دسکتاپ
- **امنیت بالا** با CSRF، XSS Protection، Password Hashing
- **طراحی مینیمال** با Tailwind CSS

---

## نیازمندی‌ها

```
PHP >= 7.4
MySQL >= 5.7
PDO Extension
```

### هاست cPanel
- PHP 7.4 یا بالاتر
- MySQL Database
- SSL Certificate (توصیه می‌شود)

---

## نصب و راه‌اندازی

### مرحله ۱: آپلود فایل‌ها

تمام محتویات پوشه `php-project` را در `public_html` آپلود کنید.

### مرحله ۲: ساخت دیتابیس

1. در cPanel به **MySQL Databases** بروید
2. یک دیتابیس جدید بسازید
3. یک کاربر جدید با رمز قوی بسازید
4. کاربر را به دیتابیس با **All Privileges** اضافه کنید

### مرحله ۳: ایمپورت جداول

در phpMyAdmin فایل `database.sql` را ایمپورت کنید:

```sql
-- جدول کاربران (ادمین)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- جدول انواع بسته
CREATE TABLE package_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- جدول بسته‌ها
CREATE TABLE packages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tracking_code VARCHAR(20) UNIQUE NOT NULL,
    receiver_name VARCHAR(200) NOT NULL,
    receiver_phone VARCHAR(11) NOT NULL,
    package_type_id INT,
    description TEXT,
    image_path VARCHAR(255),
    status ENUM('pending', 'delivered') DEFAULT 'pending',
    registered_by INT,
    registered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    delivered_by INT,
    delivered_at TIMESTAMP NULL,
    delivery_method ENUM('signature', 'photo') NULL,
    receiver_signature LONGTEXT NULL,
    delivery_photo VARCHAR(255) NULL,
    receiver_national_code VARCHAR(10) NULL,
    delivered_to_name VARCHAR(200) NULL,
    delivered_to_phone VARCHAR(11) NULL,
    FOREIGN KEY (package_type_id) REFERENCES package_types(id),
    FOREIGN KEY (registered_by) REFERENCES users(id),
    FOREIGN KEY (delivered_by) REFERENCES users(id)
);

-- جدول مشتریان
CREATE TABLE customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    national_code VARCHAR(10) UNIQUE NOT NULL,
    phone VARCHAR(11) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### مرحله ۴: تنظیم config

فایل `includes/config.php` را ویرایش کنید:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'نام_دیتابیس');
define('DB_USER', 'نام_کاربر_دیتابیس');
define('DB_PASS', 'رمز_دیتابیس');
define('SITE_URL', 'https://yourdomain.com');
define('SITE_NAME', 'سیستم مدیریت بسته');
```

### مرحله ۵: ساخت پوشه uploads

```bash
mkdir uploads
chmod 755 uploads
```

### مرحله ۶: ساخت کاربر ادمین

به آدرس زیر بروید و کاربر ادمین بسازید:
```
https://yourdomain.com/setup-admin.php
```

**مهم:** بعد از ساخت ادمین، فایل `setup-admin.php` را حذف کنید.

---

## ساختار پروژه

```
public_html/
├── index.php                 # صفحه اصلی (رهگیری عمومی)
├── login.php                 # ورود ادمین
├── logout.php                # خروج ادمین
├── .htaccess                 # تنظیمات Apache
│
├── admin/                    # پنل مدیریت
│   ├── dashboard.php         # داشبورد
│   ├── register.php          # ثبت بسته
│   ├── deliver.php           # تحویل بسته
│   ├── delivered.php         # تحویل شده‌ها
│   ├── search.php            # جستجو
│   └── types.php             # مدیریت انواع
│
├── customer/                 # پنل مشتری
│   ├── register.php          # ثبت‌نام
│   ├── login.php             # ورود
│   ├── dashboard.php         # داشبورد
│   └── logout.php            # خروج
│
├── includes/                 # کلاس‌ها و توابع
│   ├── config.php            # تنظیمات
│   ├── Database.php          # کلاس دیتابیس
│   ├── Auth.php              # کلاس احراز هویت
│   ├── Security.php          # کلاس امنیت
│   ├── Package.php           # کلاس بسته
│   ├── PackageType.php       # کلاس نوع بسته
│   ├── Customer.php          # کلاس مشتری
│   ├── Shamsi.php            # کلاس تاریخ شمسی
│   │
│   └── templates/            # قالب‌ها
│       ├── header.php        # هدر
│       ├── sidebar.php       # سایدبار
│       └── footer.php        # فوتر
│
└── uploads/                  # آپلودها
    ├── packages/             # عکس بسته‌ها
    └── delivery/             # عکس تحویل
```

---

## ساختار دیتابیس

### جدول users (کاربران ادمین)

| فیلد | نوع | توضیحات |
|------|-----|---------|
| id | INT | شناسه (کلید اصلی) |
| username | VARCHAR(50) | نام کاربری (یکتا) |
| password | VARCHAR(255) | رمز عبور (هش شده) |
| full_name | VARCHAR(100) | نام کامل |
| created_at | TIMESTAMP | تاریخ ایجاد |

### جدول packages (بسته‌ها)

| فیلد | نوع | توضیحات |
|------|-----|---------|
| id | INT | شناسه |
| tracking_code | VARCHAR(20) | کد رهگیری (یکتا) |
| receiver_name | VARCHAR(200) | نام گیرنده |
| receiver_phone | VARCHAR(11) | شماره گیرنده |
| package_type_id | INT | نوع بسته |
| description | TEXT | توضیحات |
| image_path | VARCHAR(255) | مسیر عکس |
| status | ENUM | وضعیت (pending/delivered) |
| registered_by | INT | ثبت کننده |
| registered_at | TIMESTAMP | تاریخ ثبت |
| delivered_by | INT | تحویل دهنده |
| delivered_at | TIMESTAMP | تاریخ تحویل |
| delivery_method | ENUM | روش تحویل (signature/photo) |
| receiver_signature | LONGTEXT | امضای دیجیتال (Base64) |
| delivery_photo | VARCHAR(255) | عکس تحویل |
| receiver_national_code | VARCHAR(10) | کد ملی تحویل گیرنده |
| delivered_to_name | VARCHAR(200) | نام تحویل گیرنده |
| delivered_to_phone | VARCHAR(11) | شماره تحویل گیرنده |

### جدول package_types (انواع بسته)

| فیلد | نوع | توضیحات |
|------|-----|---------|
| id | INT | شناسه |
| name | VARCHAR(100) | نام نوع |
| created_at | TIMESTAMP | تاریخ ایجاد |

### جدول customers (مشتریان)

| فیلد | نوع | توضیحات |
|------|-----|---------|
| id | INT | شناسه |
| first_name | VARCHAR(100) | نام |
| last_name | VARCHAR(100) | نام خانوادگی |
| national_code | VARCHAR(10) | کد ملی (یکتا) |
| phone | VARCHAR(11) | شماره موبایل (یکتا) |
| password | VARCHAR(255) | رمز عبور (هش شده) |
| created_at | TIMESTAMP | تاریخ ایجاد |

---

## راهنمای استفاده

### ثبت بسته

#### روش ۱: ثبت گروهی (مثل اکسل)
1. به صفحه "ثبت بسته" بروید
2. نوع بسته را انتخاب کنید
3. در جدول، نام و شماره گیرنده را وارد کنید
4. با Enter به ردیف بعدی بروید
5. "ثبت همه" را بزنید

#### روش ۲: آپلود CSV
1. یک فایل CSV با دو ستون آماده کنید:
   ```csv
   نام گیرنده,شماره موبایل
   علی محمدی,09121234567
   رضا احمدی,09351234567
   ```
2. نوع بسته را انتخاب کنید
3. فایل را آپلود کنید

### کد رهگیری

کد رهگیری به صورت خودکار تولید می‌شود:
- فرمت: `YYYYMMDD` + `6 رقم رندوم`
- مثال: `14041109` + `847291` = `14041109847291`
- ۸ رقم اول تاریخ شمسی (سال/ماه/روز)
- ۶ رقم آخر رندوم

### تحویل بسته

#### روش امضا
1. بسته را جستجو کنید
2. "تحویل با امضا" را انتخاب کنید
3. اطلاعات تحویل گیرنده را وارد کنید
4. امضا را روی صفحه ثبت کنید

#### روش عکس
1. بسته را جستجو کنید
2. "تحویل با عکس" را انتخاب کنید
3. عکس را آپلود کنید یا با دوربین بگیرید

### تحویل گروهی
1. چند بسته را انتخاب کنید (چک‌باکس)
2. "تحویل انتخاب شده‌ها" را بزنید
3. روش تحویل را انتخاب کنید

---

## API و توابع

### کلاس Package

```php
// ثبت بسته جدید
Package::create([
    'tracking_code' => '14041109847291',
    'receiver_name' => 'علی محمدی',
    'receiver_phone' => '09121234567',
    'package_type_id' => 1,
    'registered_by' => 1
]);

// تحویل بسته
Package::deliver($packageId, $deliveredBy, [
    'method' => 'signature',
    'signature' => 'base64...',
    'receiver_name' => 'رضا احمدی'
]);

// جستجو
Package::search('علی');
Package::searchForDelivery('09121234567', $typeId, 'pending');

// آمار
Package::getStats();
Package::getLast30DaysStats();
```

### کلاس Shamsi

```php
// تاریخ شمسی امروز
Shamsi::date();              // 1404/11/09

// تاریخ و ساعت
Shamsi::datetime();          // 1404/11/09 14:30

// تبدیل تاریخ
Shamsi::date('2025-01-28');  // 1404/11/09

// تولید کد رهگیری
Shamsi::generateTrackingCode();  // 14041109847291
```

### کلاس Security

```php
// تولید توکن CSRF
$token = Security::generateCSRFToken();

// اعتبارسنجی توکن
Security::validateCSRFToken($_POST['csrf_token']);

// پاکسازی ورودی
Security::escape($input);
Security::sanitize($input);
```

---

## امنیت

### محافظت‌های فعال

| نوع | توضیحات |
|-----|---------|
| CSRF Protection | توکن یکتا برای هر فرم |
| XSS Protection | پاکسازی تمام ورودی‌ها |
| SQL Injection | استفاده از Prepared Statements |
| Password Hashing | bcrypt با cost=12 |
| Session Security | regenerate_id بعد از لاگین |

### توصیه‌ها

1. **SSL Certificate** نصب کنید
2. فایل‌های `debug*.php` و `setup-admin.php` را حذف کنید
3. پوشه `includes` را با `.htaccess` محافظت کنید
4. رمز دیتابیس قوی انتخاب کنید

### محافظت از پوشه includes

در `includes/.htaccess`:
```apache
Deny from all
```

---

## سفارشی‌سازی

### تغییر رنگ‌ها

رنگ‌های اصلی در فایل‌های PHP با Tailwind CSS:

| رنگ | کلاس | استفاده |
|-----|------|---------|
| خاکستری تیره | slate-800, slate-900 | سایدبار |
| طلایی | amber-500, amber-600 | دکمه‌ها و تاکید |
| سبز | emerald-500 | موفقیت |
| قرمز | red-500 | خطا |

### تغییر لوگو

در `includes/templates/sidebar.php`:
```php
<div class="w-12 h-12 bg-gradient-to-br from-amber-400 to-amber-600 rounded-xl flex items-center justify-center">
    <!-- آیکون یا لوگو -->
</div>
```

### افزودن صفحه جدید

1. فایل PHP جدید در `admin/` بسازید
2. در ابتدا این فایل‌ها را include کنید:
```php
<?php
session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/Auth.php';
require_once __DIR__ . '/../includes/Security.php';
require_once __DIR__ . '/../includes/Shamsi.php';

Auth::requireLogin();
```
3. لینک را به `sidebar.php` اضافه کنید

---

## عیب‌یابی

### خطای 500
- فایل `config.php` را بررسی کنید
- اطلاعات دیتابیس را چک کنید
- فایل `debug.php` را آپلود و اجرا کنید

### تاریخ اشتباه
- Timezone در `Shamsi.php` باید `Asia/Tehran` باشد
- فایل `Shamsi.php` را دوباره آپلود کنید

### آپلود فایل کار نمی‌کند
- پوشه `uploads` با permission `755` بسازید
- `upload_max_filesize` در php.ini را بررسی کنید

---

## نسخه‌ها

| نسخه | تاریخ | تغییرات |
|------|-------|---------|
| 1.0.0 | 1404/11/09 | انتشار اولیه |

---

## پشتیبانی

برای گزارش مشکل یا پیشنهاد، تیکت ارسال کنید.

---

## لایسنس

این پروژه تحت لایسنس MIT منتشر شده است.

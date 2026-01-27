<?php
// این فایل فقط شامل توابع کمکی است (تاریخ شمسی و هر تابع دیگری که بعداً اضافه می‌کنیم)

// تبدیل تاریخ میلادی به شمسی (نسخه دستی و بدون نیاز به لایبرری)
function gregorian_to_jalali($gy, $gm, $gd) {
    $g_d_m = [0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334];
    $jy = ($gy <= 1600) ? 0 : 979;
    $gy -= ($gy <= 1600) ? 621 : 1600;
    $gy2 = ($gm > 2) ? ($gy + 1) : $gy;
    $days = (365 * $gy) + ((int)(($gy2 + 3) / 4)) - ((int)(($gy2 + 99) / 100))
          + ((int)(($gy2 + 399) / 400)) - 80 + $gd + $g_d_m[$gm - 1];
    $jy += 33 * ((int)($days / 12053));
    $days %= 12053;
    $jy += 4 * ((int)($days / 1461));
    $days %= 1461;
    if ($days > 365) {
        $jy += (int)(($days - 1) / 365);
        $days = ($days - 1) % 365;
    }
    $jm = ($days < 186) ? 1 + (int)($days / 31) : 7 + (int)(($days - 186) / 30);
    $jd = 1 + (($days < 186) ? ($days % 31) : (($days - 186) % 30));
    return [$jy, $jm, $jd];
}

// فرمت کردن تاریخ به شکل شمسی (مثل ۱۴۰۴/۰۵/۱۸)
function toJalali($date_str) {
    if (empty($date_str) || $date_str === '0000-00-00') {
        return 'نامشخص';
    }

    $timestamp = strtotime($date_str);
    if ($timestamp === false) {
        return 'نامشخص';
    }

    list($gy, $gm, $gd) = explode('-', date('Y-m-d', $timestamp));
    list($jy, $jm, $jd) = gregorian_to_jalali((int)$gy, (int)$gm, (int)$gd);

    return sprintf("%04d/%02d/%02d", $jy, $jm, $jd);
}
// تابع تبدیل شمسی به میلادی (برای فیلتر تاریخ)
function jalali_to_gregorian($jy, $jm, $jd) {
    $jy += 1595;
    $days = -355668 + (365 * $jy) + ((int)($jy / 33) * 8) + ((int)((($jy % 33) + 3) / 4)) + $jd + (($jm < 7) ? ($jm - 1) * 31 : (($jm - 7) * 30) + 186);
    $gy = 400 * ((int)($days / 146097));
    $days %= 146097;
    if ($days > 36524) {
        $gy += 100 * ((int)(--$days / 36524));
        $days %= 36524;
        if ($days >= 365) $days++;
    }
    $gy += 4 * ((int)($days / 1461));
    $days %= 1461;
    if ($days > 365) {
        $gy += (int)(($days - 1) / 365);
        $days = ($days - 1) % 365;
    }
    $gd = $days + 1;
    $sal_a = [0, 31, (($gy % 4 === 0 && $gy % 100 !== 0) || ($gy % 400 === 0)) ? 29 : 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
    for ($gm = 0; $gm < 13; $gm++) {
        $v = $sal_a[$gm];
        if ($gd <= $v) break;
        $gd -= $v;
    }
    return [$gy, $gm, $gd];
}

// تابع تبدیل تاریخ شمسی کامل به میلادی (ورودی مثل ۱۴۰۴/۰۵/۱۸)
function toGregorian($jalali_date) {
    if (empty($jalali_date)) return 'نامشخص';
    list($jy, $jm, $jd) = explode('/', $jalali_date);
    list($gy, $gm, $gd) = jalali_to_gregorian((int)$jy, (int)$jm, (int)$jd);
    return sprintf("%04d-%02d-%02d", $gy, $gm, $gd);
}
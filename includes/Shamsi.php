<?php
/**
 * کلاس تبدیل تاریخ شمسی (جلالی) - الگوریتم اصلاح شده
 * با timezone تهران
 */
class Shamsi {
    
    /**
     * تنظیم timezone تهران
     */
    public static function setTimezone(): void {
        date_default_timezone_set('Asia/Tehran');
    }
    
    /**
     * تبدیل میلادی به شمسی - الگوریتم صحیح
     */
    public static function toShamsi($timestamp = null): array {
        self::setTimezone();
        
        if ($timestamp === null) {
            $timestamp = time();
        }
        
        if (is_string($timestamp)) {
            $timestamp = strtotime($timestamp);
        }
        
        $gy = (int)date('Y', $timestamp);
        $gm = (int)date('m', $timestamp);
        $gd = (int)date('d', $timestamp);
        
        // الگوریتم تبدیل میلادی به شمسی
        $g_a = [0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334];
        
        if ($gy > 1600) {
            $jy = 979;
            $gy -= 1600;
        } else {
            $jy = 0;
            $gy -= 621;
        }
        
        $leap = ($gy % 4 == 0 && $gy % 100 != 0) || ($gy % 400 == 0);
        $gy2 = ($gm > 2) ? ($gy + 1) : $gy;
        $days = (365 * $gy) + (int)(($gy2 + 3) / 4) - (int)(($gy2 + 99) / 100) + (int)(($gy2 + 399) / 400) - 80 + $gd + $g_a[$gm - 1];
        
        $jy += 33 * (int)($days / 12053);
        $days %= 12053;
        
        $jy += 4 * (int)($days / 1461);
        $days %= 1461;
        
        if ($days > 365) {
            $jy += (int)(($days - 1) / 365);
            $days = ($days - 1) % 365;
        }
        
        $jm = ($days < 186) ? 1 + (int)($days / 31) : 7 + (int)(($days - 186) / 30);
        $jd = 1 + (($days < 186) ? ($days % 31) : (($days - 186) % 30));
        
        return [
            'year' => $jy,
            'month' => $jm,
            'day' => $jd,
            'hour' => (int)date('H', $timestamp),
            'minute' => (int)date('i', $timestamp),
            'second' => (int)date('s', $timestamp)
        ];
    }
    
    /**
     * فرمت تاریخ شمسی
     */
    public static function format($timestamp = null, string $format = 'Y/m/d'): string {
        $date = self::toShamsi($timestamp);
        
        $result = $format;
        $result = str_replace('Y', $date['year'], $result);
        $result = str_replace('m', str_pad($date['month'], 2, '0', STR_PAD_LEFT), $result);
        $result = str_replace('d', str_pad($date['day'], 2, '0', STR_PAD_LEFT), $result);
        $result = str_replace('H', str_pad($date['hour'], 2, '0', STR_PAD_LEFT), $result);
        $result = str_replace('i', str_pad($date['minute'], 2, '0', STR_PAD_LEFT), $result);
        $result = str_replace('s', str_pad($date['second'], 2, '0', STR_PAD_LEFT), $result);
        
        return $result;
    }
    
    /**
     * تاریخ و ساعت کامل شمسی
     */
    public static function datetime($timestamp = null): string {
        return self::format($timestamp, 'Y/m/d H:i');
    }
    
    /**
     * فقط تاریخ شمسی
     */
    public static function date($timestamp = null): string {
        return self::format($timestamp, 'Y/m/d');
    }
    
    /**
     * فقط ساعت
     */
    public static function time($timestamp = null): string {
        return self::format($timestamp, 'H:i');
    }
    
    /**
     * تولید کد رهگیری با تاریخ شمسی
     * فرمت: 14041108 + 6 رقم رندوم = 14 رقم
     */
    public static function generateTrackingCode(): string {
        $date = self::toShamsi();
        $year = $date['year'];
        $month = str_pad($date['month'], 2, '0', STR_PAD_LEFT);
        $day = str_pad($date['day'], 2, '0', STR_PAD_LEFT);
        $random = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
        
        return $year . $month . $day . $random;
    }
    
    /**
     * نام ماه شمسی
     */
    public static function monthName(int $month): string {
        $months = [
            1 => 'فروردین', 2 => 'اردیبهشت', 3 => 'خرداد',
            4 => 'تیر', 5 => 'مرداد', 6 => 'شهریور',
            7 => 'مهر', 8 => 'آبان', 9 => 'آذر',
            10 => 'دی', 11 => 'بهمن', 12 => 'اسفند'
        ];
        return $months[$month] ?? '';
    }
    
    /**
     * نام روز هفته
     */
    public static function dayName($timestamp = null): string {
        self::setTimezone();
        if ($timestamp === null) {
            $timestamp = time();
        }
        if (is_string($timestamp)) {
            $timestamp = strtotime($timestamp);
        }
        
        $days = ['یکشنبه', 'دوشنبه', 'سه‌شنبه', 'چهارشنبه', 'پنجشنبه', 'جمعه', 'شنبه'];
        return $days[date('w', $timestamp)];
    }
    
    /**
     * تاریخ کامل با نام روز و ماه
     */
    public static function fullDate($timestamp = null): string {
        $date = self::toShamsi($timestamp);
        $dayName = self::dayName($timestamp);
        $monthName = self::monthName($date['month']);
        
        return $dayName . ' ' . $date['day'] . ' ' . $monthName . ' ' . $date['year'];
    }
}

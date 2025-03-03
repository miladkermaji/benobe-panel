<?php

namespace App\Helpers;

use Carbon\Carbon;
use Morilog\Jalali\Jalalian;

class JalaliHelper
{
 public static function gregorianToJalali($gy, $gm, $gd)
 {
  // تبدیل به عدد و چک کردن null یا غیرعددی بودن
  $gy = is_numeric($gy) ? (int) $gy : 0;
  $gm = is_numeric($gm) ? (int) $gm : 1;
  $gd = is_numeric($gd) ? (int) $gd : 1;

  $g_d_m = [0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334];
  $jy = ($gy <= 1600) ? 0 : 979;
  $gy -= ($gy <= 1600) ? 621 : 1600;
  $gy2 = ($gm > 2) ? ($gy + 1) : $gy;
  $days = (365 * $gy) + floor($gy / 4) - floor($gy / 100) + floor($gy2 / 400) + $g_d_m[$gm - 1] + $gd - 1;
  $jy += 33 * floor($days / 12053);
  $days %= 12053;
  $jy += 4 * floor($days / 1461);
  $days %= 1461;
  if ($days > 365) {
   $jy += floor(($days - 1) / 365);
   $days = ($days - 1) % 365;
  }
  $jm = ($days < 186) ? 1 + floor($days / 31) : 7 + floor(($days - 186) / 30);
  $jd = 1 + (($days < 186) ? ($days % 31) : (($days - 186) % 30));
  return [$jy, $jm, $jd];
 }

 public static function toJalaliDate($gregorianDate)
 {
  if (!$gregorianDate)
   return '-';
  $date = Carbon::parse($gregorianDate);
  $jDate = Jalalian::fromCarbon($date);
  return $jDate->format('Y-m-d');
 }

 public static function toJalaliDateTime($gregorianDateTime)
 {
  if (!$gregorianDateTime)
   return '-';
  $date = Carbon::parse($gregorianDateTime);
  $jDate = Jalalian::fromCarbon($date);
  return $jDate->format('Y-m-d H:i');
 }

 public static function parsePersianTextDate($persianTextDate)
 {
  if (!$persianTextDate)
   return null;

  $persianMonths = [
   'فروردین' => '01',
   'اردیبهشت' => '02',
   'خرداد' => '03',
   'تیر' => '04',
   'مرداد' => '05',
   'شهریور' => '06',
   'مهر' => '07',
   'آبان' => '08',
   'آذر' => '09',
   'دی' => '10',
   'بهمن' => '11',
   'اسفند' => '12'
  ];

  $parts = explode(' ', trim($persianTextDate));
  if (count($parts) !== 3)
   return null;

  $day = str_pad($parts[0], 2, '0', STR_PAD_LEFT);
  $monthName = $parts[1];
  $year = $parts[2];

  $month = $persianMonths[$monthName] ?? null;
  if (!$month)
   return null;

  $jalaliDate = "$year-$month-$day";
  return Jalalian::fromFormat('Y-m-d', $jalaliDate)->toCarbon();
 }
}
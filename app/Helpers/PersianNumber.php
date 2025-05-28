<?php

namespace App\Helpers;

class PersianNumber
{
    private static $persianNumbers = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
    private static $englishNumbers = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

    public static function convertToPersian($string)
    {
        if (is_null($string)) {
            return null;
        }
        return str_replace(self::$englishNumbers, self::$persianNumbers, $string);
    }

    public static function convertToEnglish($string)
    {
        if (is_null($string)) {
            return null;
        }
        return str_replace(self::$persianNumbers, self::$englishNumbers, $string);
    }
}

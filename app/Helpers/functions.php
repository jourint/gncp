<?php

if (!function_exists('declension_pairs')) {
    /**
     * Склонение слова "пара" в зависимости от числа
     */
    function declension_pairs(int $number): string
    {
        $number = abs($number);
        $mod10 = $number % 10;
        $mod100 = $number % 100;

        $suffix = 'пар'; // По умолчанию для 0, 5-9 и 11-14

        if ($mod100 < 11 || $mod100 > 14) {
            if ($mod10 === 1) $suffix = 'пара';
            if ($mod10 >= 2 && $mod10 <= 4) $suffix = 'пары';
        }

        return "{$number} {$suffix}";
    }
}

if (! function_exists('format_phone')) {
    function format_phone(?string $phone): string
    {
        if (!$phone) return '';

        return preg_replace(
            '/(\d{2})(\d{3})(\d{3})(\d{2})(\d{2})/',
            '+$1 ($2) $3-$4-$5',
            $phone
        );
    }
}

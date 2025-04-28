<?php

namespace App\Helpers;

class CustomHelper
{
    public static function formatCurrency($amount)
    {
        return 'Rs. ' . number_format($amount, 2);
    }

    public static function truncateText($text, $limit = 50)
    {
        return strlen($text) > $limit ? substr($text, 0, $limit) . '...' : $text;
    }
}

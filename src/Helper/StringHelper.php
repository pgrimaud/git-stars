<?php

declare(strict_types=1);

namespace App\Helper;

class StringHelper
{
    public static function slugify(string $text, bool $language = false): string
    {
        // replace special char for languages
        if ($language) {
            $text = str_replace(['#', '++', '*'], ['-sharp', '-plus-plus', '-star'], $text);
        }

        // replace non letter or digits by -
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);

        // transliterate
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

        // remove unwanted characters
        $text = preg_replace('~[^-\w]+~', '', $text);

        // trim
        $text = trim($text, '-');

        // remove duplicate -
        $text = preg_replace('~-+~', '-', $text);

        // lowercase
        $text = strtolower($text);

        if (empty($text)) {
            return 'n-a';
        }

        return $text;
    }
}
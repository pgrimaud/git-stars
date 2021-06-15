<?php

declare(strict_types=1);

namespace App\Utils;

class LanguageHelper
{
    public static function createColor(): string
    {
        return '#' . substr(bin2hex(random_bytes(5)), 0, 6);
    }
}

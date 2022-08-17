<?php

declare(strict_types=1);

namespace App\Utils;

class PaginateHelper
{
    public static function create(int $currentPage, int $maxResults, int $nbPerPage = 25): array
    {
        $totalPages = (int) ceil($maxResults / $nbPerPage);

        $range = match (true) {
            ($currentPage === 1)           => range(1, ($totalPages < 3) ? $totalPages : 3),
            ($currentPage === $totalPages) => range($totalPages <= 2 ? 1 : $currentPage - 2, $currentPage),
            default                        => range($currentPage                        - 1, $currentPage + 1),
        };

        return [
            'current'     => $currentPage,
            'total'       => $totalPages,
            'hasNext'     => $currentPage < $totalPages,
            'hasPrevious' => $currentPage > 1,
            'range'       => $range,
        ];
    }
}

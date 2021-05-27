<?php

declare(strict_types=1);

namespace App\Utils;

class PaginateHelper
{
    public static function create(int $currentPage, int $maxResults, int $nbPerPage = 25): array
    {
        $totalPages = (int) ceil($maxResults / $nbPerPage);

        $range = match (true) {
            1 === $currentPage           => range(1, 3),
            $currentPage === $totalPages => range($currentPage - 2, $currentPage),
            default                        => range($currentPage - 1, $currentPage + 1),
        };

        return [
            'current'   => $currentPage,
            'total'     => $totalPages,
            'hasNext'   => $currentPage < $totalPages,
            'hasBefore' => $currentPage > 1,
            'range'     => $range,
        ];
    }
}

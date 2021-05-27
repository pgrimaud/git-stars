<?php

declare(strict_types=1);

namespace App\Utils;

class PaginateHelper
{
    public static function create(int $currentPage, int $maxResults, int $nbPerPage = 25): array
    {
        $hasPrevious = true;
        $hasNext     = true;

        $totalPages = ceil($maxResults / $nbPerPage);


        if ($currentPage == 1) {
            $hasPrevious  = false;
            $nextPage     = $currentPage + 1;
            $nextPagePlus = $currentPage + 2;

            return [
                'currentPage'  => $currentPage,
                'nextPage'     => $nextPage,
                'nextPagePlus' => $nextPagePlus,
                'resPerPage'   => $nbPerPage,
                'totalPages'   => $totalPages,
            ];

        } else if ($currentPage == $totalPages) {
            $hasNext          = false;
            $previousPage     = $currentPage - 1;
            $previousPagePlus = $currentPage - 2;

            return [
                'currentPage'      => $currentPage,
                'previousPage'     => $previousPage,
                'previousPagePlus' => $previousPagePlus,
                'resPerPage'       => $nbPerPage,
                'totalPages'       => $totalPages,
            ];

        } else {
            $previousPage = $currentPage - 1;
            $nextPage     = $currentPage + 1;

            return [
                'currentPage' => $currentPage,
                'previous'    => $previousPage,
                'nextPage'    => $nextPage,
                'resPerPage'  => $nbPerPage,
                'totalPages'  => $totalPages,
            ];
        }

    }
}
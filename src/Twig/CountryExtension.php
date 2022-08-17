<?php

declare(strict_types=1);

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class CountryExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('get_flag', [$this, 'getFlag']),
        ];
    }

    public function getFlag(?string $isoCode): ?string
    {
        /* @phpstan-ignore-next-line */
        return country((string) $isoCode)->getEmoji();
    }
}

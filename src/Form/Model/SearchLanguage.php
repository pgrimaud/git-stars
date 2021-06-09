<?php

declare(strict_types=1);

namespace App\Form\Model;

class SearchLanguage

{
    public string $language = '';

    public function __toString(): string
    {
        return $this->language;
    }
}

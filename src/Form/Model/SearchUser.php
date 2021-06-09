<?php

declare(strict_types=1);

namespace App\Form\Model;

class SearchUser

{
    public string $username = '';

    public function __toString(): string
    {
        return $this->username;
    }
}

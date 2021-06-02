<?php

declare(strict_types=1);

namespace App\Form\Model;

class Search
{
    public string $username = '';

//    public function getUsername(): ?string
//    {
//        return $this->username;
//    }
//
//    public function setUsername(string $username): void
//    {
//        $this->username = $username;
//    }
//
    public function __toString(): string
    {
        return $this->username;
    }
}

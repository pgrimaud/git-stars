<?php

declare(strict_types=1);

namespace App\Message;

class ManualUpdateUser
{
    public function __construct(private int $id, private string $token)
    {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getToken(): string
    {
        return $this->token;
    }
}

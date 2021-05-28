<?php

declare(strict_types=1);

namespace App\Message;

class UpdateUser
{
    public function __construct(private int $id)
    {
    }

    public function getId(): int
    {
        return $this->id;
    }
}

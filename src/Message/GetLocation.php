<?php

namespace App\Message;

class GetLocation
{
    public function __construct(private int $id, private string $location)
    {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getLocation(): string
    {
        return $this->location;
    }
}

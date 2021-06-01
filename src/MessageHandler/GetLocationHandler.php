<?php

namespace App\MessageHandler;

use App\Message\GetLocation;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class GetLocationHandler implements MessageHandlerInterface
{
    public function __invoke(GetLocation $message): void
    {
        // ... do some work - like sending an SMS message!
    }
}

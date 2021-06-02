<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\GetLocation;
use App\Service\GeocodeService;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class GetLocationHandler implements MessageHandlerInterface
{
    public function __construct(private GeocodeService $geocodeService)
    {
    }

    public function __invoke(GetLocation $message): void
    {
        $this->geocodeService->update($message->getId(), $message->getLocation());
    }
}

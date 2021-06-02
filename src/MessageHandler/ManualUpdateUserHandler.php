<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\ManualUpdateUser;
use App\Service\UserService;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class ManualUpdateUserHandler implements MessageHandlerInterface
{
    public function __construct(private UserService $userService)
    {
    }

    public function __invoke(ManualUpdateUser $updateUser): void
    {
        $this->userService->update($updateUser->getId(), $updateUser->getToken());
    }
}

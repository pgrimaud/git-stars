<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\UpdateUser;
use App\Service\UserService;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class UpdateUserHandler implements MessageHandlerInterface
{
    public function __construct(private UserService $userService)
    {
    }

    public function __invoke(UpdateUser $updateUser): void
    {
        $this->userService->update($updateUser->getId());
    }
}

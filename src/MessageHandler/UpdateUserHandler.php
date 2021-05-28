<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\UpdateUser;
use App\Service\UserService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class UpdateUserHandler implements MessageHandlerInterface
{
    public function __construct(
        private EntityManagerInterface $objectManager,
        private MessageBusInterface $bus,
        private UserService $userService
    ) {
    }

    public function __invoke(UpdateUser $updateUser): void
    {
        $this->userService->update(intval($updateUser->getId()));
    }
}

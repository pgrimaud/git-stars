<?php

declare(strict_types=1);

namespace App\Controller;

use App\Message\UpdateUser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

class AppController extends AbstractController
{
    #[Route('/', name: 'app_index', methods: ['GET'])]
    public function index(MessageBusInterface $bus): Response
    {
//        for ($i = 1; $i < 1000; ++$i) {
//            $bus->dispatch(
//                new UpdateUser($i)
//            );
//        }

        return $this->render('app/index.html.twig');
    }
}

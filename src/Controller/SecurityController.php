<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;

class SecurityController extends AbstractController
{
    #[Route('/callback', name: 'security_callback', methods: ['GET'])]
    public function callback(Request $request): void
    {
    }

    #[Route('/logout', name: 'security_logout', methods: ['GET'])]
    public function logout()
    {
        $session = new Session();
        $session->invalidate();

        return $this->redirectToRoute('app_index');
    }
}
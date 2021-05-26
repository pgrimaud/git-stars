<?php

namespace App\Controller;

use App\Repository\UserLanguageRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    public function __construct(private UserRepository $userRepository)
    {
    }

    #[Route('/user', name: 'user_index')]
    public function index(): Response
    {
        return $this->render('user/index.html.twig', [
            'controller_name' => 'UserController',
        ]);
    }

    #[Route('/user/{username}', name: 'user_show')]
    public function show(string $username, UserLanguageRepository $userLanguageRepository): Response
    {
        $user = $this->userRepository->findOneBy(['username' => $username]);
        $userLanguages = $userLanguageRepository->findLanguageByUsers($user);
//        dd($userLanguages);
        return $this->render('user/show.html.twig', [
            'username' => $username,
            'userLanguages' => $userLanguages,
        ]);
    }
}

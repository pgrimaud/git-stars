<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserLanguageRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    public function __construct(private UserRepository $userRepository)
    {
    }

    #[Route('/user', name: 'user_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('user/index.html.twig', [
            'controller_name' => 'UserController',
        ]);
    }

    #[Route('/user/{username}', name: 'user_show', methods: ['GET'])]
    public function show(UserLanguageRepository $userLanguageRepository, string $username): Response
    {
        $user = $this->userRepository->findOneBy(['username' => $username]);

        if (!$user instanceof User) {
            throw new NotFoundHttpException('User not found');
        }

        $userLanguages = $userLanguageRepository->findLanguageByUsers($user);
//        dd($userLanguages);
        return $this->render('user/show.html.twig', [
            'username'      => $username,
            'userLanguages' => $userLanguages,
        ]);
    }
}

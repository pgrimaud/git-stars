<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Form\Model\Search;
use App\Form\SearchType;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AppController extends AbstractController
{
    #[Route('/', name: 'app_index', methods: ['GET', 'POST'])]
    public function index(Request $request, UserRepository $userRepository): Response
    {
        $searchError = null;

        $search = new Search();

        $form = $this->createForm(SearchType::class, $search);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $search = $form->getData()->username;
            $user   = $userRepository->findOneBy(['username' => $search]);

            if (!$user instanceof User) {
                $searchError = 'User ' . $search . ' was not found';
            } else {
                return $this->redirectToRoute('user_show', [
                    'username' => $search,
                ]);
            }
        }

        return $this->render('app/index.html.twig', [
            'search_form'  => $form->createView(),
            'search_error' => $searchError,
        ]);
    }
}

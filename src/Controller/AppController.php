<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Form\Model\Search;
use App\Form\SearchType;
use App\Repository\UserRepository;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AppController extends AbstractController
{
    #[Route('/', name: 'app_index', methods: ['GET', 'POST'])]
    public function index(
        Request $request,
        UserRepository $userRepository,
        UserService $userService
    ): Response {
        $searchError = null;

        $search = new Search();

        $form = $this->createForm(SearchType::class, $search);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $search = strip_tags($form->getData()->username);
            $user   = $userRepository->findOneBy(['username' => $search]);

            if (!$user instanceof User) {
                if (!$this->getUser()) {
                    $loginLink   = $this->generateUrl('hwi_oauth_service_redirect', ['service' => 'github']);
                    $searchError = 'User ' . $search .
                        ' was not found in Git Stars. Please <a href="' . $loginLink . '">login</a> to import it.';
                } else {
                    // @phpstan-ignore-next-line
                    $newUser = $userService->partialFetchUser($search, $this->getUser()->getAccessToken());

                    if ($newUser instanceof User) {
                        return $this->redirectToRoute('user_show', [
                            'username' => $newUser->getUsername(),
                        ]);
                    } else {
                        $searchError = 'User ' . $search . ' was not found in GitHub';
                    }
                }
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

    #[Route('/about', name: 'app_about', methods: ['GET'])]
    public function about(): Response
    {
        return $this->render('app/about.html.twig');
    }
}

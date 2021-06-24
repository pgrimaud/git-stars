<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Form\Model\SearchUser;
use App\Form\SearchUserType;
use App\Repository\LanguageRepository;
use App\Repository\UserRepository;
use App\Service\RankingService;
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
        LanguageRepository $languageRepository,
        RankingService $rankingService,
        UserService $userService
    ): Response {
        $searchError = null;

        $search           = new SearchUser();
        $search->username = $request->get('search') ?: '';

        $form = $this->createForm(SearchUserType::class, $search);
        $form->handleRequest($request);

        if (($form->isSubmitted() && $form->isValid()) || ($this->getUser() instanceof User && $request->get('search'))) {
            $search = strip_tags($form->getData()->username);
            $user   = $userRepository->findOneBy(['username' => $search]);

            if (!$user instanceof User) {
                if (!$this->getUser()) {
                    $loginLink = $this->generateUrl('hwi_oauth_service_redirect', ['service' => 'github']);
                    $loginLink .= '?_destination=/%3Fsearch=' . $search;
                    $searchError = 'User ' . $search .
                        ' was not found in Git Stars. Please <a href="' . $loginLink . '">login</a> to import it. 🤓';
                } else {
                    // @phpstan-ignore-next-line
                    $newUser = $userService->partialFetchUser($search, $this->getUser()->getAccessToken());

                    if ($newUser instanceof User) {
                        return $this->redirectToRoute('user_show', [
                            'username' => $newUser->getUsername(),
                        ]);
                    } else {
                        $searchError = 'User ' . $search . ' was not found in GitHub. 🥺';
                    }
                }
            } else {
                return $this->redirectToRoute('user_show', [
                    'username' => $search,
                ]);
            }
        }

        $topUsers     = $rankingService->getTopUsers(0);
        $topCorps     = $rankingService->getTopUsers(1);
        $topToday     = $userRepository->getTodayTop();
        $topLanguages = $languageRepository->getTopLanguages(3);

        $totalUsers = $userRepository->count([]);

        return $this->render('app/index.html.twig', [
            'search_form'  => $form->createView(),
            'search_error' => $searchError,
            'topUsers'     => $topUsers,
            'topCorps'     => $topCorps,
            'topToday'     => $topToday,
            'topLanguages' => $topLanguages,
            'totalUsers'   => $totalUsers,
        ]);
    }

    #[Route('/about', name: 'app_about', methods: ['GET'])]
    public function about(): Response
    {
        return $this->render('app/about.html.twig');
    }

    #[Route('/count-users.json', name: 'app_index_count', methods: ['GET'])]
    public function countUsers(UserRepository $userRepository): Response
    {
        return $this->json(
            [
                'frames' => [
                    [
                        'index' => 0,
                        'text'  => number_format($userRepository->count([]), 0, '', ' '),
                        'icon'  => null,
                    ],
                ],
            ]
        );
    }
}

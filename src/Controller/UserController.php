<?php

namespace App\Controller;

use App\Entity\User;
use App\Message\ManualUpdateUser;
use App\Repository\UserLanguageRepository;
use App\Repository\UserRepository;
use App\Utils\PaginateHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    public function __construct(private UserRepository $userRepository)
    {
    }

    #[Route('/users/{page}', name: 'user_index', methods: ['GET'])]
    public function index(int $page = 1): Response
    {
        $totalUsers = $this->userRepository->totalPages();
        $paginate   = PaginateHelper::create($page, $totalUsers);

        if ($page > $paginate['total'] || $page <= 0) {
            throw new NotFoundHttpException('Page not found');
        }

        $start = ($page - 1) * 25;

        $users = $this->userRepository->findSomeUsers($start);

        return $this->render('user/index.html.twig', [
            'users'    => $users,
            'paginate' => $paginate,
        ]);
    }

    #[Route('/user/{username}', name: 'user_show', requirements: ['username' => '[a-zA-Z0-9\-\_]+'], methods: ['GET'])]
    public function show(UserLanguageRepository $userLanguageRepository, string $username): Response
    {
        $user = $this->userRepository->findOneBy(['username' => $username]);

        if (!$user instanceof User) {
            throw new NotFoundHttpException('User not found');
        }

        $userLanguages = $userLanguageRepository->findLanguageByUsers($user);

        return $this->render('user/show.html.twig', [
            'user'          => $user,
            'userLanguages' => $userLanguages,
        ]);
    }

    #[Route('/user/{username}/update', name: 'user_update', requirements: ['username' => '[a-zA-Z0-9\-\_]+'], methods: ['GET'])]
    public function update(MessageBusInterface $bus, string $username): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('hwi_oauth_service_redirect', ['service' => 'github']);
        } else {
            $user = $this->userRepository->findOneBy(['username' => $username]);

            if (!$user instanceof User) {
                throw new NotFoundHttpException('User not found');
            }

            if ($user->getStatus() === User::STATUS_IDLE) {
                $user->setStatus($user::STATUS_RUNNING);
                $this->getDoctrine()->getManager()->persist($user);
                $this->getDoctrine()->getManager()->flush();

                $bus->dispatch(
                // @phpstan-ignore-next-line
                    new ManualUpdateUser($user->getGithubId(), $this->getUser()->getAccessToken())
                );
            }
        }

        return $this->redirectToRoute('user_show', ['username' => $user->getUsername()]);
    }

    #[Route('/user/{username}/status', name: 'user_status', requirements: ['username' => '[a-zA-Z0-9\-\_]+'], methods: ['GET'])]
    public function status(string $username): Response
    {
        $user = $this->userRepository->findOneBy(['username' => $username]);

        if (!$user instanceof User) {
            throw new NotFoundHttpException('User not found');
        }

        return $this->json([
            'status' => $user->getStatus(),
        ]);
    }
}

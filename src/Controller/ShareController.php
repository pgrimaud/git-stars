<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\RankingService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[Route('/share')]
class ShareController extends AbstractController
{
    #[Route('/embed/{username}.svg', name: 'share_embed', requirements: ['username' => '[a-zA-Z0-9\-\_]+'], methods: ['GET'])]
    public function index(
        UserRepository $userRepository,
        RankingService $rankingService,
        Filesystem $filesystem,
        string $username
    ): Response {
        $user = $userRepository->findOneBy(['username' => $username]);

        if (!$user instanceof User) {
            throw new NotFoundHttpException('User not found');
        }

        $imagePath = __DIR__ . '/../../public/avatars/' . $user->getGithubId() . '.jpg';

        if (!$filesystem->exists($imagePath)) {
            $imageData = (string) file_get_contents('https://avatars.githubusercontent.com/u/' . $user->getGithubId() . '?s=150&v=4');
            $filesystem->appendToFile($imagePath, $imageData);
        }

        $picture = $this->generateUrl('app_index', [], UrlGeneratorInterface::ABSOLUTE_URL) . 'avatars/' . $user->getGithubId() . '.jpg';

        $response = new Response(
            $this->renderView('partials/sharer-svg.html.twig', [
                'user'          => $user,
                'globalRanking' => $rankingService->getRankingGlobal($user),
                'totalUsers'    => $userRepository->countUsers(),
                'topLanguage'   => $rankingService->getTopLanguage($user),
                'picture'       => $picture,
            ])
        );

        $response->headers->set('Content-Type', 'image/svg+xml; charset=utf-8');

        return $response;
    }
}

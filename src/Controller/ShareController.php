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

#[Route('/share')]
class ShareController extends AbstractController
{
    public function __construct(
        private UserRepository $userRepository,
        private RankingService $rankingService,
        private Filesystem $filesystem
    ) {
    }

    #[Route('/embed/{username}.svg', name: 'share_embed', requirements: ['username' => '[a-zA-Z0-9\-\_]+'], methods: ['GET'])]
    public function embedSvg(string $username): Response
    {
        $user = $this->userRepository->findOneBy(['username' => $username]);

        if (!$user instanceof User) {
            throw new NotFoundHttpException('User not found');
        }

        $response = new Response(
            $this->renderView('partials/sharer-svg.html.twig', [
                'user'          => $user,
                'globalRanking' => $this->rankingService->getRankingGlobal($user),
                'totalUsers'    => $this->userRepository->countUsers(),
                'topLanguage'   => $this->rankingService->getTopLanguage($user),
                'picture'       => base64_encode($this->fetchAvatarPicture($user)),
            ])
        );

        $response->headers->set('Content-Type', 'image/svg+xml; charset=utf-8');

        return $response;
    }

    #[Route('/meta/{username}.jpg', name: 'share_meta', requirements: ['username' => '[a-zA-Z0-9\-\_]+'], methods: ['GET'])]
    public function sharerMeta(string $username): Response
    {
        $user = $this->userRepository->findOneBy(['username' => $username]);

        if (!$user instanceof User) {
            throw new NotFoundHttpException('User not found');
        }

        $svgContent = $this->renderView('partials/sharer-meta-svg.html.twig', [
            'user'          => $user,
            'globalRanking' => $this->rankingService->getRankingGlobal($user),
            'totalUsers'    => $this->userRepository->countUsers(),
            'topLanguage'   => $this->rankingService->getTopLanguage($user),
            'picture'       => base64_encode($this->fetchAvatarPicture($user)),
        ]);

        $image = new \Imagick();

        $image->readImageBlob($svgContent);
        $image->setImageFormat('jpg');
        $image->thumbnailImage(1200, 600);

        $response = new Response($image->getImageBlob());
        $response->headers->set('Content-Type', 'image/jpg');

        return $response;
    }

    private function fetchAvatarPicture(User $user): string
    {
        $imagePath = __DIR__ . '/../../public/avatars/' . $user->getGithubId() . '.jpg';

        if (!$this->filesystem->exists($imagePath)) {
            $imageData = (string) file_get_contents('https://avatars.githubusercontent.com/u/' . $user->getGithubId() . '?s=360&v=4');
            $this->filesystem->appendToFile($imagePath, $imageData);
        } else {
            $imageData = (string) file_get_contents($imagePath);
        }

        return $imageData;
    }
}

<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Language;
use App\Repository\LanguageRepository;
use App\Repository\UserLanguageRepository;
use App\Utils\PaginateHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class LanguageController extends AbstractController
{
    public function __construct(private LanguageRepository $languageRepository)
    {
    }

    #[Route('/languages/{page}', name: 'languages_index', requirements: ['page' => '[0-9]+'], methods: ['GET'])]
    public function index(int $page = 1): Response
    {
        $totalLanguages = $this->languageRepository->totalLanguage();

        $paginate = PaginateHelper::create($page, (int) $totalLanguages);

        if ($page > $paginate['total'] || $page <= 0) {
            throw new NotFoundHttpException('Page not found');
        }

        $start = ($page - 1) * 25;

        $languages = $this->languageRepository->findAllByStars($start);

        return $this->render('language/index.html.twig', [
            'languages' => $languages,
            'paginate'  => $paginate,
        ]);
    }

    #[Route('/language/{slug}/{page}', name: 'languages_show', requirements: ['slug' => '[a-z0-9\-]+', 'page' => '[0-9]+'], methods: ['GET'])]
    public function show(UserLanguageRepository $userLanguageRepository, string $slug, int $page = 1): Response
    {
        $language = $this->languageRepository->findOneBy(['slug' => $slug]);

        if (!$language instanceof Language) {
            throw new NotFoundHttpException('Language not found');
        }

        $totalLanguageUsers = $userLanguageRepository->totalLanguagePages($language);

        $paginate = PaginateHelper::create($page, $totalLanguageUsers);

        if ($page > $paginate['total'] || $page <= 0) {
            throw new NotFoundHttpException('Page not found');
        }

        $start = ($page - 1) * 25;

        $userLanguages = $userLanguageRepository->findUserByLanguage($language, $start);

        return $this->render('language/show.html.twig', [
            'language'      => $language,
            'userLanguages' => $userLanguages,
            'paginate'      => $paginate,
        ]);
    }
}

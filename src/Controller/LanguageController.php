<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Language;
use App\Repository\LanguageRepository;
use App\Repository\UserLanguageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use App\Utils\PaginateHelper;

class LanguageController extends AbstractController
{
    public function __construct(private LanguageRepository $languageRepository)
    {
    }

    #[Route('/languages', name: 'languages_index', methods: ['GET'])]
    public function index(): Response
    {
        $languages = $this->languageRepository->findAll();

        return $this->render('language/index.html.twig', [
            'languages' => $languages,
        ]);
    }

    #[Route('/languages/{slug}/{page}', name: 'languages_show', methods: ['GET'])]
    public function show(UserLanguageRepository $userLanguageRepository, string $slug, int $page = 1): Response
    {
        $language = $this->languageRepository->findOneBy(['slug' => $slug]);

        if (!$language instanceof Language) {
            throw new NotFoundHttpException('Language not found');
        }

        $start = ($page - 1) * 2;

        $userLanguages = $userLanguageRepository->findUserByLanguage($language, $start);

        $totalLanguagePages = $userLanguageRepository->totalLanguagePages($language);

        $paginate = PaginateHelper::create($page, $totalLanguagePages);
        dd($totalLanguagePages, $paginate);
        return $this->render('language/show.html.twig', [
            'language'      => $language,
            'userLanguages' => $userLanguages,
        ]);
    }
}

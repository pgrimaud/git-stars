<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\LanguageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LanguageController extends AbstractController
{
    public function __construct(private LanguageRepository $languageRepository)
    {
    }

    #[Route('/languages', name: 'languages_index')]
    public function index(): Response
    {
        $languages = $this->languageRepository->findAll();

        return $this->render('language/index.html.twig', [
            'languages' => $languages,
        ]);
    }

    #[Route('/languages/{slug}', name: 'languages_show')]
    public function show(string $slug): Response
    {
        $language = $this->languageRepository->findOneBy(['slug' => $slug]);

        return $this->render('language/show.html.twig', [
            'language' => $language,
        ]);
    }
}

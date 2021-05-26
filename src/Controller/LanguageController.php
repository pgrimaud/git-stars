<?php

namespace App\Controller;

use App\Entity\Language;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LanguageController extends AbstractController
{
    #[Route('/languages', name: 'languages_index')]
    public function index(): Response
    {
        $languages = $this->getDoctrine()
            ->getRepository(Language::class)
            ->findAll();

        return $this->render('language/index.html.twig', [
            'languages' => $languages,
        ]);
    }

    #[Route('/languages/{slug}', name: 'languages_show')]
    public function show(string $slug): Response
    {
        $language = $this->getDoctrine()
            ->getRepository(Language::class)
            ->findOneBy(['slug' => $slug]);

        return $this->render('language/show.html.twig', [
            'language'  => $language,
        ]);
    }
}

<?php

declare(strict_types=1);

namespace App\Controller;

use App\Form\Model\SearchLanguage;
use App\Form\SearchLanguageType;
use App\Repository\LanguageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/search')]
class SearchController extends AbstractController
{
    #[Route('/language', name: 'search_language')]
    public function searchLanguage(Request $request, LanguageRepository $languageRepository): Response
    {
        $form = $this->createForm(SearchLanguageType::class, new SearchLanguage());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $result = $form->getData()->language;
            if ($language = $languageRepository->findOneBy(['name' => $result])) {
                return $this->redirectToRoute('languages_show', ['slug' => $language->getSlug()]);
            }
        }

        throw new NotFoundHttpException('Language does not exist');
    }
}

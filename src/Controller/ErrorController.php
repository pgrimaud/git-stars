<?php

declare(strict_types=1);

namespace App\Controller;

use App\Form\Model\SearchUser;
use App\Form\SearchUserType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\HttpFoundation\Response;

class ErrorController extends AbstractController
{
    public function show(FlattenException $exception): Response
    {
        $form = $this->createForm(SearchUserType::class, new SearchUser());

        $template = $exception->getStatusCode() === 404 ? 'error404' : 'error';

        return $this->render('bundles/TwigBundle/Exception/' . $template . '.html.twig', [
            'search_form' => $form->createView(),
        ]);
    }
}

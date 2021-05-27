<?php

declare(strict_types=1);

namespace App\Controller;

use App\Client\GitHub\GitHubClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AppController extends AbstractController
{
    #[Route('/', name: 'app_index', methods: ['GET'])]
    public function index(): Response
    {
        //$githubClient = GitHubClient::get($this->getUser());
        //dd($githubClient->api('user')->repositories('nispeon'));

        return $this->render('app/index.html.twig');
    }
}

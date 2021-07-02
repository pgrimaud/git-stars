<?php

namespace App\Controller;

use App\Repository\LanguageRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SitemapController extends AbstractController
{
    public const URL_LIMIT = 20000;

    #[Route('/sitemap.xml', name: 'sitemap_index')]
    public function index(UserRepository $userRepository): Response
    {
        $xml = new \SimpleXMLElement('<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"/>');

        $sitemapTable = [
            [
                'path'   => 'sitemap_pages',
                'params' => [],
            ],
            [
                'path'   => 'sitemap_languages',
                'params' => [],
            ],
        ];

        $countUsers = $userRepository->countUsers();
        $userPages  = ceil($countUsers / self::URL_LIMIT);

        for ($i = 1; $i <= $userPages; ++$i) {
            $page = [
                'path'   => 'sitemap_users',
                'params' => ['page' => $i],
            ];
            array_push($sitemapTable, $page);
        }

        foreach ($sitemapTable as $sitemap) {
            $map = $xml->addChild('sitemap');
            $map->addChild('loc', $this->generateUrl(
                $sitemap['path'],
                $sitemap['params'],
                UrlGeneratorInterface::ABSOLUTE_URL)
            );
        }

        $response = new Response((string) $xml->asXML());

        $response->headers->set('Content-type', 'text/xml');

        return $response;
    }

    #[Route('/sitemap-languages.xml', name: 'sitemap_languages')]
    public function languages(LanguageRepository $languageRepository): Response
    {
        $xml = new \SimpleXMLElement('<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"/>');

        $languageArray = $languageRepository->findAll();
        $date          = new \DateTime('-7 days');

        foreach ($languageArray as $lang) {
            $map = $xml->addChild('url');
            $map->addChild('loc', $this->generateUrl(
                'languages_show',
                ['slug' => $lang->getSlug()],
                UrlGeneratorInterface::ABSOLUTE_URL)
            );
            $map->addChild('lastmod', $date->format('c'));
            $map->addChild('changefreq', 'weekly');
        }

        $response = new Response((string) $xml->asXML());

        $response->headers->set('Content-type', 'text/xml');

        return $response;
    }

    #[Route('/sitemap-pages.xml', name: 'sitemap_pages')]
    public function pages(): Response
    {
        $xml = new \SimpleXMLElement('<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"/>');

        $pagesArray = [
            'app_index',
            'app_about',
            'user_index',
            'languages_index',
        ];

        $date = new \DateTime('-7 days');

        foreach ($pagesArray as $page) {
            $map = $xml->addChild('url');
            $map->addChild('loc', $this->generateUrl(
                $page,
                [],
                UrlGeneratorInterface::ABSOLUTE_URL)
            );
            $map->addChild('lastmod', $date->format('c'));
            $map->addChild('changefreq', 'weekly');
        }

        $response = new Response((string) $xml->asXML());

        $response->headers->set('Content-type', 'text/xml');

        return $response;
    }

    #[Route('/sitemap-users-{page}.xml', name: 'sitemap_users')]
    public function users(int $page, UserRepository $userRepository): Response
    {
        $count = $userRepository->countUsers();
        if ($page > ceil($count / self::URL_LIMIT) || $page <= 0) {
            throw new NotFoundHttpException('Page not found');
        }

        $xml = new \SimpleXMLElement('<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"/>');

        $start    = ($page - 1) * self::URL_LIMIT;
        $userList = $userRepository->getSitemapUsers($start, self::URL_LIMIT);

        $date = new \DateTime('-7 days');

        foreach ($userList as $user) {
            $map = $xml->addChild('url');
            $map->addChild('loc', $this->generateUrl(
                'user_show',
                ['username' => $user['username']],
                UrlGeneratorInterface::ABSOLUTE_URL)
            );
            $map->addChild('lastmod', $date->format('c'));
            $map->addChild('changefreq', 'weekly');
        }

        $response = new Response((string) $xml->asXML());

        $response->headers->set('Content-type', 'text/xml');

        return $response;
    }
}

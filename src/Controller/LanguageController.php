<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Language;
use App\Form\Model\SearchLanguage;
use App\Form\SearchLanguageType;
use App\Repository\CityRepository;
use App\Repository\CountryRepository;
use App\Repository\LanguageRepository;
use App\Repository\UserLanguageRepository;
use App\Repository\UserRepository;
use App\Utils\PaginateHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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

        $languageArray = $this->languageRepository->getArrayOfNames();

        $searchForm = $this->createForm(SearchLanguageType::class, new SearchLanguage());

        return $this->render('language/index.html.twig', [
            'languages'     => $languages,
            'paginate'      => $paginate,
            'languageArray' => json_encode($languageArray),
            'search_form'   => $searchForm->createView(),
        ]);
    }

    #[Route('/language/{slug}/{page}', name: 'languages_show', requirements: ['slug' => '[a-z0-9\-]+', 'page' => '[0-9]+'], methods: ['GET'])]
    public function show(
        Request $request,
        UserLanguageRepository $userLanguageRepository,
        UserRepository $userRepository,
        CountryRepository $countryRepository,
        CityRepository $cityRepository,
        string $slug,
        int $page = 1): Response
    {
        $language = $this->languageRepository->findOneBy(['slug' => $slug]);

        $city    = null;
        $country = null;

        if ($countryParam = $request->get('country')) {
            $country = $countryRepository->findOneBy([
                'slug' => $countryParam,
            ]);

            if ($cityParam = $request->get('city')) {
                $city = $cityRepository->findOneBy([
                    'slug' => $cityParam,
                ]);
            }
        }

        if (!$language instanceof Language) {
            throw new NotFoundHttpException('Language not found');
        }

        $totalLanguageUsers = $userLanguageRepository->totalLanguagePages($language, $country, $city);

        $paginate = PaginateHelper::create($page, $totalLanguageUsers);

        if ($page > $paginate['total'] || $page <= 0) {
            throw new NotFoundHttpException('Page not found');
        }

        $start = ($page - 1) * 25;

        $userLanguages = $userLanguageRepository->findUserByLanguage($language, $country, $city, $start);
        $countries     = $countryRepository->findAllCountriesByLanguage($language);

        $cities = !$country ? null : $cityRepository->findAllCitiesByLanguage($language, $country);

        return $this->render('language/show.html.twig', [
            'language'      => $language,
            'userLanguages' => $userLanguages,
            'countries'     => $countries,
            'city'          => $city,
            'cities'        => $cities,
            'country'       => $country,
            'paginate'      => $paginate,
        ]);

//        #[Route('/language/search', name: 'languages_search', methods: ['GET'])]
//        public function show()
//        {
//
//        }
    }
}

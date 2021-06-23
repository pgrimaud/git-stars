<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Message\ManualUpdateUser;
use App\Repository\CityRepository;
use App\Repository\CountryRepository;
use App\Repository\UserLanguageRepository;
use App\Repository\UserRepository;
use App\Service\RankingService;
use App\Utils\PaginateHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    public function __construct(private UserRepository $userRepository)
    {
    }

    #[Route('/users/{page}', name: 'user_index', requirements: ['page' => '[0-9]+'], methods: ['GET'])]
    public function index(
        Request $request,
        CountryRepository $countryRepository,
        CityRepository $cityRepository,
        RankingService $rankingService,
        int $page = 1): Response
    {
        $userTypeFilter = null;
        if ($userType = $request->get('type')) {
            match ($userType) {
                'users'         => $userTypeFilter = 0,
                'organizations' => $userTypeFilter = 1,
                default         => $userTypeFilter = null,
            };
        }

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
        $totalUsers = $this->userRepository->totalPages($country, $city, $userTypeFilter);

        $paginate = PaginateHelper::create($page, $totalUsers);

        if ($page > $paginate['total'] || $page <= 0) {
            throw new NotFoundHttpException('Page not found');
        }

        $start = ($page - 1) * 25;

        $users = $rankingService->findSomeUsers($country, $city, $userTypeFilter, $start);

        $countries = $countryRepository->findAllCountries();

        $cities = !$country ? null : $cityRepository->findCitiesByCountry($country, $userTypeFilter);

        $hasUsers         = $this->userRepository->checkUserType($country, $city, false);
        $hasOrganizations = $this->userRepository->checkUserType($country, $city, true);

        return $this->render('user/index.html.twig', [
            'users'            => $users,
            'paginate'         => $paginate,
            'countries'        => $countries,
            'country'          => $country,
            'cities'           => $cities,
            'city'             => $city,
            'userType'         => $userType,
            'hasUsers'         => $hasUsers,
            'hasOrganizations' => $hasOrganizations,
        ]);
    }

    #[Route('/user/{username}', name: 'user_show', requirements: ['username' => '[a-zA-Z0-9\-\_]+'], methods: ['GET'])]
    public function show(
        RankingService $rankingService,
        UserLanguageRepository $userLanguageRepository,
        string $username
    ): Response {
        $user = $this->userRepository->findOneBy(['username' => $username]);

        if (!$user instanceof User) {
            throw new NotFoundHttpException('User not found');
        }

        $userLanguages = $rankingService->getRankingLanguage($user);

        $worldRank = $rankingService->getRankingGlobal($user);

        // user is not ranked yet?
        if ($userLanguages === []) {
            $userLanguages = $userLanguageRepository->findLanguageByUser($user);
        }

        return $this->render('user/show.html.twig', [
            'user'          => $user,
            'userLanguages' => $userLanguages,
            'worldRank'     => $worldRank,
        ]);
    }

    #[Route('/user/{username}/update', name: 'user_update', requirements: ['username' => '[a-zA-Z0-9\-\_]+'], methods: ['GET'])]
    public function update(MessageBusInterface $bus, string $username): Response
    {
        if (!$this->getUser()) {
            $route = $this->generateUrl('hwi_oauth_service_redirect', [
                'service'      => 'github',
                '_destination' => $this->generateUrl('user_update', [
                    'username' => $username,
                ]),
            ]);

            return $this->redirect($route);
        } else {
            $user = $this->userRepository->findOneBy(['username' => $username]);

            if (!$user instanceof User) {
                throw new NotFoundHttpException('User not found');
            }

            if ($user->getStatus() === User::STATUS_IDLE) {
                $user->setStatus($user::STATUS_RUNNING);
                $this->getDoctrine()->getManager()->persist($user);
                $this->getDoctrine()->getManager()->flush();

                $bus->dispatch(
                // @phpstan-ignore-next-line
                    new ManualUpdateUser($user->getGithubId(), $this->getUser()->getAccessToken())
                );
            }
        }

        return $this->redirectToRoute('user_show', ['username' => $user->getUsername()]);
    }

    #[Route('/user/{username}/status', name: 'user_status', requirements: ['username' => '[a-zA-Z0-9\-\_]+'], methods: ['GET'])]
    public function status(string $username): Response
    {
        $user = $this->userRepository->findOneBy(['username' => $username]);

        if (!$user instanceof User) {
            throw new NotFoundHttpException('User not found');
        }

        return $this->json([
            'status' => $user->getStatus(),
        ]);
    }
}

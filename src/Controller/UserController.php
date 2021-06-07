<?php

namespace App\Controller;

use App\Entity\User;
use App\Message\ManualUpdateUser;
use App\Repository\CountryRepository;
use App\Repository\LanguageRepository;
use App\Repository\UserLanguageRepository;
use App\Repository\UserRepository;
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

    #[Route('/users/{page}', name: 'user_index', methods: ['GET'])]
    public function index(Request $request, CountryRepository $countryRepository, int $page = 1): Response
    {
        if ($countryParam = $request->get('country')) {
            $country = $countryRepository->findOneBy([
                'slug' => $countryParam,
            ]);
        } else {
            $country = null;
        }

        $totalUsers = $this->userRepository->totalPages($country);

        $paginate = PaginateHelper::create($page, $totalUsers);

        if ($page > $paginate['total'] || $page <= 0) {
            throw new NotFoundHttpException('Page not found');
        }

        $start = ($page - 1) * 25;

        $users = $this->userRepository->findSomeUsers($start, $country);

        $countries = $countryRepository->findAllCountries();

        return $this->render('user/index.html.twig', [
            'users'     => $users,
            'paginate'  => $paginate,
            'countries' => $countries,
            'country'   => $country,
        ]);
    }

    #[Route('/user/{username}', name: 'user_show', requirements: ['username' => '[a-zA-Z0-9\-\_]+'], methods: ['GET'])]
    public function show(
        UserLanguageRepository $userLanguageRepository,
        LanguageRepository $languageRepository,
        string $username): Response
    {
        $user = $this->userRepository->findOneBy(['username' => $username]);

        if (!$user instanceof User) {
            throw new NotFoundHttpException('User not found');
        }

        $userLanguages = $userLanguageRepository->findLanguageByUsers($user);

//        dd($userLanguages);

        foreach ($userLanguages as $ul) {
            $language = $languageRepository->findOneBy(['name' => $ul['name']]);

            $totalRanking = $userLanguageRepository->getUserRank($language);
//            $ul['rank']   = $this->searchForId($user->getId(), $totalRanking);
            dd($ul);
        }

        return $this->render('user/show.html.twig', [
            'user'          => $user,
            'userLanguages' => $userLanguages,
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

//    private function searchForId(int $search_value, array $array)
//    {
//        $id_path = ['$'];
//        // Iterating over main array
//        foreach ($array as $key1 => $val1) {
//            $temp_path = $id_path;
//
//            // Adding current key to search path
//            array_push($temp_path, $key1);
//
//            // Check if this value is an array
//            // with at least one element
//            if (is_array($val1) and count($val1)) {
//                // Iterating over the nested array
//                foreach ($val1 as $key2 => $val2) {
//                    if ($val2 == $search_value) {
//                        // Adding current key to search path
//                        array_push($temp_path, $key2);
//
//                        return join(' --> ', $temp_path);
//                    }
//                }
//            } elseif ($val1 == $search_value) {
//                return join(' --> ', $temp_path);
//            }
//        }
//
//        return null;
//    }
}

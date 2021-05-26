<?php

namespace App\DataFixtures;

use App\Entity\Language;
use App\Entity\User;
use App\Entity\UserLanguage;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class UserLanguageFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $users = $manager->getRepository(User::class)->findAll();

        $languages = $manager->getRepository(Language::class)->findAll();

        foreach ($users as $user) {

            $languagesTemp = $languages;

            for ($i = 0; $i < 10; $i++) {
                $userLang = new UserLanguage();
                $userLang->setUser($user);

                $assignedLang = array_rand($languagesTemp, 1);
                $userLang->setLanguage($languagesTemp[$assignedLang]);
                unset($languagesTemp[$assignedLang]);

                $userLang->setStars(rand(0, 30000));

                $manager->persist($userLang);
            }
        }

        $manager->flush();
    }
}

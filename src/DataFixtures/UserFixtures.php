<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class UserFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $faker = Factory::create();

        for ($i = 0; $i < 500; $i++) {
            $user = new User();
            $user->setUsername($faker->userName);
            $user->setAccessToken($faker->shuffleString('sf6zc6ez4vc165vc468re4sdc168ez4c65'));
            $user->setGithubId(1234 + $i);

            $manager->persist($user);
        }

        $manager->flush();
    }
}

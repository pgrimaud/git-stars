<?php

namespace App\DataFixtures;

use App\Entity\Language;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\String\Slugger\AsciiSlugger;

class LanguageFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $json = file_get_contents('https://raw.githubusercontent.com/ozh/github-colors/master/colors.json');

        $data = json_decode($json, true);

        foreach ($data as $key => $githubLang) {
            $language = new Language();
            $language->setName($key);

            $slugger = new AsciiSlugger();
            $slug = $slugger->slug('Wôrķšƥáçè ~~sèťtïñğš~~');
            dd($slug);
            
            $language->setSlug('php');
            $language->setColor($githubLang['color']);

            $manager->persist($language);
        }
        
        $manager->flush();
    }
}

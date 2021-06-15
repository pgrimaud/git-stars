<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Language;
use App\Utils\LanguageHelper;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\String\Slugger\SluggerInterface;

class LanguageFixtures extends Fixture implements FixtureGroupInterface
{
    public function __construct(private SluggerInterface $slugger)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $json = (string) file_get_contents('https://raw.githubusercontent.com/ozh/github-colors/master/colors.json');
        $data = json_decode($json, true);

        foreach ($data as $key => $githubLang) {
            $slug = str_replace(['+ERB', '+', '#', '*'], ['-plus-ERB', '-plus', '-sharp', '-star'], $key);
            $slug = $this->slugger->slug($slug)->lower();

            $language = new Language();
            $language->setName($key);
            $language->setSlug($slug->toString());
            $language->setColor(strtolower($githubLang['color'] ?? LanguageHelper::createColor()));

            $manager->persist($language);
        }

        $manager->flush();
    }

    public static function getGroups(): array
    {
        return ['full', 'partial'];
    }
}

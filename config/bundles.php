<?php

return [
    Symfony\Bundle\FrameworkBundle\FrameworkBundle::class             => ['all' => true],
    Symfony\Bundle\MakerBundle\MakerBundle::class                     => ['dev' => true],
    Doctrine\Bundle\DoctrineBundle\DoctrineBundle::class              => ['all' => true],
    Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle::class  => ['all' => true],
    Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle::class      => ['dev' => true, 'test' => true],
    Symfony\Bundle\TwigBundle\TwigBundle::class                       => ['all' => true],
    Symfony\Bundle\WebProfilerBundle\WebProfilerBundle::class         => ['dev' => true, 'test' => true],
    Symfony\Bundle\MonologBundle\MonologBundle::class                 => ['all' => true],
    Symfony\Bundle\DebugBundle\DebugBundle::class                     => ['dev' => true],
    Twig\Extra\TwigExtraBundle\TwigExtraBundle::class                 => ['all' => true],
    Symfony\Bundle\SecurityBundle\SecurityBundle::class               => ['all' => true],
    HWI\Bundle\OAuthBundle\HWIOAuthBundle::class                      => ['all' => true],
    Http\HttplugBundle\HttplugBundle::class                           => ['all' => true],
    Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle::class => ['all' => true],
    Sentry\SentryBundle\SentryBundle::class                           => ['all' => true],
    Joli\GifExceptionBundle\GifExceptionBundle::class                 => ['dev' => true, 'test' => true],
];

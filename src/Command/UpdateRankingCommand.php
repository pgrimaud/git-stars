<?php

declare(strict_types=1);

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:update:ranking',
    description: 'Update ranking table',
)]
class UpdateRankingCommand extends Command
{
    public function __construct(private EntityManagerInterface $em, string $name = null)
    {
        parent::__construct($name);
    }

    protected function configure(): void
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $this->em->getConnection()->executeQuery('DROP TABLE IF EXISTS ranking_language;');

        $createTable = 'CREATE TABLE ranking_language AS

                        SELECT t1.*, t4.total_user_world, t2.total_user_country, t3.total_user_city
                        FROM (
                        
                        SELECT ul.user_id, u.country_id, u.city_id, language_id, ul.repositories, stars,
                        ROUND(sum(ul.stars) + (1.0 - 1.0/ul.repositories), 2) AS score, 
                        row_number() OVER (PARTITION BY language_id ORDER BY score DESC) as rank_world,
                        IF(country_id IS NOT NULL, row_number() OVER (PARTITION BY language_id, country_id ORDER BY score  DESC), NULL) as rank_country,
                        IF(city_id IS NOT NULL, row_number() OVER (PARTITION BY language_id, city_id ORDER BY score  DESC), NULL) as rank_city
                        FROM user_language ul
                        LEFT JOIN user u ON ul.user_id = u.id
                        GROUP BY language_id, user_id, city_id, country_id
                        ) t1
                        
                        LEFT OUTER JOIN (
                        
                        SELECT count(ul.user_id) as total_user_country, ul.language_id as language_id, u.country_id
                        FROM user_language ul
                        INNER JOIN user u on u.id = ul.user_id
                        WHERE u.country_id IS NOT NULL
                        GROUP BY ul.language_id, u.country_id
                         
                        ) t2 on t1.language_id = t2.language_id and t1.country_id = t2.country_id
                        
                        LEFT OUTER JOIN (
                        
                        SELECT count(ul.user_id) as total_user_city, ul.language_id as language_id, u.city_id
                        FROM user_language ul
                        INNER JOIN user u on u.id = ul.user_id
                        WHERE u.city_id IS NOT NULL
                        GROUP BY ul.language_id, u.city_id
                         
                        ) t3 on t1.language_id = t3.language_id and t1.city_id = t3.city_id
                        
                        INNER JOIN (
                        
                        SELECT count(ul.user_id) as total_user_world, ul.language_id as language_id
                        FROM user_language ul
                        GROUP BY ul.language_id
                        
                        ) t4 ON t1.language_id = t4.language_id;';
        $this->em->getConnection()->executeQuery($createTable);

        // add index
        $this->em->getConnection()->executeQuery('CREATE INDEX ranks_user_id ON ranking_language(user_id) USING HASH;');

        $io->success('Ranking language have been updated');

        $this->em->getConnection()->executeQuery('DROP TABLE IF EXISTS ranking_global;');

        $createTableGlobal = 'CREATE TABLE ranking_global AS
                                SELECT user_id, SUM(stars) as stars, 
                                ROUND(sum(stars) + (1.0 - 1.0/sum(repositories)), 2) AS score,
                                row_number() OVER (ORDER BY score DESC) as rank
                                FROM user_language 
                                GROUP BY user_id;';

        $this->em->getConnection()->executeQuery($createTableGlobal);

        // add index
        $this->em->getConnection()->executeQuery('CREATE INDEX ranks_user_id ON ranking_global(user_id) USING HASH;');

        $io->success('Ranking global have been updated');

        return Command::SUCCESS;
    }
}

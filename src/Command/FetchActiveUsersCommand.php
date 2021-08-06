<?php

declare(strict_types=1);

namespace App\Command;

use App\Client\AMQP\AMQPClient;
use App\Message\UpdateUser;
use App\Repository\UserRepository;
use Google\Cloud\BigQuery\BigQueryClient;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(
    name: 'app:fetch:active-users',
    description: 'Fetch new actives users from Github Archive',
)]
class FetchActiveUsersCommand extends Command
{
    public const FETCH_RESULT = 300000;

    public function __construct(
        private UserRepository $userRepository,
        private MessageBusInterface $bus,
        private AMQPClient $amqpClient,
        private AdapterInterface $cacheAdapter,
        string $name = null
    ) {
        parent::__construct($name);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io   = new SymfonyStyle($input, $output);
        $date = new \DateTime('-2 days');

        if (($messages = $this->amqpClient->getQueueMessages('users')) > 0) {
            $io->warning('Queue still contains ' . $messages . ' messages. Skipping command.');

            return Command::FAILURE;
        }

        $cacheKey = $this->cacheAdapter->getItem('gh-offset');

        if ($cacheKey->isHit()) {
            $offset = $cacheKey->get();
            $cacheKey->set($offset + self::FETCH_RESULT);
        } else {
            $offset = 0;
            $cacheKey->set(self::FETCH_RESULT);
        }

        $cacheReset = new \DateTime();
        $cacheReset->setTime(23, 45);
        $cacheKey->expiresAt($cacheReset);

        $this->cacheAdapter->save($cacheKey);

        $limit = self::FETCH_RESULT;

        $bigQuery = new BigQueryClient([
            'keyFile' => json_decode((string) file_get_contents(__DIR__ . '/../../gc-key.json'), true),
        ]);

        $io->warning('Starting. Offset is ' . $offset);

        $query = 'SELECT actor.id, actor.login FROM `githubarchive.day.' . $date->format('Ymd') . '` 
                  WHERE actor.login NOT LIKE "%[bot]%"
                  GROUP BY actor.id, actor.login ORDER BY actor.id
                  LIMIT ' . $limit . ' 
                  OFFSET ' . $offset;

        $queryJobConfig = $bigQuery->query($query);
        $queryResults   = $bigQuery->runQuery($queryJobConfig);

        $users = $this->userRepository->findAllId();

        $newUser = 0;

        foreach ($queryResults as $result) {
            if (!in_array($result['id'], $users)) {
                $this->bus->dispatch(
                    new UpdateUser($result['id'])
                );
                ++$newUser;
            }
        }

        $io->success($newUser . ' new user(s) added!');

        return Command::SUCCESS;
    }
}

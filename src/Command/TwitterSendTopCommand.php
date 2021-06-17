<?php

namespace App\Command;

use App\Client\Twitter\TwitterClient;
use App\Repository\UserRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:twitter:send-top',
    description: 'Tweets the users of the day on Twitter',
)]
class TwitterSendTopCommand extends Command
{
    public function __construct(
        private TwitterClient $twitterClient,
        private UserRepository $userRepository,
        string $name = null
    ) {
        parent::__construct($name);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $topUsers = $this->userRepository->getTodayTop();

        foreach ($topUsers as $user) {
            $message = '@' . $user[0]->getTwitterHandle()
                . ' is one of our lucky users of today!'
                . PHP_EOL . 'Check them out over at https://git-stars.com/user/'
                . $user[0]->getUsername()
                . '!';
            $this->twitterClient->sendTweet($message);
        }

        $io->success('The top users have been tweeted out successfully!');

        return Command::SUCCESS;
    }
}

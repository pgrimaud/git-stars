<?php

namespace App\Command;

use App\Client\Twitter\TwitterClient;
use App\Message\UpdateUser;
use App\Repository\UserRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(
    name: 'app:twitter:send-top',
    description: 'Posts the users of the day on Twitter',
)]
class TwitterSendTopCommand extends Command
{
    public function __construct(
        private TwitterClient $twitterClient,
        private UserRepository $userRepository,
        private MessageBusInterface $bus,
        string $name = null
    ) {
        parent::__construct($name);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $topUsers = $this->userRepository->getTodayTop();

        $messageIntro = [
            'The stars have spoken ✨!',
            'I have had a premonition 🔮!',
            'The prophecies had foretold us 🗿!',
            'The voice of space echoes once again 💫!',
        ];

        foreach ($topUsers as $user) {
            $this->bus->dispatch(
                new UpdateUser($user[0]->getGithubId())
            );
            $message = $messageIntro[rand(0, 3)] . PHP_EOL . '@' . $user[0]->getTwitterHandle()
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

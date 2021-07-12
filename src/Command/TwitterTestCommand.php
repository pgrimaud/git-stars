<?php

namespace App\Command;

use App\Client\Twitter\TwitterClient;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:twitter:hello-world',
    description: 'Tweet "Hello world"',
)]
class TwitterTestCommand extends Command
{
    public function __construct(
        private TwitterClient $twitterClient,
        string $name = null
    ) {
        parent::__construct($name);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $this->twitterClient->sendTweet('Hello world!');

        $io->success('Hello world!');

        return Command::SUCCESS;
    }
}

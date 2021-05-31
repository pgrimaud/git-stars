<?php

namespace App\Command;

use App\Client\GitHub\GitHubClient;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:check-apikey',
    description: 'Add a short description for your command',
)]
class CheckApikeyCommand extends Command
{
    public function __construct(private GitHubClient $gitHubClient, string $name = null)
    {
        parent::__construct($name);
    }

    protected function configure(): void
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $rateLimits = $this->gitHubClient->checkApiKey();

        foreach ($rateLimits as $service => $rate) {
            $date = new \DateTime();
            $date->setTimestamp($rate->getReset());
            $diff = $date->diff(new \DateTime());

            $io->success($service . ' : ' . $rate->getRemaining() . ' calls remaining. It will reset in : ' . $diff->format('%h:%i:%s') . '.');
        }

        return Command::SUCCESS;
    }
}

<?php

declare(strict_types=1);

namespace App\Command;

use App\Client\GitHub\GitHubClient;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:check:api-keys',
    description: 'Check api keys rate limits',
)]
class CheckApikeyCommand extends Command
{
    public function __construct(private string $tokens, private GitHubClient $gitHubClient, string $name = null)
    {
        parent::__construct($name);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $tokens = explode(',', $this->tokens);

        $progress = new ProgressBar($output, count($tokens));
        $progress->setFormat("<fg=green>%message%</>\n %current%/%max% [%bar%] %percent:3s%% (%remaining%)");
        $progress->setProgressCharacter("\xF0\x9F\x8D\xBA");
        $progress->setMessage('Starting');
        $progress->start();

        $rates = [];
        foreach ($tokens as $position => $token) {
            $rateLimits = $this->gitHubClient->checkApiKey($token);

            $progress->setMessage('Check API key : ' . $token);
            $progress->display();

            foreach ($rateLimits as $service => $rate) {
                if ($service === 'core') {
                    $date = new \DateTime();
                    $date->setTimestamp($rate->getReset());
                    $diff = $date->diff(new \DateTime());

                    $rates[] = [
                        'position'  => $position + 1,
                        'token'     => $token,
                        'remaining' => $rate->getRemaining(),
                        'timer'     => ($diff->format('%i') < 10 ? '0' . $diff->format('%i') : $diff->format('%i')) . 'm' .
                            ($diff->format('%s') < 10 ? '0' . $diff->format('%s') : $diff->format('%s')) . 's',
                    ];
                    break;
                }
            }
            $progress->advance();
        }

        $progress->finish();

        $io->newLine(2);

        // sort array by reset timer
        $timer = [];
        foreach ($rates as $k => $v) {
            $timer[$k] = $v['remaining'];
        }

        array_multisort($timer, $rates);

        // render clean table
        $table = new Table($io);
        $table->setHeaders(['Position', 'API Key', 'Calls remaining', 'Reset timer'])->setRows($rates);
        $table->render();

        return Command::SUCCESS;
    }
}

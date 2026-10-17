<?php
declare(strict_types=1);

namespace App\Command;

use Automattic\Wistia\DataMediaService;
use Automattic\Wistia\Exception\WistiaException;
use Automattic\Wistia\Request\Data\ListMediaRequest;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'wistia:media', description: 'Read one bounded page of media without printing metadata')]
final class WistiaListMediaCommand extends Command
{
    public function __construct(private readonly DataMediaService $media)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $result = $this->media->listMedia(new ListMediaRequest(per_page: 25));
            $output->writeln('Media records on this page: ' . count($result->data));
            return Command::SUCCESS;
        } catch (WistiaException $error) {
            $output->writeln('Wistia failure: ' . get_class($error) . '; HTTP ' . ($error->status ?? 'none'));
            if ($error->remoteOutcomeUncertain) {
                $output->writeln('Remote outcome uncertain; reconcile state before resubmission.');
            }
            // Do not log the exception, remote documents or full caller traces.
            return Command::FAILURE;
        }
    }
}

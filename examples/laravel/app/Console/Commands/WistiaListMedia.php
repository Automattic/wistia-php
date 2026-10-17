<?php
declare(strict_types=1);

namespace App\Console\Commands;

use Automattic\Wistia\DataMediaService;
use Automattic\Wistia\Exception\WistiaException;
use Automattic\Wistia\Request\Data\ListMediaRequest;
use Illuminate\Console\Command;

final class WistiaListMedia extends Command
{
    protected $signature = 'wistia:media';
    protected $description = 'Read one bounded page of Wistia media without printing metadata';

    public function handle(DataMediaService $media): int
    {
        try {
            $result = $media->listMedia(new ListMediaRequest(per_page: 25));
            $this->info('Media records on this page: ' . count($result->data));
            return self::SUCCESS;
        } catch (WistiaException $error) {
            $this->error('Wistia failure: ' . get_class($error) . '; HTTP ' . ($error->status ?? 'none'));
            if ($error->remoteOutcomeUncertain) {
                $this->error('Remote outcome uncertain; reconcile state before resubmission.');
            }
            // Do not report($error), dump responses or log exception traces.
            return self::FAILURE;
        }
    }
}

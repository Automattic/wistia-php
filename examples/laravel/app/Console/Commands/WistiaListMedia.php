<?php
declare(strict_types=1);

namespace App\Console\Commands;

use Automattic\Wistia\DataMediaService;
use Automattic\Wistia\Exception\WistiaException;
use Automattic\Wistia\Request\Data\ListMediaRequest;
use Illuminate\Console\Command;

final class WistiaListMedia extends Command
{
    // Laravel discovers this command in app/Console/Commands. The signature is
    // the name a consumer runs with: php artisan wistia:media.
    protected $signature = 'wistia:media';
    protected $description = 'Read one bounded page of Wistia media without printing metadata';

    // Laravel resolves this type hint using WistiaServiceProvider. No command
    // code needs to look up a token or construct its own HTTP client.
    public function handle(DataMediaService $media): int
    {
        try {
            // The request describes a single read with at most 25 records. The
            // call sends it; ListMediaResult->data contains typed media records.
            // Count them rather than printing names/URLs that may be private.
            $result = $media->listMedia(new ListMediaRequest(per_page: 25));
            $this->info('Media records on this page: ' . count($result->data));
            return self::SUCCESS;
        } catch (WistiaException $error) {
            // All typed library failures share this base class. Keep the concrete
            // class/status for diagnosis without exposing the remote document.
            // There is no retry loop, including for rate limits or timeouts.
            $this->error('Wistia failure: ' . get_class($error) . '; HTTP ' . ($error->status ?? 'none'));
            if ($error->remoteOutcomeUncertain) {
                $this->error('Remote outcome uncertain; reconcile state before resubmission.');
            }
            // Do not report($error), dump responses or log exception traces.
            return self::FAILURE;
        }
    }
}

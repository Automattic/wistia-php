<?php
declare(strict_types=1);

namespace WistiaExamples;

use Automattic\Wistia\{Client, Configuration, DataMediaService, DataBackgroundJobStatusService, UploadService, WebhookParser, WebhookVerifier};
use Automattic\Wistia\Exception\WistiaException;
use Automattic\Wistia\Jobs\{BackgroundJobPoller, PollingOptions, PollingRuntime, PollingTermination, SystemPollingRuntime};
use Automattic\Wistia\Request\Data\{ListMediaRequest, ShowBackgroundJobStatusRequest};
use Automattic\Wistia\Request\Upload\{UploadFileRequest, ImportUrlRequest};
use Automattic\Wistia\Upload\LocalFile;

/** Application-owned I/O; injected dependencies let the actual examples run offline. */
final class Application
{
    public static function run(string $workflow, array $args, callable $env, callable $out,
        ?Client $client = null, ?PollingRuntime $runtime = null): int
    {
        try {
            $expected = match ($workflow) {
                'list-media' => 0, 'upload-file' => 2, 'import-url' => 1,
                'poll-job' => 2, 'webhook' => 1,
            };
            if (count($args) !== $expected) { throw new \InvalidArgumentException(); }
            if ($workflow === 'webhook') {
                // Read the saved original bytes, never decode/re-encode before verification.
                $path = $args[0];
                if (!is_file($path) || !is_readable($path) || filesize($path) > 1048576) {
                    throw new \InvalidArgumentException();
                }
                $body = @file_get_contents($path);
                if ($body === false) { throw new \InvalidArgumentException(); }
                $delivery = (new WebhookParser(new WebhookVerifier(self::required($env, 'WISTIA_WEBHOOK_SECRET'))))
                    ->parse($body, self::required($env, 'WISTIA_WEBHOOK_SIGNATURE'));
                $out('Verified events: ' . count($delivery->events) . '. No events processed.');
                return 0;
            }
            // Validate all inputs before constructing a client or making a request.
            $request = match ($workflow) {
                'list-media' => new ListMediaRequest(per_page: 25),
                'upload-file' => self::upload($args),
                'import-url' => self::import($args, $env),
                'poll-job' => new ShowBackgroundJobStatusRequest($args[0]),
            };
            $options = $workflow === 'poll-job' ? new PollingOptions(self::attempts($args[1]), 1.0) : null;
            $client ??= new Client(new Configuration(token: self::required($env, 'WISTIA_API_TOKEN')));
            switch ($workflow) {
                case 'list-media':
                    $media = new DataMediaService($client);
                    $page = $media->listMedia($request);
                    $out('Single page records: ' . count($page->data));
                    // A separate traversal starts at page one; no metadata or URLs are logged.
                    $count = 0;
                    foreach ($media->paginateMedia($request, maxPages: 3)->items() as $item) { ++$count; }
                    $out('Bounded traversal records: ' . $count);
                    break;
                case 'upload-file':
                    $result = (new UploadService($client))->uploadFile($request);
                    $out('UploadFileResult accepted (HTTP ' . $result->status . '); media ID supplied: '
                        . ($result->data->hashed_id !== null ? 'yes' : 'no') . '. Processing/playability not confirmed.');
                    break;
                case 'import-url':
                    $result = (new UploadService($client))->importUrl($request);
                    $out('ImportUrlResult accepted (HTTP ' . $result->status . '); media ID supplied: '
                        . ($result->data->hashed_id !== null ? 'yes' : 'no') . '. Processing/playability not confirmed; no job ID inferred.');
                    break;
                case 'poll-job':
                    $poll = (new BackgroundJobPoller(new DataBackgroundJobStatusService($client), $runtime ?? new SystemPollingRuntime()))
                        ->poll($request, $options);
                    $out('Polling: ' . $poll->termination->name . '; attempts: ' . $poll->attempts);
                    $summary = $poll->last->backgroundJobStatus->object?->summary;
                    if ($summary !== null) { $out('Bulk results: succeeded=' . $summary->succeeded . '; failed=' . $summary->failed); }
                    return match ($poll->termination) {
                        PollingTermination::Completed => 0,
                        PollingTermination::ReportedFailure => 3,
                        PollingTermination::Exhausted => 4,
                    };
            }
            return 0;
        } catch (\InvalidArgumentException) {
            $out('Local input/configuration rejected. Check the examples README; no automatic retry.');
            return 2;
        } catch (WistiaException $error) {
            // Do not print messages, bodies, URLs, exception traces or remote error documents.
            $out('Library failure: ' . get_class($error) . '; HTTP ' . ($error->status ?? 'none'));
            if ($error->remoteOutcomeUncertain) {
                $out('Remote write outcome uncertain. Reconcile remote state before any resubmission.');
            }
            return 1;
        } catch (\OverflowException|\UnexpectedValueException) {
            $out('Traversal stopped at its bound or a non-advancing cursor. Results may be incomplete.');
            return 4;
        }
    }

    private static function required(callable $env, string $key): string
    {
        $value = $env($key);
        if (!is_string($value) || trim($value) === '') { throw new \InvalidArgumentException(); }
        return $value;
    }

    private static function upload(array $args): UploadFileRequest
    {
        if ($args[0] !== '--execute') { throw new \InvalidArgumentException(); }
        return new UploadFileRequest(LocalFile::fromPath($args[1], contentType: 'video/mp4'));
    }

    private static function import(array $args, callable $env): ImportUrlRequest
    {
        if ($args[0] !== '--execute') { throw new \InvalidArgumentException(); }
        return new ImportUrlRequest(self::required($env, 'WISTIA_SOURCE_URL'), low_priority: false);
    }

    private static function attempts(string $value): int
    {
        if (!preg_match('/^[1-9][0-9]?$/D', $value)) { throw new \InvalidArgumentException(); }
        return (int) $value;
    }
}

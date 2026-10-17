<?php
declare(strict_types=1);

namespace WistiaExamples;

use Automattic\Wistia\{Client, Configuration, DataMediaService, DataBackgroundJobStatusService, UploadService, WebhookParser, WebhookVerifier};
use Automattic\Wistia\Exception\WistiaException;
use Automattic\Wistia\Jobs\{BackgroundJobPoller, PollingOptions, PollingRuntime, PollingTermination, SystemPollingRuntime};
use Automattic\Wistia\Request\Data\{ListMediaRequest, ShowBackgroundJobStatusRequest};
use Automattic\Wistia\Request\Upload\{UploadFileRequest, ImportUrlRequest};
use Automattic\Wistia\Upload\LocalFile;

/**
 * Shared runner for the five CLI entry files in this directory.
 *
 * Each switch branch shows one workflow: construct a named request, call its
 * service, then read the typed result. This class is example application code,
 * not part of the Wistia library or a required architecture for your application.
 */
final class Application
{
    /**
     * CLI scripts supply getenv() and terminal output. Tests supply synthetic
     * configuration, captured output, a controlled client and fake polling time.
     * Keeping those dependencies injectable lets both use the same workflow code.
     *
     * @param list<string> $args Arguments after the script name, not the full argv.
     * @param callable(string): (string|false) $env Application-owned secret lookup.
     * @param callable(string): void $out Receives only deliberately safe summaries.
     * @return int Process exit code; the index documents failure/exhaustion codes.
     */
    public static function run(string $workflow, array $args, callable $env, callable $out,
        ?Client $client = null, ?PollingRuntime $runtime = null): int
    {
        try {
            // Reject an incomplete command before any network operation. Mutating
            // workflows also validate their explicit --execute flag below.
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
                // Webhooks are local verification, so they need no API client or
                // token. The secret is shared with the sender; the signature must
                // be the received header, not an HMAC we generate for this body.
                // parse() verifies first, then returns a typed envelope of events.
                $delivery = (new WebhookParser(new WebhookVerifier(self::required($env, 'WISTIA_WEBHOOK_SECRET'))))
                    ->parse($body, self::required($env, 'WISTIA_WEBHOOK_SIGNATURE'));
                $out('Verified events: ' . count($delivery->events) . '. No events processed.');
                return 0;
            }
            // Request constructors validate the operation's local input contract;
            // constructing a request does not send it. Remote permission, source
            // content and account limits are still checked by Wistia.
            $request = match ($workflow) {
                'list-media' => new ListMediaRequest(per_page: 25),
                'upload-file' => self::upload($args),
                'import-url' => self::import($args, $env),
                'poll-job' => new ShowBackgroundJobStatusRequest($args[0]),
            };
            // The attempt count and one-second interval bound repeated status
            // reads. Each individual HTTP request also has finite client timeouts.
            $options = $workflow === 'poll-job' ? new PollingOptions(self::attempts($args[1]), 1.0) : null;
            // Configuration supplies Bearer authentication; the shared client
            // handles HTTP, decoding and typed failures. Construction sends nothing.
            $client ??= new Client(new Configuration(token: self::required($env, 'WISTIA_API_TOKEN')));
            switch ($workflow) {
                case 'list-media':
                    $media = new DataMediaService($client);
                    // One request returns ListMediaResult. Its data property holds
                    // typed media records; count them here instead of logging metadata.
                    $page = $media->listMedia($request);
                    $out('Single page records: ' . count($page->data));
                    // This is a SECOND read workflow, not a continuation of $page:
                    // it starts at page one. items() fetches further pages lazily as
                    // iteration advances. maxPages prevents an unbounded account scan;
                    // reaching the bound can throw rather than imply complete results.
                    $count = 0;
                    foreach ($media->paginateMedia($request, maxPages: 3)->items() as $item) { ++$count; }
                    $out('Bounded traversal records: ' . $count);
                    break;
                case 'upload-file':
                    // This call creates remote media. LocalFile streams the source;
                    // the service closes its own file handle even when sending fails.
                    // UploadFileResult->data is Upload media, not a Data API record.
                    $result = (new UploadService($client))->uploadFile($request);
                    $out('UploadFileResult accepted (HTTP ' . $result->status . '); media ID supplied: '
                        . ($result->data->hashed_id !== null ? 'yes' : 'no') . '. Processing/playability not confirmed.');
                    break;
                case 'import-url':
                    // Wistia fetches the URL; this program does not download it first.
                    // ImportUrlResult describes accepted Upload media, not a queued
                    // Data import job. A supplied ID is not proof of playability.
                    $result = (new UploadService($client))->importUrl($request);
                    $out('ImportUrlResult accepted (HTTP ' . $result->status . '); media ID supplied: '
                        . ($result->data->hashed_id !== null ? 'yes' : 'no') . '. Processing/playability not confirmed; no job ID inferred.');
                    break;
                case 'poll-job':
                    // Poll an ALREADY EXISTING job ID. The poller only reads status;
                    // it never recreates the job or retries its originating write.
                    // Production uses real waits; tests inject a clock with no sleep.
                    $poll = (new BackgroundJobPoller(new DataBackgroundJobStatusService($client), $runtime ?? new SystemPollingRuntime()))
                        ->poll($request, $options);
                    $out('Polling: ' . $poll->termination->name . '; attempts: ' . $poll->attempts);
                    // Some jobs carry bulk results. A finished job may still contain
                    // failed items, so use the poller's termination as well as status.
                    // Summarize counts without exposing per-item remote error details.
                    $summary = $poll->last->backgroundJobStatus->object?->summary;
                    if ($summary !== null) { $out('Bulk results: succeeded=' . $summary->succeeded . '; failed=' . $summary->failed); }
                    // Exhausted means our local budget ended, not remote failure or
                    // cancellation. Stopping this process never cancels Wistia work.
                    return match ($poll->termination) {
                        PollingTermination::Completed => 0,
                        PollingTermination::ReportedFailure => 3,
                        PollingTermination::Exhausted => 4,
                    };
            }
            return 0;
        } catch (\InvalidArgumentException) {
            // Local validation/configuration problems differ from typed API failures:
            // correct the input rather than blindly repeating the same command.
            $out('Local input/configuration rejected. Check the examples README; no automatic retry.');
            return 2;
        } catch (WistiaException $error) {
            // Do not print messages, bodies, URLs, exception traces or remote error documents.
            $out('Library failure: ' . get_class($error) . '; HTTP ' . ($error->status ?? 'none'));
            // A lost response can follow a successful write. Never infer "nothing
            // happened" from a timeout, and never automatically resubmit a mutation.
            if ($error->remoteOutcomeUncertain) {
                $out('Remote write outcome uncertain. Reconcile remote state before any resubmission.');
            }
            return 1;
        } catch (\OverflowException|\UnexpectedValueException) {
            // Pagination guards stop excessive reads or a cursor that does not
            // advance. Already-seen items are only a partial result, not a full list.
            $out('Traversal stopped at its bound or a non-advancing cursor. Results may be incomplete.');
            return 4;
        }
    }

    private static function required(callable $env, string $key): string
    {
        // The library does not load .env. Let your application choose its secret
        // source. Never include the secret's value in a validation error.
        $value = $env($key);
        if (!is_string($value) || trim($value) === '') { throw new \InvalidArgumentException(); }
        return $value;
    }

    private static function upload(array $args): UploadFileRequest
    {
        // Opt in deliberately: this operation changes the remote account.
        // fromPath accepts a readable local file, not a URL. contentType labels the
        // upload; it does not inspect the file or prove that the bytes are an MP4.
        if ($args[0] !== '--execute') { throw new \InvalidArgumentException(); }
        return new UploadFileRequest(LocalFile::fromPath($args[1], contentType: 'video/mp4'));
    }

    private static function import(array $args, callable $env): ImportUrlRequest
    {
        // Keep potentially signed source URLs out of command history and output.
        // Pass the original URL intact; the library handles encoding it once.
        // low_priority: false is an explicit option, not a processing guarantee.
        if ($args[0] !== '--execute') { throw new \InvalidArgumentException(); }
        return new ImportUrlRequest(self::required($env, 'WISTIA_SOURCE_URL'), low_priority: false);
    }

    private static function attempts(string $value): int
    {
        // Bound user-selected polling to 1–99 attempts; reject coercible but invalid
        // CLI strings (zero, negatives, fractions, etc.) before converting to int.
        if (!preg_match('/^[1-9][0-9]?$/D', $value)) { throw new \InvalidArgumentException(); }
        return (int) $value;
    }
}

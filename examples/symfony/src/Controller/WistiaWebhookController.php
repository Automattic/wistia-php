<?php
declare(strict_types=1);

namespace App\Controller;

use Automattic\Wistia\WebhookParser;
use Automattic\Wistia\Webhook\{ParsingException, VerificationException};
use Symfony\Component\HttpFoundation\{JsonResponse, Request};

final class WistiaWebhookController
{
    // services.yaml injects the parser and its verifier; the controller does
    // not look up secrets or construct an outbound API client.
    public function __construct(private readonly WebhookParser $parser) {}

    public function __invoke(Request $request): JsonResponse
    {
        // Accept one received header value, never pick among duplicates. The
        // verifier also rejects comma-joined values instead of guessing a signature.
        $signatures = $request->headers->all('x-wistia-signature');
        if (count($signatures) !== 1) {
            return new JsonResponse(['error' => 'Invalid signature'], 401);
        }
        // HMAC signs exact bytes, including JSON whitespace. Use the original
        // bytes, not toArray(), getPayload(), form fields or re-encoded JSON.
        $rawBody = $request->getContent();
        // Post-buffer demonstration limit; also enforce a pre-buffer server limit.
        if (strlen($rawBody) > 1048576) {
            return new JsonResponse(['error' => 'Body too large'], 413);
        }
        try {
            // Verification is mandatory inside parse() and runs before decoding.
            // The returned typed envelope is authenticated, not yet processed.
            $delivery = $this->parser->parse($rawBody, $signatures[0]);
        } catch (VerificationException) {
            // Fixed responses reveal neither secret material nor request contents.
            return new JsonResponse(['error' => 'Invalid signature'], 401);
        } catch (ParsingException) {
            // Even authentic bytes may be invalid JSON or an invalid envelope.
            return new JsonResponse(['error' => 'Invalid envelope'], 400);
        }

        // Demonstration only: replace with durable application-owned processing
        // and acknowledgement policy before production. No storage or dispatch.
        // HMAC alone does not prevent replays or duplicates, or order events;
        // implement those controls before treating acknowledgement as durable work.
        return new JsonResponse(['verified_events' => count($delivery->events), 'processed' => false]);
    }
}

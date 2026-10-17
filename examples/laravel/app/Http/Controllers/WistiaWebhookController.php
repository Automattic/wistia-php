<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use Automattic\Wistia\WebhookParser;
use Automattic\Wistia\Webhook\{ParsingException, VerificationException};
use Illuminate\Http\{JsonResponse, Request};

final class WistiaWebhookController
{
    // This invokable controller handles the single POST route in routes/wistia.php.
    // Laravel supplies the HTTP request and container-bound verified parser.
    public function __invoke(Request $request, WebhookParser $parser): JsonResponse
    {
        // Require one received header value; do not choose between duplicate
        // signatures. Comma-joined values are also rejected by the verifier.
        $signatures = $request->headers->all('x-wistia-signature');
        if (count($signatures) !== 1) {
            return new JsonResponse(['error' => 'Invalid signature'], 401);
        }
        // HMAC covers exact bytes: even whitespace changes can invalidate it.
        // Do not use input(), json(), all(), or re-encode before verification.
        $rawBody = $request->getContent();
        // This is only a post-buffer cap. Enforce limits at your HTTP server too.
        if (strlen($rawBody) > 1048576) {
            return new JsonResponse(['error' => 'Body too large'], 413);
        }
        try {
            // parse() checks the signature before decoding the envelope. Only
            // after it returns may an application trust the envelope's integrity.
            $delivery = $parser->parse($rawBody, $signatures[0]);
        } catch (VerificationException) {
            // Unauthenticated input: return a fixed response, not body/signature data.
            return new JsonResponse(['error' => 'Invalid signature'], 401);
        } catch (ParsingException) {
            // Authentic bytes can still be invalid JSON or have an invalid envelope.
            return new JsonResponse(['error' => 'Invalid envelope'], 400);
        }

        // Demonstration hook only: deliberately no storage, dispatch or queue.
        // Replace this with durable application-owned processing before production.
        // A valid signature does not establish freshness, uniqueness or event order;
        // add replay protection/deduplication and choose when to acknowledge delivery.
        return new JsonResponse([
            'verified_events' => count($delivery->events),
            'processed' => false,
        ]);
    }
}

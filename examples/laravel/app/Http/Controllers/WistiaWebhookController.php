<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use Automattic\Wistia\WebhookParser;
use Automattic\Wistia\Webhook\{ParsingException, VerificationException};
use Illuminate\Http\{JsonResponse, Request};

final class WistiaWebhookController
{
    public function __invoke(Request $request, WebhookParser $parser): JsonResponse
    {
        $signatures = $request->headers->all('x-wistia-signature');
        if (count($signatures) !== 1) {
            return new JsonResponse(['error' => 'Invalid signature'], 401);
        }
        // Do not use input(), json(), all(), or re-encode before verification.
        $rawBody = $request->getContent();
        // This is only a post-buffer cap. Enforce limits at your HTTP server too.
        if (strlen($rawBody) > 1048576) {
            return new JsonResponse(['error' => 'Body too large'], 413);
        }
        try {
            $delivery = $parser->parse($rawBody, $signatures[0]);
        } catch (VerificationException) {
            return new JsonResponse(['error' => 'Invalid signature'], 401);
        } catch (ParsingException) {
            return new JsonResponse(['error' => 'Invalid envelope'], 400);
        }

        // Demonstration hook only: deliberately no storage, dispatch or queue.
        // Replace this with durable application-owned processing before production.
        return new JsonResponse([
            'verified_events' => count($delivery->events),
            'processed' => false,
        ]);
    }
}

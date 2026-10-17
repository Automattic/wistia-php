<?php
declare(strict_types=1);

namespace App\Controller;

use Automattic\Wistia\WebhookParser;
use Automattic\Wistia\Webhook\{ParsingException, VerificationException};
use Symfony\Component\HttpFoundation\{JsonResponse, Request};

final class WistiaWebhookController
{
    public function __construct(private readonly WebhookParser $parser) {}

    public function __invoke(Request $request): JsonResponse
    {
        $signatures = $request->headers->all('x-wistia-signature');
        if (count($signatures) !== 1) {
            return new JsonResponse(['error' => 'Invalid signature'], 401);
        }
        // Original bytes, not toArray(), getPayload(), form fields or re-encoded JSON.
        $rawBody = $request->getContent();
        // Post-buffer demonstration limit; also enforce a pre-buffer server limit.
        if (strlen($rawBody) > 1048576) {
            return new JsonResponse(['error' => 'Body too large'], 413);
        }
        try {
            $delivery = $this->parser->parse($rawBody, $signatures[0]);
        } catch (VerificationException) {
            return new JsonResponse(['error' => 'Invalid signature'], 401);
        } catch (ParsingException) {
            return new JsonResponse(['error' => 'Invalid envelope'], 400);
        }

        // Demonstration only: replace with durable application-owned processing
        // and acknowledgement policy before production. No storage or dispatch.
        return new JsonResponse(['verified_events' => count($delivery->events), 'processed' => false]);
    }
}

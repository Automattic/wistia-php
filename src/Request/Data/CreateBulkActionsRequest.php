<?php
declare(strict_types=1);

namespace Automattic\Wistia\Request\Data;

use Automattic\Wistia\Bulk\{Action, Job};
use Automattic\Wistia\Encoding\{Body, Query};

final readonly class CreateBulkActionsRequest
{
    private Body $encodedBody;

    /** @param list<Action>|null $actions */
    public function __construct(#[\SensitiveParameter] ?array $actions = null, #[\SensitiveParameter] ?Job $job = null)
    {
        if (($actions === null) === ($job === null)) { throw new \InvalidArgumentException('Provide exactly one of actions or job.'); }
        if ($actions !== null) {
            if (!array_is_list($actions) || count($actions) < 1 || count($actions) > 1000) {
                throw new \InvalidArgumentException('Provide a list of 1 to 1000 actions.');
            }
            foreach ($actions as $action) {
                if (!$action instanceof Action) { throw new \InvalidArgumentException('Expected typed bulk actions.'); }
            }
        }
        $body = Body::json($job === null ? (object) ['actions' => $actions] : (object) ['job' => $job]);
        // Decimal MB is a conservative interpretation of the documented "under 2 MB".
        if (strlen($body->contents()) >= 2_000_000) { throw new \InvalidArgumentException('Bulk body must be under 2 MB.'); }
        $this->encodedBody = $body;
    }
    public function query(): Query { return new Query(); }
    public function body(): Body { return $this->encodedBody; }
    public function __debugInfo(): array { return []; }
}

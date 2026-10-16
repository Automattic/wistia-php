<?php
declare(strict_types=1);

namespace Automattic\Wistia\Jobs;

enum BulkOperation: string { case Create = 'create'; case Update = 'update'; case Delete = 'delete'; case Move = 'move'; case Purchase = 'purchase'; }

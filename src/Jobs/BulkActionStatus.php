<?php
declare(strict_types=1);

namespace Automattic\Wistia\Jobs;

enum BulkActionStatus: string { case Succeeded = 'succeeded'; case Failed = 'failed'; }

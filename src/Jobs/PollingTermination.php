<?php
declare(strict_types=1);

namespace Automattic\Wistia\Jobs;

enum PollingTermination { case Completed; case ReportedFailure; case Exhausted; }

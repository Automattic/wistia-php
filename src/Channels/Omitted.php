<?php
declare(strict_types=1);

namespace Automattic\Wistia\Channels;

/** Distinguishes an omitted input from an explicit JSON null. */
enum Omitted { case Value; }

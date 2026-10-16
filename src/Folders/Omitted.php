<?php
declare(strict_types=1);

namespace Automattic\Wistia\Folders;

/** Distinguishes an omitted update from an explicit null reset. */
enum Omitted { case Value; }

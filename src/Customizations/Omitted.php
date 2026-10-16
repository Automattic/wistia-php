<?php
declare(strict_types=1);

namespace Automattic\Wistia\Customizations;

/** Leave the remote field unchanged; null explicitly clears a partial-update field. */
enum Omitted { case Value; }

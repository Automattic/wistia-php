<?php
declare(strict_types=1);

namespace Automattic\Wistia\Tests\Contract\Data;

final class ShowPlaybackCustomizationsTest extends CustomizationsContractCase
{
    protected function operation(): string { return 'ShowPlaybackCustomizations'; }
}

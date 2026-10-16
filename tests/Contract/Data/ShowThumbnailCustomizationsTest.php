<?php
declare(strict_types=1);

namespace Automattic\Wistia\Tests\Contract\Data;

final class ShowThumbnailCustomizationsTest extends CustomizationsContractCase
{
    protected function operation(): string { return 'ShowThumbnailCustomizations'; }
}

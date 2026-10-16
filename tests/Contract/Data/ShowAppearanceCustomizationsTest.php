<?php
declare(strict_types=1);

namespace Automattic\Wistia\Tests\Contract\Data;

final class ShowAppearanceCustomizationsTest extends CustomizationsContractCase
{
    protected function operation(): string { return 'ShowAppearanceCustomizations'; }
}

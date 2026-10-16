<?php
declare(strict_types=1);
namespace Automattic\Wistia\Brands;

/** Omission leaves a brand field unchanged; null explicitly clears it. */
enum Omitted { case Value; }

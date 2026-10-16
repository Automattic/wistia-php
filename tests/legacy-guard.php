<?php
declare(strict_types=1);

if (getenv('WISTIA_ENABLE_LEGACY_LIVE_TESTS') !== '1'
    || getenv('WISTIA_APPROVE_DESTRUCTIVE_TESTS') !== '1') {
    throw new RuntimeException('Legacy live tests require explicit live and destructive approval.');
}

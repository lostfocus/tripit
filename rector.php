<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/Src',
    ])
    // uncomment to reach your current PHP version
    ->withPhpSets(php80: true)
    ->withTypeCoverageLevel(0);

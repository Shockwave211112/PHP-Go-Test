<?php

declare(strict_types=1);

require_once __DIR__ . '/TestHelper.php';
require_once __DIR__ . '/PlacementTest.php';
require_once __DIR__ . '/AggregationTest.php';
require_once __DIR__ . '/TimezoneTest.php';
require_once __DIR__ . '/StatEventTest.php';

$tests = [];
foreach (get_defined_functions()['user'] as $fn) {
    if (str_starts_with($fn, 'test_')) {
        $tests[$fn] = $fn;
    }
}
exit(run_tests($tests));
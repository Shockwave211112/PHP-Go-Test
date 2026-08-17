<?php

declare(strict_types=1);

require_once __DIR__ . '/TestHelper.php';

function test_timezone_affect_on_aggregation(): void
{
    reset_tables();

    insert_ad_event([
        'placement_id' => 'placement-video-main',
        'occurred_at' => '2026-08-06 21:05:00.000000 +00:00',
    ]);
    insert_ad_event([
        'placement_id' => 'placement-banner-sidebar',
        'occurred_at' => '2026-08-07',
    ]);
    run_aggregation('2026-08-07', timezone: 'UTC');
    $utcRow = count(fetch_daily_stats('2026-08-07'));

    reset_tables();

    insert_ad_event([
        'placement_id' => 'placement-banner-sidebar',
        'occurred_at' => '2026-08-07',
    ]);
    insert_ad_event([
        'placement_id' => 'placement-video-main',
        'occurred_at' => '2026-08-06 21:05:00.000000 +00:00',
    ]);

    run_aggregation('2026-08-07', timezone: 'Europe/Moscow');

    $mscRow = count(fetch_daily_stats('2026-08-07'));

    assert_not_equals($utcRow, $mscRow);
}

<?php

declare(strict_types=1);

require_once __DIR__ . '/TestHelper.php';

// Несколько агрегаций - записи не клонируются
function test_aggregation_not_clone(): void
{
    reset_tables();

    $stat_date = '2026-08-07';

    insert_ad_event([
        'placement_id' => 'placement-video-main',
        'occurred_at' => $stat_date,
    ]);
    insert_ad_event([
        'placement_id' => 'placement-sport-top',
        'occurred_at' => $stat_date,
    ]);
    insert_ad_event([
        'placement_id' => 'placement-banner-sidebar',
        'occurred_at' => $stat_date,
    ]);

    run_aggregation($stat_date);
    run_aggregation($stat_date);

    $sql = "SELECT COUNT(*) FROM daily_stats WHERE stat_date = :stat_date";
    $select = db()->prepare($sql);
    $select->execute(['stat_date' => $stat_date]);
    $count = (int) $select->fetchColumn();

//    3 placement_id => 3 записи daily_stats за день, не 6
    assert_equals(3, $count);
}
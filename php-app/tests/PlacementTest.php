<?php

declare(strict_types=1);

require_once __DIR__ . '/TestHelper.php';

// С фильтром - только подходящие
function test_placement_filter_returns_only_matching_placement(): void
{
    reset_tables();

    insert_ad_event([
        'placement_id' => 'placement-video-main',
        'occurred_at' => '2026-08-07',
    ]);
    insert_ad_event([
        'placement_id' => 'placement-banner-sidebar',
        'occurred_at' => '2026-08-07',
    ]);
    run_aggregation('2026-08-07');

    $response = api_get('/api/daily-stats?date=2026-08-07&placement_id=placement-video-main');
    assert_equals(200, $response['status'], 'Ожидался HTTP 200');

    $items = $response['body']['data'] ?? $response['body']['items'];
    assert_count(1, $items, 'В ответе должна быть ровно одна запись');

    $row = $items[0];
    assert_equals('placement-video-main', $row['placement_id']);
    assert_equals('2026-08-07', $row['stat_date']);

    foreach ($items as $item) {
        if ($item['placement_id'] !== 'placement-video-main') {
            throw new RuntimeException("Фильтр вернул лишний placement");
        }
    }
}

// С неизвестным айди - пусто
function test_placement_filter_returns_empty_for_unknown_placement(): void
{
    reset_tables();

    insert_ad_event([
        'placement_id' => 'placement-video-main',
        'occurred_at' => '2026-08-07',
    ]);
    run_aggregation('2026-08-07');

    $response = api_get('/api/daily-stats?date=2026-08-07&placement_id=does-not-exist');

    assert_equals(200, $response['status']);
    $items = $response['body']['data'] ?? $response['body']['items'];
    assert_count(0, $items, 'Для неизвестного placement_id должен вернуться пустой список');
}

// Без фильтра - возвращается всё
function test_placement_filter_without_param_returns_all_placements(): void
{
    reset_tables();

    insert_ad_event([
        'placement_id' => 'placement-video-main',
        'occurred_at' => '2026-08-07',
    ]);
    insert_ad_event([
        'placement_id' => 'placement-banner-sidebar',
        'occurred_at' => '2026-08-07',
    ]);
    run_aggregation('2026-08-07');

    $response = api_get('/api/daily-stats?date=2026-08-07');
    assert_equals(200, $response['status']);

    $items = $response['body']['data'] ?? $response['body']['items'];
    assert_count(2, $items, 'Без фильтра должны вернуться все placements');
}
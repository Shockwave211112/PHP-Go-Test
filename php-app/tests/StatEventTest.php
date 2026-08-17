<?php

declare(strict_types=1);

require_once __DIR__ . '/TestHelper.php';

function test_go_stat_event_accepted(): void
{
    reset_tables();

    $response = api_get(
        '/stat?actionType=impression&placement=placement-video-main&price=12.34&requestId=req-1',
    getenv('GO_STAT_URL') ?: 'http://localhost:7011'
    );

    assert_equals(202, $response['status'], 'Ожидался HTTP 202');
}

function test_go_stat_unknown_action_type(): void
{
    reset_tables();

    $response = api_get(
        '/stat?actionType=unknown&placement=placement-video-main&price=12.34&requestId=req-1',
    getenv('GO_STAT_URL') ?: 'http://localhost:7011'
    );

    assert_equals(422, $response['status'], 'Ожидался HTTP 422');
}

function test_go_unknown_placement(): void
{
    reset_tables();

    $response = api_get(
        '/stat?actionType=impression&placement=placement-ilya-kornienko&price=12.34&requestId=req-1',
        getenv('GO_STAT_URL') ?: 'http://localhost:7011'
    );

    assert_equals(422, $response['status'], 'Ожидался HTTP 422');
}

function test_go_invalid_price(): void
{
    reset_tables();

    $response = api_get(
        '/stat?actionType=impression&placement=placement-video-main&price=-2131&requestId=req-1',
        getenv('GO_STAT_URL') ?: 'http://localhost:7011'
    );

    assert_equals(422, $response['status'], 'Ожидался HTTP 422');
}
function test_go_empty_params(): void
{
    reset_tables();

    $response = api_get(
        '/stat',
        getenv('GO_STAT_URL') ?: 'http://localhost:7011'
    );

    assert_equals(422, $response['status'], 'Ожидался HTTP 422');
}
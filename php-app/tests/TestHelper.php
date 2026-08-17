<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';

use App\Database;

function db(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $pdo = Database::connect();
    }

    return $pdo;
}

// HTTP-клиент
function api_get(string $path, string $base = 'http://localhost:8080'): array
{
    $url = "{$base}{$path}";

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => ['Accept: application/json'],
        CURLOPT_TIMEOUT        => 10,
    ]);

    $body   = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error  = curl_error($ch);
    curl_close($ch);

    if ($body === false) {
        throw new RuntimeException("cURL error while calling {$url}: {$error}");
    }

    return [
        'status' => $status,
        'body'   => json_decode($body, true),
        'raw'    => $body,
    ];
}

function reset_tables(): void
{
    db()->exec('TRUNCATE TABLE daily_stats, raw_events RESTART IDENTITY CASCADE');
}

function insert_ad_event(array $overrides = []): void
{
    $data = array_merge([
        'placement_id' => 'placement-banner-sidebar',
        'action_type' => 'impression',
        'price_cents' => 1200,
        'occurred_at' => '2026-08-06',
        'request_id' => 'seed-1',
    ], $overrides);

    $insert = db()->prepare(
        'INSERT INTO raw_events (placement_id, action_type, price_cents, occurred_at, request_id)
         VALUES (:placement_id, :action_type, :price_cents, :occurred_at, :request_id)'
    );
    $insert->execute($data);
}

function run_aggregation(string $date, ?string $timezone = null): void
{
    $envPrefix = $timezone !== null ? 'APP_TIMEZONE=' . escapeshellarg($timezone) . ' ' : '';
    $cmd = sprintf(
        '%sphp %s/../bin/console aggregate:daily %s 2>&1',
        $envPrefix,
        __DIR__,
        escapeshellarg($date)
    );
    exec($cmd, $output, $exitCode);

    if ($exitCode !== 0) {
        throw new RuntimeException("Aggregation command failed:\n" . implode("\n", $output));
    }

}

function fetch_daily_stats(string $date): array
{
    $select = db()->prepare('SELECT * FROM daily_stats WHERE stat_date = :date ORDER BY placement_id');
    $select->execute(['date' => $date]);

    return $select->fetchAll();
}

// asserts
function assert_equals(mixed $expected, mixed $actual, string $message = ''): void
{
    if ($expected !== $actual) {
        $msg = $message ?: sprintf(
            "Ожидалось: %s, получено: %s",
            var_export($expected, true),
            var_export($actual, true)
        );
        throw new RuntimeException($msg);
    }
}

function assert_not_equals(mixed $expected, mixed $actual, string $message = ''): void
{
    if ($expected === $actual) {
        $msg = $message ?: "Переменные равны";
        throw new RuntimeException($msg);
    }
}

function assert_count(int $expected, array $array, string $message = ''): void
{
    assert_equals($expected, count($array), $message ?: "Ожидалось {$expected} элементов, получено " . count($array));
}

// test-runner
function run_tests(array $tests): int
{
    $failed = 0;
    $passed = 0;

    foreach ($tests as $name => $fn) {
        echo "→ {$name} ... ";
        try {
            $fn();
            echo "OK\n";
            $passed++;
        } catch (Throwable $e) {
            echo "FAIL\n";
            echo "    " . $e->getMessage() . "\n";
            echo "    at " . $e->getFile() . ':' . $e->getLine() . "\n";
            $failed++;
        }
    }

    echo "---\nУспешно: {$passed}, С ошибкой: {$failed}\n";

    return $failed === 0 ? 0 : 1;
}
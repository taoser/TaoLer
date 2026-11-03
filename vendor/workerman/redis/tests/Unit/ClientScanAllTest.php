<?php

use Workerman\Redis\Client;

/*
|--------------------------------------------------------------------------
| *ScanAll callback-mode aggregation (pure logic, no event loop, no server)
|--------------------------------------------------------------------------
|
| The four aggregation helpers (scanAll / hScanAll / sScanAll / zScanAll)
| have two branches:
|
|   1. Coroutine branch: `!$cb && class_exists(EventLoop::class)`. Revolt is
|      NOT installed in the Unit context, so this branch is unreachable here
|      (calling it with no $cb would also try to suspend a fiber). It is left
|      to Group 8.
|   2. Callback branch: the caller passes a $cb. This chains scan() calls via
|      a recursive `$step` closure. THIS is the pure aggregation logic and is
|      fully drivable without a socket:
|
|        - scanAll($opts, $cb) queues one SCAN entry whose stored callback is
|          $step and whose stored formatter is scan()'s reshaper.
|        - process() is inert ($_connection is null on a no-constructor client),
|          so nothing is sent.
|        - We simulate the server: pop the queued entry, run a crafted RAW RESP
|          reply through its formatter, then invoke $step with the formatted
|          result. $step accumulates and either re-queues the next SCAN (cursor
|          != '0' and limit not hit) or fires the user $cb.
|
| Feeding crafted cursor pages this way exercises: loop termination on
| cursor '0', accumulation across pages, the LIMIT cap, dedup, the
| non-array/error -> false abort path, and zScanAll score-string precision.
*/

function scanClient(): Client
{
    return (new ReflectionClass(Client::class))->newInstanceWithoutConstructor();
}

/**
 * @return array<int, mixed> the raw $_queue
 */
function scanQueueRaw(Client $client): array
{
    $prop = (new ReflectionClass(Client::class))->getProperty('_queue');
    $prop->setAccessible(true);

    return $prop->getValue($client);
}

function scanSetQueue(Client $client, array $queue): void
{
    $prop = (new ReflectionClass(Client::class))->getProperty('_queue');
    $prop->setAccessible(true);
    $prop->setValue($client, $queue);
}

/**
 * Drive a *ScanAll callback loop by feeding it crafted RAW RESP scan replies.
 *
 * @param Client            $client
 * @param array<int, mixed> $pages  Each page is the raw two-element reply
 *                                  [cursorString, flatArray] that a real
 *                                  SCAN/HSCAN/etc. would return, OR a
 *                                  non-array (e.g. false / an error string)
 *                                  to exercise the abort path.
 */
function scanPump(Client $client, array $pages): void
{
    foreach ($pages as $rawReply) {
        $queue = scanQueueRaw($client);
        // The pending scan entry is the most recently queued one.
        $entry = end($queue);
        if ($entry === false) {
            throw new RuntimeException('scanPump: no pending scan entry to feed');
        }
        $key = key($queue);
        // Consume it (a real send would have removed it from the head once the
        // reply arrived; the $step closure re-queues the next scan itself).
        unset($queue[$key]);
        scanSetQueue($client, $queue);

        $cb = $entry[2];
        $format = $entry[3] ?? null;
        $formatted = $format ? $format($rawReply) : $rawReply;
        $cb($formatted);
    }
}

final class ClientScanAllTest extends \Tests\TestCase
{
    // -----------------------------------------------------------------------
    // scanAll
    // -----------------------------------------------------------------------

    public function test_scanall_accumulates_keys_across_pages_and_terminates_on_cursor_0(): void
    {
        $client = scanClient();
        $result = null;
        $client->scanAll([], function ($keys) use (&$result) {
            $result = $keys;
        });

        // Two pages: cursor "12" then "0".
        scanPump($client, [
            ['12', ['k1', 'k2']],
            ['0', ['k3']],
        ]);

        $this->assertSame(['k1', 'k2', 'k3'], $result);
    }

    public function test_scanall_fires_immediately_on_a_single_terminal_page(): void
    {
        $client = scanClient();
        $result = null;
        $client->scanAll([], function ($keys) use (&$result) {
            $result = $keys;
        });

        scanPump($client, [['0', ['only']]]);

        $this->assertSame(['only'], $result);
    }

    public function test_scanall_honours_the_limit_cap_and_stops_mid_page(): void
    {
        $client = scanClient();
        $result = null;
        $client->scanAll(['LIMIT' => 2], function ($keys) use (&$result) {
            $result = $keys;
        });

        // First page already overflows the limit of 2; loop must stop and never
        // pull the second page.
        scanPump($client, [['99', ['a', 'b', 'c']]]);

        $this->assertSame(['a', 'b'], $result);
        // No further scan was queued (we stopped at the cap, not at cursor 0).
        $this->assertSame([], scanQueueRaw($client));
    }

    public function test_scanall_hands_the_callback_false_when_scan_yields_a_non_array_error_reply(): void
    {
        $client = scanClient();
        $result = 'untouched';
        $client->scanAll([], function ($reply) use (&$result) {
            $result = $reply;
        });

        // Simulate an error reply: scan()'s formatter passes non-arrays through
        // unchanged, so $step sees a non-array and aborts with false.
        scanPump($client, ['ERR some failure']);

        $this->assertFalse($result);
    }

    public function test_scanall_forwards_match_count_type_into_the_first_scan_args_and_drops_limit(): void
    {
        $client = scanClient();
        $client->scanAll(['MATCH' => 'user:*', 'COUNT' => 100, 'LIMIT' => 5, 'TYPE' => 'string'], function () {});

        $queue = scanQueueRaw($client);
        $args = $queue[array_key_first($queue)][0];
        // First element is SCAN, second is the '0' start cursor, then the options.
        $this->assertSame('SCAN', $args[0]);
        $this->assertSame('0', $args[1]);
        // LIMIT is a client-side cap and must NOT appear on the wire.
        $this->assertFalse(in_array('LIMIT', $args, true));
        $this->assertTrue(in_array('MATCH', $args, true));
        $this->assertTrue(in_array('COUNT', $args, true));
        $this->assertTrue(in_array('TYPE', $args, true));
    }

    // -----------------------------------------------------------------------
    // hScanAll
    // -----------------------------------------------------------------------

    public function test_hscanall_merges_field_value_pairs_across_pages_into_one_map(): void
    {
        $client = scanClient();
        $result = null;
        $client->hScanAll('h', [], function ($fields) use (&$result) {
            $result = $fields;
        });

        scanPump($client, [
            ['7', ['f1', 'v1', 'f2', 'v2']],
            ['0', ['f3', 'v3']],
        ]);

        $this->assertSame(['f1' => 'v1', 'f2' => 'v2', 'f3' => 'v3'], $result);
    }

    public function test_hscanall_lets_a_later_page_overwrite_a_re_yielded_field_rehash_safety(): void
    {
        $client = scanClient();
        $result = null;
        $client->hScanAll('h', [], function ($fields) use (&$result) {
            $result = $fields;
        });

        scanPump($client, [
            ['4', ['dup', 'old']],
            ['0', ['dup', 'new', 'other', 'x']],
        ]);

        $this->assertSame(['dup' => 'new', 'other' => 'x'], $result);
    }

    // -----------------------------------------------------------------------
    // sScanAll
    // -----------------------------------------------------------------------

    public function test_sscanall_dedupes_members_revisited_during_a_rehash_and_returns_a_flat_list(): void
    {
        $client = scanClient();
        $result = null;
        $client->sScanAll('s', [], function ($members) use (&$result) {
            $result = $members;
        });

        scanPump($client, [
            ['3', ['m1', 'm2']],
            ['0', ['m2', 'm3']],   // m2 repeated
        ]);

        $this->assertSame(['m1', 'm2', 'm3'], $result);
    }

    public function test_sscanall_caps_at_limit_and_returns_array_values_a_flat_list(): void
    {
        $client = scanClient();
        $result = null;
        $client->sScanAll('s', ['LIMIT' => 2], function ($members) use (&$result) {
            $result = $members;
        });

        scanPump($client, [['8', ['x', 'y', 'z']]]);

        $this->assertSame(['x', 'y'], $result);
    }

    // -----------------------------------------------------------------------
    // zScanAll
    // -----------------------------------------------------------------------

    public function test_zscanall_merges_member_score_pairs_and_keeps_scores_as_raw_strings_precision(): void
    {
        $client = scanClient();
        $result = null;
        $client->zScanAll('z', [], function ($members) use (&$result) {
            $result = $members;
        });

        // A score that would lose precision if cast to float.
        scanPump($client, [
            ['5', ['alpha', '3.0000000000000004', 'beta', '2']],
            ['0', ['gamma', '1.5']],
        ]);

        $this->assertSame([
            'alpha' => '3.0000000000000004',
            'beta' => '2',
            'gamma' => '1.5',
        ], $result);
        // Scores stay strings — never coerced to float.
        $this->assertIsString($result['alpha']);
    }

    public function test_zscanall_aborts_to_false_on_an_error_reply(): void
    {
        $client = scanClient();
        $result = 'untouched';
        $client->zScanAll('z', [], function ($reply) use (&$result) {
            $result = $reply;
        });

        scanPump($client, ['WRONGTYPE Operation against a key holding the wrong kind of value']);

        $this->assertFalse($result);
    }
}

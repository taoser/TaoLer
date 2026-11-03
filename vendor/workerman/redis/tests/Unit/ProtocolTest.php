<?php

use Workerman\Redis\Protocols\Redis;

/**
 * A minimal ConnectionInterface stub so Redis::input() (which only needs the
 * type hint, never actually touches the connection) can be exercised in-process.
 * Returned as a fresh instance per call to avoid cross-test state.
 */
function protocolStubConnection(): \Workerman\Connection\ConnectionInterface
{
    // Signatures use only PHP 7.1-safe syntax (untyped params, ?bool, void) that
    // stays LSP-compatible with BOTH Workerman v4 (untyped ConnectionInterface,
    // exercised on the PHP 7.x legs) and v5 (typed `mixed`/`bool|null`). Avoid
    // `mixed`/union-type syntax here — it is PHP 8.0+ and would parse-error on 7.2.
    return new class extends \Workerman\Connection\ConnectionInterface {
        public function send($sendBuffer, $raw = false): ?bool { return null; }
        public function getRemoteIp(): string { return ''; }
        public function getRemotePort(): int { return 0; }
        public function getRemoteAddress(): string { return ''; }
        public function getLocalIp(): string { return ''; }
        public function getLocalPort(): int { return 0; }
        public function getLocalAddress(): string { return ''; }
        public function isIpV4(): bool { return true; }
        public function isIpV6(): bool { return false; }
        public function close($data = null, $raw = false): void {}
    };
}

final class ProtocolTest extends \Tests\TestCase
{
    public function test_encodes_a_simple_flat_command_into_resp(): void
    {
        $wire = Redis::encode(['PING']);
        $this->assertSame("*1\r\n\$4\r\nPING\r\n", $wire);
    }

    public function test_encodes_commands_with_multiple_bulk_string_args(): void
    {
        $wire = Redis::encode(['SET', 'foo', 'bar']);
        $this->assertSame("*3\r\n\$3\r\nSET\r\n\$3\r\nfoo\r\n\$3\r\nbar\r\n", $wire);
    }

    public function test_flattens_nested_array_args_mget_style(): void
    {
        $wire = Redis::encode(['MGET', ['a', 'b', 'c']]);
        $this->assertSame("*4\r\n\$4\r\nMGET\r\n\$1\r\na\r\n\$1\r\nb\r\n\$1\r\nc\r\n", $wire);
    }

    public function test_decodes_a_simple_string_reply(): void
    {
        [$type, $value] = Redis::decode("+OK\r\n");
        $this->assertSame('+', $type);
        $this->assertSame('OK', $value);
    }

    public function test_decodes_an_integer_reply(): void
    {
        [$type, $value] = Redis::decode(":42\r\n");
        $this->assertSame(':', $type);
        $this->assertSame(42, $value);
    }

    public function test_decodes_a_bulk_string_reply(): void
    {
        [$type, $value] = Redis::decode("\$5\r\nhello\r\n");
        $this->assertSame('$', $type);
        $this->assertSame('hello', $value);
    }

    public function test_decodes_a_null_bulk_string_reply(): void
    {
        [$type, $value] = Redis::decode("\$-1\r\n");
        $this->assertSame('$', $type);
        $this->assertNull($value);
    }

    public function test_decodes_an_array_reply_with_mixed_types(): void
    {
        [$type, $value] = Redis::decode("*3\r\n:1\r\n\$3\r\nfoo\r\n+OK\r\n");
        $this->assertSame('*', $type);
        $this->assertSame([1, 'foo', 'OK'], $value);
    }

    public function test_decodes_an_error_reply(): void
    {
        [$type, $value] = Redis::decode("-ERR wrong\r\n");
        $this->assertSame('-', $type);
        $this->assertSame('ERR wrong', $value);
    }

    public function test_decodes_a_nested_array_reply_scan_shape(): void
    {
        // *2\r\n$1\r\n0\r\n*2\r\n$3\r\nfoo\r\n$3\r\nbar\r\n
        $wire = "*2\r\n\$1\r\n0\r\n*2\r\n\$3\r\nfoo\r\n\$3\r\nbar\r\n";
        [$type, $value] = Redis::decode($wire);
        $this->assertSame('*', $type);
        $this->assertSame(['0', ['foo', 'bar']], $value);
    }

    public function test_decodes_a_nested_null_bulk_inside_an_array_mget_with_missing_key_shape(): void
    {
        // *3\r\n$1\r\na\r\n$-1\r\n$1\r\nb\r\n  — middle element is a nil bulk
        // at $offset > 0, which used to be missed by the strpos===0 check.
        $wire = "*3\r\n\$1\r\na\r\n\$-1\r\n\$1\r\nb\r\n";
        [$type, $value] = Redis::decode($wire);
        $this->assertSame('*', $type);
        $this->assertSame(['a', null, 'b'], $value);
    }

    public function test_decodes_a_nested_null_array_inside_an_array(): void
    {
        // *2\r\n$1\r\na\r\n*-1\r\n — null multi-bulk as a nested element.
        $wire = "*2\r\n\$1\r\na\r\n*-1\r\n";
        [$type, $value] = Redis::decode($wire);
        $this->assertSame('*', $type);
        $this->assertSame(['a', null], $value);
    }

    public function test_decodes_an_empty_array_reply_0(): void
    {
        // *0\r\n — valid RESP empty multi-bulk.
        $wire = "*0\r\n";
        [$type, $value] = Redis::decode($wire);
        $this->assertSame('*', $type);
        $this->assertSame([], $value);
    }

    public function test_encodes_an_empty_string_element_as_a_zero_length_bulk_string(): void
    {
        // Empty strings are valid RESP bulk strings: $0\r\n\r\n
        $wire = Redis::encode(['SET', 'key', '']);
        $this->assertSame("*3\r\n\$3\r\nSET\r\n\$3\r\nkey\r\n\$0\r\n\r\n", $wire);
    }

    public function test_decodes_a_deeply_nested_array_within_max_depth_without_error(): void
    {
        // Build a 60-deep nested RESP array: *1\r\n ( repeated 59 times ) $3\r\nend\r\n
        // MAX_DEPTH is 64, so this must succeed and preserve the nesting.
        $depth = 60;
        $wire = str_repeat("*1\r\n", $depth) . "\$3\r\nend\r\n";

        [$type, $value] = Redis::decode($wire);

        $this->assertSame('*', $type);

        // Unwrap all nesting levels; every level is a single-element array.
        // There are $depth layers of *1 wrapping the leaf string, so we must
        // peel exactly $depth times to reach 'end'.
        $node = $value;
        for ($i = 0; $i < $depth; $i++) {
            $this->assertIsArray($node);
            $this->assertSame(1, count($node));
            $node = $node[0];
        }
        $this->assertSame('end', $node);
    }

    public function test_decodes_an_array_deeper_than_max_depth_as_a_protocol_error(): void
    {
        // Build a 70-deep nested RESP array — beyond the MAX_DEPTH of 64.
        // decodeOne() must propagate the depth-exceeded error upward.
        $depth = 70;
        $wire = str_repeat("*1\r\n", $depth) . "\$3\r\nend\r\n";

        [$type, $value] = Redis::decode($wire);

        $this->assertSame('!', $type);
        $this->assertSame('protocol error: max array depth exceeded', $value);
    }

    public function test_input_returns_0_for_a_truncated_nested_array_frame(): void
    {
        // A valid SCAN reply looks like:
        //   *2\r\n$1\r\n0\r\n*2\r\n$3\r\nfoo\r\n$3\r\nbar\r\n
        // Truncate the last element mid-bulk-string: cut off after "$3\r\nb"
        // (the second element of the inner array is incomplete).
        $truncated = "*2\r\n\$1\r\n0\r\n*2\r\n\$3\r\nfoo\r\n\$3\r\nb";

        $len = Redis::input($truncated, protocolStubConnection());

        // The last bulk header already says 3 bytes, so the full frame size is
        // known even though "ar\r\n" has not arrived yet.
        $full = "*2\r\n\$1\r\n0\r\n*2\r\n\$3\r\nfoo\r\n\$3\r\nbar\r\n";
        $this->assertSame(\strlen($full), $len);
    }

    /*
    |--------------------------------------------------------------------------
    | input() / measure() frame-length probe
    |--------------------------------------------------------------------------
    |
    | input() returns the byte length of the next complete RESP frame in the
    | buffer, or 0 when more bytes are needed. These exercise every measure()
    | branch — the cheap, server-free wins that close the last ~10% of the
    | protocol class.
    */

    public function test_input_sizes_a_simple_string_frame_exactly(): void
    {
        // '+OK\r\n' is 5 bytes; input() must report the full frame length.
        $this->assertSame(5, Redis::input("+OK\r\n", protocolStubConnection()));
    }

    public function test_input_sizes_an_integer_frame_exactly(): void
    {
        $this->assertSame(5, Redis::input(":42\r\n", protocolStubConnection()));
    }

    public function test_input_sizes_an_error_frame_exactly(): void
    {
        // '-ERR x\r\n' is 8 bytes.
        $this->assertSame(8, Redis::input("-ERR x\r\n", protocolStubConnection()));
    }

    public function test_input_sizes_a_bulk_string_frame_including_the_payload_and_trailing_crlf(): void
    {
        // '$5\r\nhello\r\n' is 11 bytes.
        $this->assertSame(11, Redis::input("\$5\r\nhello\r\n", protocolStubConnection()));
    }

    public function test_input_sizes_a_null_bulk_string_frame_as_5_bytes(): void
    {
        // '$-1\r\n' — the measure() $-1 fast path returns the literal 5.
        $this->assertSame(5, Redis::input("\$-1\r\n", protocolStubConnection()));
    }

    public function test_input_sizes_a_null_multi_bulk_frame_as_5_bytes(): void
    {
        // '*-1\r\n' — the measure() *-1 fast path returns the literal 5.
        $this->assertSame(5, Redis::input("*-1\r\n", protocolStubConnection()));
    }

    public function test_input_sizes_an_empty_array_frame_as_4_bytes(): void
    {
        $this->assertSame(4, Redis::input("*0\r\n", protocolStubConnection()));
    }

    public function test_input_sizes_a_nested_multi_bulk_scan_frame_exactly(): void
    {
        // *2\r\n$1\r\n0\r\n*2\r\n$3\r\nfoo\r\n$3\r\nbar\r\n — sum of all child frames.
        $wire = "*2\r\n\$1\r\n0\r\n*2\r\n\$3\r\nfoo\r\n\$3\r\nbar\r\n";
        $this->assertSame(\strlen($wire), Redis::input($wire, protocolStubConnection()));
    }

    public function test_input_returns_0_for_an_empty_buffer(): void
    {
        // measure() hits the `!isset($buffer[$offset])` guard.
        $this->assertSame(0, Redis::input('', protocolStubConnection()));
    }

    public function test_input_returns_0_when_the_first_line_has_no_crlf_yet(): void
    {
        // measure() finds no "\r\n" and returns 0 (need more bytes).
        $this->assertSame(0, Redis::input('$5', protocolStubConnection()));
    }

    public function test_input_reports_a_bulk_frame_length_from_its_header_before_the_payload_arrives(): void
    {
        // '$5\r\nhello\r\n' is 11 bytes. Only 3 payload bytes are buffered, but
        // the length is already known, so a too-large bulk can be rejected now.
        $this->assertSame(11, Redis::input("\$5\r\nhel", protocolStubConnection()));
        $this->assertSame(11, Redis::input("\$5\r\nhello\r\n+OK\r\n", protocolStubConnection()));
        $this->assertSame(0, Redis::input("\$-10\r\n", protocolStubConnection()));
        $this->assertSame(0, Redis::input("*-10\r\n", protocolStubConnection()));
        // A later sibling's size is still unknown, so the array length is not.
        $this->assertSame(0, Redis::input("*2\r\n\$5\r\nhel", protocolStubConnection()));
    }

    public function test_input_returns_0_when_a_multi_bulk_element_header_has_no_crlf(): void
    {
        // Outer header complete, but the single child '$3' has no CRLF — the
        // recursive measure() call returns 0, so the whole frame is incomplete.
        $this->assertSame(0, Redis::input("*1\r\n\$3", protocolStubConnection()));
    }

    public function test_input_bails_out_with_the_buffer_length_sentinel_past_max_depth(): void
    {
        // measure() recurses one level per nested '*1'. Beyond MAX_DEPTH (64) it
        // stops descending and returns strlen-offset so input() treats the frame
        // as consumed and the decoder produces the depth-exceeded protocol error.
        // 70 levels > 64, so the sentinel branch (Redis.php line 62) fires.
        $wire = str_repeat("*1\r\n", 70) . "\$3\r\nend\r\n";
        $len = Redis::input($wire, protocolStubConnection());
        // The depth-65 sentinel (strlen-offset) propagates up through the
        // $cursor accumulation to sum to the full buffer length, so the frame is
        // treated as complete and consumed in one shot.
        $this->assertSame(\strlen($wire), $len);
    }

    public function test_input_treats_an_unknown_leading_type_byte_as_a_consumed_error_frame(): void
    {
        // measure() default branch returns strlen-offset so input() reports the
        // whole buffer as one frame; decode() then surfaces the protocol error.
        $buffer = "?bogus\r\n";
        $this->assertSame(\strlen($buffer), Redis::input($buffer, protocolStubConnection()));
    }

    /*
    |--------------------------------------------------------------------------
    | decode() / decodeOne() error & incomplete branches
    |--------------------------------------------------------------------------
    */

    public function test_decodes_a_binary_safe_bulk_string_containing_an_embedded_crlf(): void
    {
        // The $length header makes the payload binary-safe — an embedded \r\n
        // must NOT terminate the value early.
        $payload = "ab\r\ncd";                       // 6 bytes incl. the CRLF
        $wire = '$' . \strlen($payload) . "\r\n" . $payload . "\r\n";
        [$type, $value] = Redis::decode($wire);
        $this->assertSame('$', $type);
        $this->assertSame($payload, $value);
    }

    public function test_decodes_a_bulk_string_containing_a_null_byte(): void
    {
        $payload = "a\0b";
        $wire = '$' . \strlen($payload) . "\r\n" . $payload . "\r\n";
        [$type, $value] = Redis::decode($wire);
        $this->assertSame('$', $type);
        $this->assertSame($payload, $value);
    }

    public function test_decodes_a_large_bulk_string_multi_kb_intact(): void
    {
        $payload = str_repeat('x', 4096);
        $wire = '$' . \strlen($payload) . "\r\n" . $payload . "\r\n";
        [$type, $value] = Redis::decode($wire);
        $this->assertSame('$', $type);
        $this->assertSame($payload, $value);
    }

    public function test_decodes_a_negative_integer_reply(): void
    {
        [$type, $value] = Redis::decode(":-7\r\n");
        $this->assertSame(':', $type);
        $this->assertSame(-7, $value);
    }

    public function test_returns_a_protocol_error_tuple_for_an_unknown_leading_type_byte(): void
    {
        // decodeOne()'s default branch returns null → decode() builds the '!' tuple
        // with the offending byte and a hex dump of the buffer.
        [$type, $value] = Redis::decode("?nope\r\n");
        $this->assertSame('!', $type);
        $this->assertStringContainsString("got '?'", $value);
        $this->assertStringContainsString(bin2hex("?nope\r\n"), $value);
    }

    public function test_returns_a_protocol_error_tuple_for_an_empty_buffer(): void
    {
        // decodeOne() hits `!isset($buffer[$offset])` → null → decode() reports
        // an empty offending byte.
        [$type, $value] = Redis::decode('');
        $this->assertSame('!', $type);
        $this->assertStringContainsString("got ''", $value);
    }

    public function test_returns_null_shaped_protocol_error_when_the_frame_has_no_crlf(): void
    {
        // decodeOne() finds no "\r\n" → null → decode() wraps it as a '!' tuple.
        [$type, $value] = Redis::decode("+partial");
        $this->assertSame('!', $type);
    }

    public function test_propagates_a_depth_exceeded_error_from_a_nested_element(): void
    {
        // An array whose single child blows MAX_DEPTH: the inner decodeOne returns
        // the '!' depth error, which the parent loop propagates instead of nesting.
        $depth = 70;
        $wire = str_repeat("*1\r\n", $depth) . "\$3\r\nend\r\n";
        [$type, $value] = Redis::decode($wire);
        $this->assertSame('!', $type);
        $this->assertSame('protocol error: max array depth exceeded', $value);
    }

    public function test_returns_a_protocol_error_when_a_nested_array_element_has_no_crlf(): void
    {
        // Outer *2 promises two children; the first decodes, but the second
        // ('+ab' with no trailing CRLF) makes decodeOne return null, which the
        // parent loop propagates → decode() surfaces a '!' protocol-error tuple.
        // (A truncated *bulk* would NOT hit this: decodeOne's $ branch substr()s
        // optimistically, trusting input() to have gated completeness first.)
        [$type] = Redis::decode("*2\r\n:1\r\n+ab");
        $this->assertSame('!', $type);
    }
}

<?php

final class SmokeTest extends \Tests\RedisTestCase
{
    public function test_round_trips_set_and_get_via_runinworker(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->set('pest:smoke:k', 'hello-pest');
            $redis->get('pest:smoke:k', function ($value) use ($emit) {
                $emit($value);
            });
PHP
        );
        $this->assertSame('hello-pest', $result);
    }

    public function test_routes_call_commands_through_the_dispatcher(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->del('pest:smoke:counter');
            $redis->incr('pest:smoke:counter');
            $redis->incr('pest:smoke:counter');
            $redis->incr('pest:smoke:counter', function ($value) use ($emit) {
                $emit($value);
            });
PHP
        );
        $this->assertSame(3, $result);
    }
}

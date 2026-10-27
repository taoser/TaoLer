<?php

namespace test;

use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use ReflectionProperty;
use Workerman\Coroutine;
use Workerman\Coroutine\Exception\PoolException;
use Workerman\Coroutine\Pool;
use Psr\Log\LoggerInterface;
use ReflectionClass;
use stdClass;
use Exception;
use Workerman\Events\Event;
use Workerman\Events\Select;
use Workerman\Timer;
use Workerman\Worker;

class PoolTest extends TestCase
{

    public function testConstructorWithConfig()
    {
        $config = [
            'min_connections' => 2,
            'idle_timeout' => 30,
            'heartbeat_interval' => 10,
            'wait_timeout' => 5,
        ];
        $pool = new Pool(10, $config);

        $this->assertEquals(10, $this->getPrivateProperty($pool, 'maxConnections'));
        $this->assertEquals(2, $this->getPrivateProperty($pool, 'minConnections'));
        $this->assertEquals(30, $this->getPrivateProperty($pool, 'idleTimeout'));
        $this->assertEquals(10, $this->getPrivateProperty($pool, 'heartbeatInterval'));
        $this->assertEquals(5, $this->getPrivateProperty($pool, 'waitTimeout'));
    }

    public function testSetConnectionCreator()
    {
        $pool = new Pool(5);
        $connectionCreator = function () {
            return new stdClass();
        };
        $pool->setConnectionCreator($connectionCreator);
        $this->assertSame($connectionCreator, $this->getPrivateProperty($pool, 'connectionCreateHandler'));
    }

    public function testSetConnectionCloser()
    {
        $pool = new Pool(5);
        $connectionCloser = function ($conn) {
            // Close connection.
        };
        $pool->setConnectionCloser($connectionCloser);
        $this->assertSame($connectionCloser, $this->getPrivateProperty($pool, 'connectionDestroyHandler'));
    }

    public function testGetConnection()
    {
        $pool = new Pool(5);

        $connectionMock = $this->createMock(stdClass::class);

        // 设置连接创建器
        $pool->setConnectionCreator(function () use ($connectionMock) {
            return $connectionMock;
        });

        $connection = $pool->get();

        $this->assertSame($connectionMock, $connection);
        $this->assertEquals(1, $this->getCurrentConnections($pool));

        // 检查 WeakMap 是否更新
        $connections = $this->getPrivateProperty($pool, 'connections');
        $lastUsedTimes = $this->getPrivateProperty($pool, 'lastUsedTimes');
        $lastHeartbeatTimes = $this->getPrivateProperty($pool, 'lastHeartbeatTimes');

        $this->assertTrue($connections->offsetExists($connection));
        $this->assertTrue($lastUsedTimes->offsetExists($connection));
        $this->assertTrue($lastHeartbeatTimes->offsetExists($connection));
    }

    public function testPutConnection()
    {
        $pool = new Pool(5);

        $connectionMock = $this->createMock(stdClass::class);

        $pool->setConnectionCreator(function () use ($connectionMock) {
            return $connectionMock;
        });

        $connection = $pool->get();

        $pool->put($connection);

        if (Coroutine::isCoroutine()) {
            $channel = $this->getPrivateProperty($pool, 'channel');
            $this->assertEquals(1, $channel->length());
        }

        $this->assertEquals(1, $pool->getConnectionCount());
    }

    public function testPutConnectionDoesNotBelong()
    {
        $this->expectException(PoolException::class);
        $this->expectExceptionMessage('The connection does not belong to the connection pool.');

        $pool = new Pool(5);
        $connection = new stdClass();

        $pool->put($connection);
    }

    public function testPutSameConnectionTwice()
    {
        $pool = new Pool(2);
        $created = 0;
        $closed = 0;
        $pool->setConnectionCreator(function () use (&$created) {
            $created++;
            return new stdClass();
        });
        $pool->setConnectionCloser(function () use (&$closed) {
            $closed++;
        });

        $connection = $pool->get();
        $pool->put($connection);

        // 非协程下归还是空操作，重复归还不能抛异常，也不能新建连接。
        if (!Coroutine::isCoroutine()) {
            $pool->put($connection);
            $this->assertSame($connection, $pool->get());
            $this->assertEquals(1, $created);
            $this->assertEquals(0, $closed);
            $this->assertEquals(1, $pool->getConnectionCount());
            return;
        }

        try {
            $pool->put($connection);
            $this->fail('Putting the same connection twice should throw.');
        } catch (PoolException $exception) {
            $this->assertSame('The connection has already been returned to the connection pool.', $exception->getMessage());
        }

        $channel = $this->getPrivateProperty($pool, 'channel');
        $this->assertEquals(1, $channel->length());
        $this->assertEquals(1, $created);
        $this->assertEquals(0, $closed);
        $this->assertEquals(1, $pool->getConnectionCount());

        $first = $pool->get();
        $second = $pool->get();
        $this->assertSame($connection, $first);
        $this->assertNotSame($first, $second);
        $this->assertEquals(2, $pool->getConnectionCount());

        $pool->put($first);
        $pool->put($second);
        $this->assertEquals(2, $channel->length());

        // 池子已满时，重复归还必须立刻失败，不能阻塞在通道写入上。
        $repeatedReturnFinished = false;
        $repeatedReturnException = null;
        Coroutine::create(function () use ($pool, $first, &$repeatedReturnFinished, &$repeatedReturnException) {
            try {
                $pool->put($first);
            } catch (PoolException $exception) {
                $repeatedReturnException = $exception;
            }
            $repeatedReturnFinished = true;
        });
        Timer::sleep(0.05);

        $this->assertTrue($repeatedReturnFinished);
        $this->assertInstanceOf(PoolException::class, $repeatedReturnException);
        $this->assertSame('The connection has already been returned to the connection pool.', $repeatedReturnException->getMessage());
        $this->assertEquals(2, $channel->length());
        $this->assertEquals(2, $created);
        $this->assertEquals(0, $closed);
        $this->assertEquals(2, $pool->getConnectionCount());
    }

    public function testGetDiscardsConnectionClosedAfterReturn()
    {
        $pool = new Pool(3, ['wait_timeout' => 2]);
        $closed = [];
        $pool->setConnectionCreator(function () {
            return new stdClass();
        });
        $pool->setConnectionCloser(function (object $connection) use (&$closed) {
            $closed[spl_object_id($connection)] = $connection;
        });

        $connection = $pool->get();
        $pool->put($connection);
        $pool->closeConnection($connection);

        $this->assertCount(1, $closed);
        $this->assertSame($connection, $closed[spl_object_id($connection)]);
        $this->assertEquals(0, $pool->getConnectionCount());

        // 非协程连接不会进入通道，关闭后下次 get() 直接新建。
        if (!Coroutine::isCoroutine()) {
            $replacement = $pool->get();
            $this->assertNotSame($connection, $replacement);
            $this->assertEquals(1, $pool->getConnectionCount());
            return;
        }

        $kept = $pool->get();
        $discarded = [$pool->get(), $pool->get()];
        $pool->put($kept);
        foreach ($discarded as $item) {
            $pool->put($item);
            $pool->closeConnection($item);
        }

        $started = microtime(true);
        $borrowed = $pool->get();
        $elapsed = microtime(true) - $started;

        $this->assertSame($kept, $borrowed);
        $this->assertArrayNotHasKey(spl_object_id($borrowed), $closed);
        $this->assertLessThan(0.5, $elapsed);
        $this->assertEquals(1, $pool->getConnectionCount());
        $this->assertCount(3, $closed);
    }

    public function testCheckConnectionsSkipsConnectionClosedAfterReturn()
    {
        if (!Coroutine::isCoroutine()) {
            $this->assertTrue(true);
            return;
        }

        $pool = new Pool(3, ['idle_timeout' => 3600, 'min_connections' => 1]);
        $closed = 0;
        $pool->setConnectionCreator(function () {
            return new stdClass();
        });
        $pool->setConnectionCloser(function () use (&$closed) {
            $closed++;
        });

        $closedConnection = $pool->get();
        $kept = $pool->get();
        $pool->put($closedConnection);
        $pool->put($kept);
        $pool->closeConnection($closedConnection);

        $this->assertEquals(1, $closed);
        $this->assertEquals(1, $pool->getConnectionCount());

        $reflectedMethod = new ReflectionMethod($pool, 'checkConnections');
        $reflectedMethod->invoke($pool);

        $channel = $this->getPrivateProperty($pool, 'channel');
        $this->assertEquals(1, $channel->length());
        $this->assertEquals(1, $closed);
        $this->assertSame($kept, $pool->get());

        // 队列里只剩已关闭的连接时，检查过程也不能抛错。
        $pool->put($kept);
        $pool->closeConnection($kept);
        $reflectedMethod->invoke($pool);

        $this->assertEquals(0, $channel->length());
        $this->assertEquals(0, $pool->getConnectionCount());
        $this->assertEquals(2, $closed);
    }

    public function testConnectionClosedDuringHeartbeatIsNotReused()
    {
        if (!Coroutine::isCoroutine()) {
            $this->assertTrue(true);
            return;
        }

        $pool = new Pool(1, [
            'heartbeat_interval' => 0,
            'idle_timeout' => 3600,
            'min_connections' => 0,
        ]);
        $closed = [];
        $pool->setConnectionCreator(function () {
            return new stdClass();
        });
        $pool->setConnectionCloser(function (object $connection) use (&$closed) {
            $closed[spl_object_id($connection)] = true;
        });
        $pool->setHeartbeatChecker(function (object $connection) use ($pool) {
            $pool->closeConnection($connection);
        });

        $connection = $pool->get();
        $pool->put($connection);

        $reflectedMethod = new ReflectionMethod($pool, 'checkConnections');
        $reflectedMethod->invoke($pool);
        $reflectedMethod->invoke($pool);

        $replacement = $pool->get();
        $this->assertNotSame($connection, $replacement);
        $this->assertArrayHasKey(spl_object_id($connection), $closed);
        $this->assertArrayNotHasKey(spl_object_id($replacement), $closed);
        $this->assertEquals(1, $pool->getConnectionCount());
        $this->assertCount(1, $closed);
    }

    public function testConcurrentBorrowReturnAndClose()
    {
        if (in_array(Worker::$eventLoopClass, [Select::class, Event::class], true)) {
            $this->assertTrue(true);
            return;
        }

        $maxConnections = 8;
        $workers = 60;
        $rounds = 40;
        $pool = new Pool($maxConnections, [
            'wait_timeout' => 5,
            'idle_timeout' => 3600,
            'heartbeat_interval' => 0,
            'min_connections' => 1,
        ]);
        $errors = [];
        $done = 0;
        $operations = 0;
        $closedCount = 0;
        $inUse = [];
        $pool->setConnectionCreator(function () {
            return new stdClass();
        });
        $pool->setConnectionCloser(function (object $connection) use (&$closedCount) {
            $connection->closed = true;
            $closedCount++;
        });
        $pool->setHeartbeatChecker(function () {
            Timer::sleep(0.001);
        });

        $reflectedMethod = new ReflectionMethod($pool, 'checkConnections');
        Coroutine::create(function () use ($pool, $reflectedMethod, $workers, &$done) {
            while ($done < $workers) {
                $reflectedMethod->invoke($pool);
                Timer::sleep(0.005);
            }
        });

        for ($i = 0; $i < $workers; $i++) {
            Coroutine::create(function () use ($pool, $rounds, $maxConnections, &$inUse, &$errors, &$done, &$operations) {
                try {
                    for ($n = 0; $n < $rounds; $n++) {
                        $connection = $pool->get();
                        $id = spl_object_id($connection);
                        if (isset($inUse[$id])) {
                            $errors[] = "connection $id borrowed twice";
                        }
                        if (!empty($connection->closed)) {
                            $errors[] = "closed connection $id was borrowed again";
                        }
                        $inUse[$id] = true;
                        Timer::sleep(0.001);
                        unset($inUse[$id]);
                        if ($n % 10 === 0) {
                            $pool->closeConnection($connection);
                        } else {
                            $pool->put($connection);
                        }
                        $operations++;
                        if ($pool->getConnectionCount() > $maxConnections) {
                            $errors[] = 'connection count exceeded the pool limit';
                        }
                    }
                } catch (\Throwable $throwable) {
                    $errors[] = $throwable::class . ': ' . $throwable->getMessage();
                } finally {
                    $done++;
                }
            });
        }

        $deadline = microtime(true) + 60;
        while ($done < $workers && microtime(true) < $deadline) {
            Timer::sleep(0.02);
        }

        $this->assertSame($workers, $done, implode(PHP_EOL, array_slice($errors, 0, 8)));
        $this->assertCount(0, $errors, implode(PHP_EOL, array_slice($errors, 0, 8)));
        $this->assertSame($workers * $rounds, $operations);
        $this->assertSame([], $inUse);
        $this->assertLessThanOrEqual($maxConnections, $pool->getConnectionCount());
        $this->assertGreaterThan(0, $closedCount);
    }

    public function testCreateConnection()
    {
        $pool = new Pool(5);
        $connectionMock = $this->createMock(stdClass::class);

        $pool->setConnectionCreator(function () use ($connectionMock) {
            return $connectionMock;
        });

        $connection = $pool->createConnection();

        $this->assertSame($connectionMock, $connection);

        // 确保 currentConnections 增加
        $this->assertEquals(1, $this->getCurrentConnections($pool));

        // 检查 WeakMap 是否更新
        $connections = $this->getPrivateProperty($pool, 'connections');
        $lastUsedTimes = $this->getPrivateProperty($pool, 'lastUsedTimes');
        $lastHeartbeatTimes = $this->getPrivateProperty($pool, 'lastHeartbeatTimes');

        $this->assertTrue($connections->offsetExists($connection));
        $this->assertTrue($lastUsedTimes->offsetExists($connection));
        $this->assertTrue($lastHeartbeatTimes->offsetExists($connection));
    }

    public function testCreateMaxConnections()
    {
        if (in_array(Worker::$eventLoopClass, [Select::class, Event::class])) {
            $this->assertTrue(true);
            return;
        }
        $maxConnections = 2;
        $pool = new Pool($maxConnections);

        $pool->setConnectionCreator(function () {
            Timer::sleep(0.01);
            return $this->createMock(stdClass::class);
        });

        $connections = [];
        for ($i = 0; $i < 3; $i++) {
            Coroutine::create(function () use ($pool, &$connections) {
                $connections[] = $pool->get();
            });
        }

        Timer::sleep(0.1);
        $this->assertEquals($maxConnections, $this->getCurrentConnections($pool));

        $lastUsedTimes = $this->getPrivateProperty($pool, 'lastUsedTimes');
        $lastHeartbeatTimes = $this->getPrivateProperty($pool, 'lastHeartbeatTimes');

        $this->assertCount($maxConnections, $lastUsedTimes);
        $this->assertCount($maxConnections, $lastHeartbeatTimes);

        foreach ($connections as $connection) {
            $pool->put($connection);
        }

    }

    public function testCreateConnectionThrowsException()
    {
        $pool = new Pool(5);

        $pool->setConnectionCreator(function () {
            throw new Exception('Failed to create connection');
        });

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Failed to create connection');

        try {
            $pool->createConnection();
        } finally {
            // 确保 currentConnections 减少
            $this->assertEquals(0, $this->getCurrentConnections($pool));
        }
    }

    public function testCloseConnection()
    {
        $pool = new Pool(5);

        $connection = $this->createMock(ConnectionMock::class);

        // 模拟连接属于连接池
        $connections = $this->getPrivateProperty($pool, 'connections');
        $connections[$connection] = time();

        $connection->expects($this->once())->method('close');
        $pool->setConnectionCloser(function ($conn) {
            $conn->close();
        });

        $pool->closeConnection($connection);

        // 确保 currentConnections 减少
        $this->assertEquals(0, $this->getCurrentConnections($pool));

        // 确保连接从 WeakMap 中移除
        $this->assertFalse($connections->offsetExists($connection));
    }

    public function testCloseConnections()
    {
        $maxConnections = 5;

        $pool = new Pool($maxConnections);

        $pool->setConnectionCreator(function () {
            $connection = $this->createMock(ConnectionMock::class);
            $connection->expects($this->once())->method('close');
            return $connection;
        });

        $pool->setConnectionCloser(function ($conn) {
            $conn->close();
        });

        $connections = [];
        for ($i = 0; $i < $maxConnections; $i++) {
            $connections[] = $pool->get();
        }

        $this->assertEquals(Coroutine::isCoroutine() ? $maxConnections : 1, $this->getCurrentConnections($pool));

        $pool->closeConnections();
        $this->assertEquals(Coroutine::isCoroutine() ? $maxConnections : 0, $this->getCurrentConnections($pool));
        if (!Coroutine::isCoroutine()) {
            return;
        }

        foreach ($connections as $connection) {
            $pool->put($connection);
        }
        $this->assertEquals($maxConnections, $this->getCurrentConnections($pool));
        $pool->closeConnections();
        $this->assertEquals(0, $this->getCurrentConnections($pool));

        $connections = [];
        for ($i = 0; $i < $maxConnections; $i++) {
            $connections[] = $pool->get();
        }
        $this->assertEquals($maxConnections, $this->getCurrentConnections($pool));
        foreach ($connections as $connection) {
            $pool->put($connection);
        }
        $pool->closeConnections();
        unset($connections);
        $this->assertEquals(0, $this->getCurrentConnections($pool));
    }

    public function testCloseConnectionWithExceptionInDestroyHandler()
    {
        $pool = new Pool(5);

        $connection = $this->createMock(stdClass::class);

        // 模拟连接属于连接池
        $connections = $this->getPrivateProperty($pool, 'connections');
        $connections[$connection] = time();

        $exception = new Exception('Error closing connection');

        $pool->setConnectionCloser(function ($conn) use ($exception) {
            throw $exception;
        });

        // 设置日志记录器
        $loggerMock = $this->createMock(LoggerInterface::class);
        $loggerMock->expects($this->once())
            ->method('info')
            ->with($this->stringContains('Error closing connection'));

        $this->setPrivateProperty($pool, 'logger', $loggerMock);

        $pool->closeConnection($connection);

        // 确保 currentConnections 减少
        $this->assertEquals(0, $this->getCurrentConnections($pool));

        // 确保连接从 WeakMap 中移除
        $this->assertFalse($connections->offsetExists($connection));
    }

    public function testHeartbeatChecker()
    {
        $pool = $this->getMockBuilder(Pool::class)
            ->setConstructorArgs([5])
            ->onlyMethods(['closeConnection'])
            ->getMock();

        $connection = $this->createMock(stdClass::class);

        // 设置连接心跳检测器
        $pool->setHeartbeatChecker(function ($conn) {
            // 模拟心跳检测
        });

        // 模拟连接在通道中
        $channel = $this->getPrivateProperty($pool, 'channel');
        $channel->push($connection);

        // 设置连接的上次使用时间和心跳时间
        $connections = $this->getPrivateProperty($pool, 'connections');
        $connections[$connection] = time();

        $lastUsedTimes = $this->getPrivateProperty($pool, 'lastUsedTimes');
        $lastUsedTimes[$connection] = time();

        $lastHeartbeatTimes = $this->getPrivateProperty($pool, 'lastHeartbeatTimes');
        $lastHeartbeatTimes[$connection] = time() - 100; // 超过心跳间隔

        // 调用受保护的 checkConnections 方法
        $reflectedMethod = new ReflectionMethod($pool, 'checkConnections');
        $reflectedMethod->invoke($pool);

        // 检查心跳时间是否更新
        $lastHeartbeatTimes = $this->getPrivateProperty($pool, 'lastHeartbeatTimes');
        $this->assertGreaterThan(time() - 2, $lastHeartbeatTimes[$connection]);
    }

    public function testConnectionDestroyedWithoutReturn()
    {
        $pool = new Pool(5);

        // 设置连接创建器
        $pool->setConnectionCreator(function () {
            return new stdClass;
        });

        // 获取初始的 currentConnections
        $initialConnections = $this->getCurrentConnections($pool);

        // 从连接池获取一个连接
        $connection = $pool->get();

        // 检查 currentConnections 是否增加
        $this->assertEquals(Coroutine::isCoroutine() ? $initialConnections + 1 : 1, $this->getCurrentConnections($pool));

        // 不归还连接，并销毁连接对象
        unset($connection);

        // 检查 currentConnections 是否减少
        $this->assertEquals(Coroutine::isCoroutine() ? $initialConnections : 1, $this->getCurrentConnections($pool));
    }

    private function getPrivateProperty($object, string $property)
    {
        $prop = new ReflectionProperty($object, $property);
        return $prop->getValue($object);
    }

    private function setPrivateProperty($object, string $property, $value)
    {
        $prop = new ReflectionProperty($object, $property);
        $prop->setValue($object, $value);
    }

    private function getCurrentConnections($object): int
    {
        return $object->getConnectionCount();
    }

}

// 定义 ConnectionMock 类用于测试
class ConnectionMock
{
    public function close()
    {
        // 模拟关闭连接
    }
}

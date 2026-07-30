<?php

namespace think\worker\conduit\driver;

use Exception;
use Revolt\EventLoop;
use Revolt\EventLoop\Suspension;
use think\worker\conduit\Driver;
use think\worker\conduit\driver\socket\Command;
use think\worker\conduit\driver\socket\Event;
use think\worker\conduit\driver\socket\Result;
use think\worker\conduit\driver\socket\Server;
use think\worker\Manager;
use Workerman\Connection\AsyncTcpConnection;
use Workerman\Protocols\Frame;
use Workerman\Timer;

class Socket extends Driver
{
    protected $id = 0;
    protected $domain;

    /** @var AsyncTcpConnection|null */
    protected $connection   = null;
    protected $reconnectTimer;
    protected $pingInterval = 55;

    /** @var array<int, array{0: Suspension, 1: int}> */
    protected $suspensions = [];
    protected $events      = [];

    public function __construct(protected Manager $manager)
    {
        $filename = runtime_path() . 'conduit.sock';
        @unlink($filename);
        $this->domain = "unix://{$filename}";
    }

    public function prepare()
    {
        //启动服务端
        Server::run($this->domain);
    }

    public function connect()
    {
        $suspension       = EventLoop::getSuspension();
        $this->connection = $this->createConnection($suspension);
        $suspension->suspend();

        Timer::add($this->pingInterval, function () {
            if ($this->connection) {
                $this->connection->send('');
            }
        });

        Timer::add(1, function () {
            //检查是否超时
            foreach ($this->suspensions as $id => $suspension) {
                if (time() - $suspension[1] > 10) {
                    $suspension[0]->throw(new Exception('conduit connection is timeout'));
                    unset($this->suspensions[$id]);
                }
            }
        });
    }

    public function get(string $name)
    {
        return $this->sendAndRecv(Command::create('get', $name));
    }

    public function set(string $name, $value)
    {
        $this->send(Command::create('set', $name, $value));
    }

    public function inc(string $name, int $step = 1)
    {
        return $this->sendAndRecv(Command::create('inc', $name, $step));
    }

    public function sAdd(string $name, ...$value)
    {
        $this->send(Command::create('sAdd', $name, $value));
    }

    public function sRem(string $name, $value)
    {
        $this->send(Command::create('sRem', $name, $value));
    }

    public function sMembers(string $name)
    {
        return $this->sendAndRecv(Command::create('sMembers', $name));
    }

    public function publish(string $name, $value)
    {
        $this->send(Command::create('publish', $name, $value));
    }

    public function subscribe(string $name, $callback)
    {
        $this->send(Command::create('subscribe', $name));
        $this->events[$name] = $callback;
    }

    protected function sendAndRecv(Command $command)
    {
        $suspension = EventLoop::getSuspension();

        $id = $this->id++;

        $command->id = $id;

        $this->suspensions[$id] = [$suspension, time()];

        $this->send($command);

        return $suspension->suspend();
    }

    protected function send(Command $command)
    {
        if (!$this->connection) {
            throw new Exception('conduit connection is disconnected');
        }

        $this->connection->send(serialize($command));
    }

    protected function createConnection(?Suspension $suspension = null)
    {
        $connection = new AsyncTcpConnection($this->domain);

        $connection->protocol = Frame::class;

        $connection->onConnect = function () use ($suspension) {
            $this->clearTimer();
            if ($suspension) {
                $suspension->resume();
            }
            //补订阅
            foreach ($this->events as $name => $callback) {
                $this->send(Command::create('subscribe', $name));
            }
        };

        $connection->onMessage = function ($connection, $buffer) {
            /** @var Result|Event $result */
            $result = unserialize($buffer);

            if ($result instanceof Event) {
                if (isset($this->events[$result->name])) {
                    $this->events[$result->name]($result->data);
                }
            } elseif (isset($result->id) && isset($this->suspensions[$result->id])) {
                [$suspension] = $this->suspensions[$result->id];
                $suspension->resume($result->data);
                unset($this->suspensions[$result->id]);
            }
        };

        $connection->onClose = function () {
            $this->connection = null;
            //重连
            $this->clearTimer();
            $this->reconnectTimer = Timer::add(1, function () {
                $this->connection = $this->createConnection();
            });
        };

        $connection->connect();

        return $connection;
    }

    protected function clearTimer()
    {
        if ($this->reconnectTimer) {
            Timer::del($this->reconnectTimer);
            $this->reconnectTimer = null;
        }
    }
}

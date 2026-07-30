<?php

namespace think\worker;

use RuntimeException;
use think\Event;
use think\worker\websocket\Pusher;
use think\worker\websocket\Room;
use Workerman\Connection\TcpConnection;

/**
 * Class Websocket
 */
class Websocket
{
    /**
     * @var \think\App
     */
    protected $app;

    /**
     * @var Room
     */
    protected $room;

    /**
     * @var string
     */
    protected $sender;

    /** @var Event */
    protected $event;

    /** @var TcpConnection|null */
    protected $connection;

    protected $connected = true;

    /**
     * Websocket constructor.
     *
     * @param \think\App $app
     * @param Room $room
     * @param Event $event
     */
    public function __construct(\think\App $app, Room $room, Event $event, ?TcpConnection $connection = null)
    {
        $this->app        = $app;
        $this->room       = $room;
        $this->event      = $event;
        $this->connection = $connection;
    }

    /**
     * @return Pusher
     */
    protected function makePusher()
    {
        return $this->app->invokeClass(Pusher::class);
    }

    public function to(...$values)
    {
        return $this->makePusher()->to(...$values);
    }

    public function push($data)
    {
        $this->makePusher()->to($this->getSender())->push($data);
    }

    public function emit(string $event, ...$data)
    {
        $this->makePusher()->to($this->getSender())->emit($event, ...$data);
    }

    public function join(...$rooms): self
    {
        $this->room->add($this->getSender(), ...$rooms);

        return $this;
    }

    public function leave(...$rooms): self
    {
        $this->room->delete($this->getSender(), ...$rooms);

        return $this;
    }

    public function setConnected($connected)
    {
        $this->connected = $connected;
    }

    public function isEstablished()
    {
        return $this->connected;
    }

    public function close()
    {
        if ($this->connection) {
            $this->connection->close();
        }
    }

    public function setSender(string $fd)
    {
        $this->sender = $fd;
        return $this;
    }

    public function getSender()
    {
        if (empty($this->sender)) {
            throw new RuntimeException('Cannot use websocket as current client before handshake!');
        }
        return $this->sender;
    }
}

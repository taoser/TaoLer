<?php

namespace think\worker\websocket;

use think\worker\Conduit;

class Room
{

    public function __construct(protected Conduit $conduit)
    {

    }

    public function add($fd, ...$rooms)
    {
        $this->conduit->sAdd($this->getClientKey($fd), ...$rooms);

        foreach ($rooms as $room) {
            $this->conduit->sAdd($this->getRoomKey($room), $fd);
        }
    }

    public function delete($fd, ...$rooms)
    {
        $rooms = count($rooms) ? $rooms : $this->getRooms($fd);

        $this->conduit->sRem($this->getClientKey($fd), ...$rooms);

        foreach ($rooms as $room) {
            $this->conduit->sRem($this->getRoomKey($room), $fd);
        }
    }

    public function getClients(string $room)
    {
        return $this->conduit->sMembers($this->getRoomKey($room)) ?: [];
    }

    public function getRooms(string $fd)
    {
        return $this->conduit->sMembers($this->getClientKey($fd)) ?: [];
    }

    protected function getClientKey(string $key)
    {
        return "ws:client:{$key}";
    }

    protected function getRoomKey($room)
    {
        return "ws:room:{$room}";
    }
}

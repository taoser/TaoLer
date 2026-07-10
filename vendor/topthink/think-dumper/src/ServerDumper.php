<?php

namespace think\dumper;

use Symfony\Component\VarDumper\Cloner\Data;
use Symfony\Component\VarDumper\Dumper\DataDumperInterface;

class ServerDumper implements DataDumperInterface
{
    protected Connection $connection;

    protected ?DataDumperInterface $wrappedDumper;

    public function __construct(?DataDumperInterface $wrappedDumper = null, array $contextProviders = [])
    {
        $this->connection    = new Connection($contextProviders);
        $this->wrappedDumper = $wrappedDumper;
    }

    /**
     * @return string|null
     */
    public function dump(Data $data)
    {
        if (!$this->connection->write($data) && $this->wrappedDumper) {
            return $this->wrappedDumper->dump($data);
        }

        return null;
    }
}

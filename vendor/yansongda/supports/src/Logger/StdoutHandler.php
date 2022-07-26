<?php

declare(strict_types=1);

namespace Yansongda\Supports\Logger;

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

class StdoutHandler extends AbstractProcessingHandler
{
    /**
     * @var \Symfony\Component\Console\Output\OutputInterface
     */
    private $output;

    public function __construct($level = Logger::DEBUG, bool $bubble = true, ?OutputInterface $output = null)
    {
        $this->output = $output ?? new ConsoleOutput();

        parent::__construct($level, $bubble);
    }

    /**
     * Writes the record down to the log of the implementing handler.
     */
    protected function write(array $record): void
    {
        $this->output->writeln($record['formatted']);
    }
}

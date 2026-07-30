<?php

namespace think\worker\watcher;

use Symfony\Component\Process\Process;
use Workerman\Timer;

class Find implements Driver
{
    protected $name;
    protected $directory;
    protected $exclude;

    public function __construct($directory, $exclude, $name)
    {
        $this->directory = $directory;
        $this->exclude   = $exclude;
        $this->name      = $name;
    }

    public function watch(callable $callback)
    {
        $ms      = 2000;
        $seconds = ceil(($ms + 1000) / 1000);
        $minutes = sprintf('-%.2f', $seconds / 60);

        $dest = implode(' ', $this->directory);

        $name    = empty($this->name) ? '' : ' \( ' . join(' -o ', array_map(fn($v) => "-name \"{$v}\"", $this->name)) . ' \)';
        $notName = '';
        $notPath = '';
        if (!empty($this->exclude)) {
            $excludeDirs = $excludeFiles = [];
            foreach ($this->exclude as $directory) {
                $directory = rtrim($directory, '/');
                if (is_dir($directory)) {
                    $excludeDirs[] = $directory;
                } else {
                    $excludeFiles[] = $directory;
                }
            }

            if (!empty($excludeFiles)) {
                $notPath = ' -not \( ' . join(' -and ', array_map(fn($v) => "-name \"{$v}\"", $excludeFiles)) . ' \)';
            }

            if (!empty($excludeDirs)) {
                $notPath = ' -not \( ' . join(' -and ', array_map(fn($v) => "-path \"{$v}/*\"", $excludeDirs)) . ' \)';
            }
        }

        $command = "find {$dest}{$name}{$notName}{$notPath} -mmin {$minutes} -type f -print";

        Timer::add($ms / 1000, function () use ($callback, $command) {
            $stdout = $this->exec($command);
            if (!empty($stdout)) {
                call_user_func($callback);
            }
        });
    }

    public function exec($command)
    {
        $process = Process::fromShellCommandline($command);
        $process->run();
        if ($process->isSuccessful()) {
            return $process->getOutput();
        }
        return false;
    }

}

<?php

namespace think\dumper;

use Symfony\Component\VarDumper\Dumper\ContextProvider\ContextProviderInterface;
use Symfony\Component\VarDumper\VarDumper;

final class SourceContextProvider implements ContextProviderInterface
{

    public function __construct(
        private ?string $charset = null,
        private ?string $projectDir = null,
        private int     $limit = 9,
    )
    {
    }

    public function getContext(): ?array
    {
        $trace = debug_backtrace(\DEBUG_BACKTRACE_PROVIDE_OBJECT | \DEBUG_BACKTRACE_IGNORE_ARGS, $this->limit);
        $trace = array_reverse($trace);

        $i    = 0;
        $file = '-';
        $line = 0;

        do {
            $file = $trace[$i]['file'] ?? $file;
            $line = $trace[$i]['line'] ?? $line;

            $function = $trace[$i]['function'] ?? null;
            $class    = $trace[$i]['class'] ?? null;
            if ($function == 'dump' && ($class == null || $class == Dumper::class || $class == VarDumper::class)) {
                break;
            }
        } while (++$i < $this->limit);

        $name = '-' === $file || 'Standard input code' === $file ? 'Standard input code' : false;

        if (false === $name) {
            $name = str_replace('\\', '/', $file);
            $name = substr($name, strrpos($name, '/') + 1);
        }

        $context = ['name' => $name, 'file' => $file, 'line' => $line];

        if (null !== $this->projectDir) {
            $context['project_dir'] = $this->projectDir;
            if (str_starts_with($file, $this->projectDir)) {
                $context['file_relative'] = ltrim(substr($file, \strlen($this->projectDir)), \DIRECTORY_SEPARATOR);
            }
        }

        return $context;
    }

}

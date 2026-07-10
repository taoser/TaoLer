<?php

namespace think\dumper;

use Exception;
use Symfony\Component\VarDumper\Caster\ReflectionCaster;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\CliDumper;
use Symfony\Component\VarDumper\Dumper\ContextProvider\ContextProviderInterface;
use Symfony\Component\VarDumper\Dumper\ContextualizedDumper;
use Symfony\Component\VarDumper\Dumper\HtmlDumper;
use think\App;
use think\Env;
use think\Request;

class Dumper
{
    public function __construct(protected App $app, protected Env $env)
    {
    }

    public function dump($var, ?string $label = null)
    {
        $format = $this->app->runningInConsole() ? 'cli' : 'html';

        $handler = $this->createHandler($format);

        return $handler($var, $label);
    }

    private function createHandler($format)
    {
        $cloner = new VarCloner();
        $cloner->addCasters(ReflectionCaster::UNSET_CLOSURE_FILE_INFO);

        switch ($format) {
            case 'html' :
                $dumper = new HtmlDumper();
                break;
            case 'cli':
                $dumper = new CliDumper();
                break;
            default:
                throw new Exception('Invalid dump format.');
        }

        if ($this->env->has('DUMPER_TOKEN')) {
            $dumper = new ServerDumper($dumper, $this->getDefaultContextProviders($format));
        } else {
            $dumper = new ContextualizedDumper($dumper, [new SourceContextProvider()]);
        }

        return function ($var, ?string $label = null) use ($cloner, $dumper) {
            $var = $cloner->cloneVar($var);

            if (null !== $label) {
                $var = $var->withContext(['label' => $label]);
            }

            $dumper->dump($var);
        };
    }

    private function getDefaultContextProviders($format): array
    {
        $contextProviders = [];

        switch ($format) {
            case 'html' :
                $contextProviders['request'] = new class($this->app->request) implements ContextProviderInterface {
                    public function __construct(protected Request $request)
                    {
                    }

                    public function getContext(): ?array
                    {
                        $request = $this->request;
                        return [
                            'uri'        => $request->url(),
                            'method'     => $request->method(),
                            'identifier' => spl_object_hash($request),
                        ];
                    }
                };
                break;
            case 'cli':
                $contextProviders['cli'] = new class implements ContextProviderInterface {
                    public function getContext(): ?array
                    {
                        return [
                            'command_line' => $commandLine = implode(' ', $_SERVER['argv'] ?? []),
                            'identifier'   => hash('crc32b', $commandLine . $_SERVER['REQUEST_TIME_FLOAT']),
                        ];
                    }
                };
                break;
        }

        $contextProviders['source'] = new SourceContextProvider();

        return $contextProviders;
    }
}

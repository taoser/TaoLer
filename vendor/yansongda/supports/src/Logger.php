<?php

declare(strict_types=1);

namespace Yansongda\Supports;

use Exception;
use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger as BaseLogger;
use Psr\Log\LoggerInterface;

/**
 * @method static void emergency($message, array $context = [])
 * @method static void alert($message, array $context = [])
 * @method static void critical($message, array $context = [])
 * @method static void error($message, array $context = [])
 * @method static void warning($message, array $context = [])
 * @method static void notice($message, array $context = [])
 * @method static void info($message, array $context = [])
 * @method static void debug($message, array $context = [])
 * @method static void log($message, array $context = [])
 */
class Logger
{
    protected ?LoggerInterface $logger = null;

    protected ?FormatterInterface $formatter = null;

    protected ?AbstractProcessingHandler $handler = null;

    protected array $config = [
        'file' => null,
        'identify' => 'yansongda.supports',
        'level' => BaseLogger::DEBUG,
        'type' => 'daily',
        'max_files' => 30,
    ];

    /**
     * Bootstrap.
     */
    public function __construct(array $config = [])
    {
        $this->setConfig($config);
    }

    /**
     * Forward call.
     *
     * @throws Exception
     */
    public function __call(string $method, array $args): void
    {
        call_user_func_array([$this->getLogger(), $method], $args);
    }

    /**
     * Set logger.
     */
    public function setLogger(LoggerInterface $logger): Logger
    {
        $this->logger = $logger;

        return $this;
    }

    /**
     * Return the logger instance.
     *
     * @throws Exception
     */
    public function getLogger(): LoggerInterface
    {
        return $this->logger ??= $this->createLogger();
    }

    public function createLogger(): BaseLogger
    {
        $handler = $this->getHandler();

        $handler->setFormatter($this->getFormatter());

        $logger = new BaseLogger($this->config['identify']);

        $logger->pushHandler($handler);

        return $logger;
    }

    /**
     * setFormatter.
     *
     * @return $this
     */
    public function setFormatter(FormatterInterface $formatter): self
    {
        $this->formatter = $formatter;

        return $this;
    }

    /**
     * getFormatter.
     */
    public function getFormatter(): FormatterInterface
    {
        return $this->formatter ??= $this->createFormatter();
    }

    /**
     * createFormatter.
     */
    public function createFormatter(): LineFormatter
    {
        return new LineFormatter(
            "%datetime% > %channel%.%level_name% > %message% %context% %extra%\n\n",
            null,
            false,
            true
        );
    }

    /**
     * setHandler.
     *
     * @return $this
     */
    public function setHandler(AbstractProcessingHandler $handler): self
    {
        $this->handler = $handler;

        return $this;
    }

    public function getHandler(): AbstractProcessingHandler
    {
        return $this->handler ??= $this->createHandler();
    }

    public function createHandler(): AbstractProcessingHandler
    {
        $file = $this->config['file'] ?? sys_get_temp_dir().'/logs/'.$this->config['identify'].'.log';

        if ('single' === $this->config['type']) {
            return new StreamHandler($file, $this->config['level']);
        }

        return new RotatingFileHandler($file, $this->config['max_files'], $this->config['level']);
    }

    /**
     * setConfig.
     *
     * @return $this
     */
    public function setConfig(array $config): self
    {
        $this->config = array_merge($this->config, $config);

        return $this;
    }

    /**
     * getConfig.
     */
    public function getConfig(): array
    {
        return $this->config;
    }
}

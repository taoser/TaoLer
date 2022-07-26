<h1 align="center">Supports</h1>

[![Linter Status](https://github.com/yansongda/supports/workflows/Linter/badge.svg)](https://github.com/yansongda/supports/actions) 
[![Tester Status](https://github.com/yansongda/supports/workflows/Tester/badge.svg)](https://github.com/yansongda/supports/actions) 
[![Latest Stable Version](https://poser.pugx.org/yansongda/supports/v/stable)](https://packagist.org/packages/yansongda/supports)
[![Total Downloads](https://poser.pugx.org/yansongda/supports/downloads)](https://packagist.org/packages/yansongda/supports)
[![Latest Unstable Version](https://poser.pugx.org/yansongda/supports/v/unstable)](https://packagist.org/packages/yansongda/supports)
[![License](https://poser.pugx.org/yansongda/supports/license)](https://packagist.org/packages/yansongda/supports)


handle with array/config/log/guzzle etc.

## About log

```PHP
use Yansongda\Supports\Logger as Log;
use Monolog\Logger;

class ApplicationLogger
{
    private static $logger;

    /**
     * Forward call.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @return mixed
     */
    public static function __callStatic(string $method, array $args)
    {
        return call_user_func_array([self::getLogger(), $method], $args);
    }

    /**
     * Forward call.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @return mixed
     */
    public function __call(string $method, array $args)
    {
        return call_user_func_array([self::getLogger(), $method], $args);
    }

    /**
     * Make a default log instance.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @return Log
     */
    public static function getLogger()
    {
        if (! self::$logger instanceof Logger) {
            self::$logger = new Log();
        }   

        return self::$logger;
    }
}
```

### Usage

After registerLog, you can use Log service:

```PHP

ApplicationLogger::debug('test', ['test log']);
```

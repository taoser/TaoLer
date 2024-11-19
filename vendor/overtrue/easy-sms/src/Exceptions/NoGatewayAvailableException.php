<?php

/*
 * This file is part of the overtrue/easy-sms.
 *
 * (c) overtrue <i@overtrue.me>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Overtrue\EasySms\Exceptions;

<<<<<<< HEAD
use Throwable;

=======
>>>>>>> 3.0
/**
 * Class NoGatewayAvailableException.
 *
 * @author overtrue <i@overtrue.me>
 */
class NoGatewayAvailableException extends Exception
{
    /**
     * @var array
     */
    public $results = [];

    /**
     * @var array
     */
    public $exceptions = [];

    /**
     * NoGatewayAvailableException constructor.
     *
<<<<<<< HEAD
     * @param array           $results
     * @param int             $code
     * @param \Throwable|null $previous
     */
    public function __construct(array $results = [], $code = 0, Throwable $previous = null)
=======
     * @param int $code
     */
    public function __construct(array $results = [], $code = 0, ?\Throwable $previous = null)
>>>>>>> 3.0
    {
        $this->results = $results;
        $this->exceptions = \array_column($results, 'exception', 'gateway');

        parent::__construct('All the gateways have failed. You can get error details by `$exception->getExceptions()`', $code, $previous);
    }

    /**
     * @return array
     */
    public function getResults()
    {
        return $this->results;
    }

    /**
     * @param string $gateway
     *
     * @return mixed|null
     */
    public function getException($gateway)
    {
        return isset($this->exceptions[$gateway]) ? $this->exceptions[$gateway] : null;
    }

    /**
     * @return array
     */
    public function getExceptions()
    {
        return $this->exceptions;
    }

<<<<<<< HEAD
    /**
     * @return mixed
     */
=======
>>>>>>> 3.0
    public function getLastException()
    {
        return end($this->exceptions);
    }
}

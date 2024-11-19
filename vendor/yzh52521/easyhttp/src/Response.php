<?php

namespace yzh52521\EasyHttp;

use ArrayAccess;
use LogicException;

class Response implements ArrayAccess
{
    protected $response;

    /**
     * The decoded JSON response.
     *
     * @var array
     */
    protected $decoded;

    public function __construct($response)
    {
        $this->response = $response;
    }

    /**
     * Get the body of the response.
     * @return string
     */
    public function body()
    {
        return (string)$this->response->getBody();
    }

    /**
     * Get the Array decoded body of the response.
     * @return array|mixed
     */
    public function array()
    {
        if (!$this->decoded) {
            $this->decoded = json_decode( (string)$this->response->getBody(),true );
        }

        return $this->decoded;
    }

    /**
     * Get the JSON decoded body of the response.
     * @return object|mixed
     */
    public function json()
    {
        if (!$this->decoded) {
            $this->decoded = json_decode( (string)$this->response->getBody() );
        }

        return $this->decoded;
    }

    /**
     * Get a header from the response.
     * @param string $header
     * @return mixed
     */
    public function header(string $header)
    {
        return $this->response->getHeaderLine( $header );
    }

    /**
     * Get the headers from the response.
     * @return mixed
     */
    public function headers()
    {
        return $this->mapWithKeys( $this->response->getHeaders(),function ($v,$k) {
            return [$k => $v];
        } )->response;
    }

    /**
     * Get the status code of the response.
     * @return int
     */
    public function status()
    {
        return (int)$this->response->getStatusCode();
    }

    /**
     * Determine if the request was successful.
     * @return bool
     */
    public function successful()
    {
        return $this->status() >= 200 && $this->status() < 300;
    }

    /**
     * Determine if the response code was "OK".
     * @return bool
     */
    public function ok()
    {
        return $this->status() === 200;
    }

    /**
     * Determine if the response was a redirect.
     * @return bool
     */
    public function redirect()
    {
        return $this->status() >= 300 && $this->status() < 400;
    }

    /**
     * Determine if the response indicates a client error occurred.
     * @return bool
     */
    public function clientError()
    {
        return $this->status() >= 400 && $this->status() < 500;
    }

    /**
     * Determine if the response indicates a server error occurred.
     * @return bool
     */
    public function serverError()
    {
        return $this->status() >= 500;
    }

    /**
     * Determine if the given offset exists.
     *
     * @param string $offset
     * @return mixed
     */
    #[\ReturnTypeWillChange]
    public function offsetExists($offset)
    {
        return array_key_exists( $offset,$this->json() );
    }

    /**
     * Get the value for a given offset.
     *
     * @param string $offset
     * @return mixed
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->json()[$offset];
    }

    /**
     * Set the value at the given offset.
     *
     * @param string $offset
     * @param mixed $value
     * @return void
     *
     * @throws \LogicException
     */
    #[\ReturnTypeWillChange]
    public function offsetSet($offset,$value)
    {
        throw new LogicException( 'Response data may not be mutated using array access.' );
    }

    /**
     * Unset the value at the given offset.
     *
     * @param string $offset
     * @return void
     *
     * @throws \LogicException
     */
    #[\ReturnTypeWillChange]
    public function offsetUnset($offset)
    {
        throw new LogicException( 'Response data may not be mutated using array access.' );
    }

    /**
     * Get the body of the response.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->body();
    }

    protected function mapWithKeys($items,callable $callback)
    {
        $result = [];

        foreach ( $items as $key => $value ) {
            $assoc = $callback( $value,$key );

            foreach ( $assoc as $mapKey => $mapValue ) {
                $result[$mapKey] = $mapValue;
            }
        }

        return new static( $result );
    }

}

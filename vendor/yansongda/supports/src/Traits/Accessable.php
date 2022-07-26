<?php

declare(strict_types=1);

namespace Yansongda\Supports\Traits;

use Yansongda\Supports\Str;

trait Accessable
{
    /**
     * __get.
     *
     * @return mixed
     */
    public function __get(string $key)
    {
        return $this->get($key);
    }

    /**
     * Whether or not an data exists by key.
     */
    public function __isset(string $key): bool
    {
        return !is_null($this->get($key));
    }

    /**
     * Unsets an data by key.
     */
    public function __unset(string $key)
    {
        $this->offsetUnset($key);
    }

    /**
     * __set.
     *
     * @param mixed $value
     */
    public function __set(string $key, $value): void
    {
        $this->set($key, $value);
    }

    /**
     * get.
     *
     * @param mixed $default
     *
     * @return mixed
     */
    public function get(?string $key = null, $default = null)
    {
        if (is_null($key)) {
            return method_exists($this, 'toArray') ? $this->toArray() : $default;
        }

        $method = 'get'.Str::studly($key);

        if (method_exists($this, $method)) {
            return $this->{$method}();
        }

        return $default;
    }

    /**
     * set.
     *
     * @param mixed $value
     */
    public function set(string $key, $value): self
    {
        $method = 'set'.Str::studly($key);

        if (method_exists($this, $method)) {
            $this->{$method}($value);
        }

        return $this;
    }

    /**
     * Whether a offset exists.
     *
     * @see https://php.net/manual/en/arrayaccess.offsetexists.php
     *
     * @param mixed $offset an offset to check for
     *
     * @return bool true on success or false on failure.
     *
     * The return value will be casted to boolean if non-boolean was returned.
     */
    #[\ReturnTypeWillChange]
    public function offsetExists($offset)
    {
        return !is_null($this->get($offset));
    }

    /**
     * Offset to retrieve.
     *
     * @see https://php.net/manual/en/arrayaccess.offsetget.php
     *
     * @param mixed $offset the offset to retrieve
     *
     * @return mixed can return all value types
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * Offset to set.
     *
     * @see https://php.net/manual/en/arrayaccess.offsetset.php
     *
     * @param mixed $offset the offset to assign the value to
     * @param mixed $value  the value to set
     *
     * @return void
     */
    #[\ReturnTypeWillChange]
    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }

    /**
     * Offset to unset.
     *
     * @see https://php.net/manual/en/arrayaccess.offsetunset.php
     *
     * @param mixed $offset the offset to unset
     *
     * @return void
     */
    #[\ReturnTypeWillChange]
    public function offsetUnset($offset)
    {
    }
}

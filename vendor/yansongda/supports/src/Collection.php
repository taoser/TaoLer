<?php

declare(strict_types=1);

namespace Yansongda\Supports;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use JsonSerializable;
use Yansongda\Supports\Traits\Accessable;
use Yansongda\Supports\Traits\Arrayable;
use Yansongda\Supports\Traits\Serializable;

class Collection implements ArrayAccess, Countable, IteratorAggregate, JsonSerializable
{
    use Serializable;
    use Accessable;
    use Arrayable;

    /**
     * The collection data.
     */
    protected array $items = [];

    /**
     * set data.
     *
     * @param mixed $items
     */
    public function __construct($items = [])
    {
        foreach ($this->getArrayableItems($items) as $key => $value) {
            $this->set($key, $value);
        }
    }

    /**
     * Wrap the given value in a collection if applicable.
     *
     * @param mixed $value
     */
    public static function wrap($value): self
    {
        return $value instanceof self ? new static($value) : new static(Arr::wrap($value));
    }

    /**
     * Get the underlying items from the given collection if applicable.
     *
     * @param array|static $value
     */
    public static function unwrap($value): array
    {
        return $value instanceof self ? $value->all() : $value;
    }

    /**
     * Return all items.
     */
    public function all(): array
    {
        return $this->items;
    }

    /**
     * Return specific items.
     */
    public function only(array $keys): array
    {
        $return = [];

        foreach ($keys as $key) {
            $value = $this->get($key);

            if (!is_null($value)) {
                $return[$key] = $value;
            }
        }

        return $return;
    }

    /**
     * Get all items except for those with the specified keys.
     *
     * @param mixed $keys
     */
    public function except($keys): self
    {
        $keys = is_array($keys) ? $keys : func_get_args();

        return new static(Arr::except($this->items, $keys));
    }

    /**
     * Run a filter over each of the items.
     */
    public function filter(callable $callback = null): self
    {
        if ($callback) {
            return new static(Arr::where($this->items, $callback));
        }

        return new static(array_filter($this->items));
    }

    /**
     * Merge the collection with the given items.
     *
     * @param mixed $items
     */
    public function merge($items): self
    {
        return new static(array_merge($this->items, $this->getArrayableItems($items)));
    }

    /**
     * To determine Whether the specified element exists.
     *
     * @param string|int $key
     */
    public function has($key): bool
    {
        return !is_null(Arr::get($this->items, $key));
    }

    /**
     * Retrieve the first item.
     *
     * @return mixed
     */
    public function first()
    {
        return reset($this->items);
    }

    /**
     * Retrieve the last item.
     *
     * @return mixed
     */
    public function last()
    {
        $end = end($this->items);

        reset($this->items);

        return $end;
    }

    /**
     * add the item value.
     *
     * @param string|int|null $key
     * @param mixed           $value
     */
    public function add($key, $value): void
    {
        Arr::set($this->items, $key, $value);
    }

    /**
     * Set the item value.
     *
     * @param string|int|null $key
     * @param mixed           $value
     */
    public function set($key, $value): void
    {
        Arr::set($this->items, $key, $value);
    }

    /**
     * Retrieve item from Collection.
     *
     * @param string|int|null $key
     * @param mixed           $default
     *
     * @return mixed
     */
    public function get($key = null, $default = null)
    {
        return Arr::get($this->items, $key, $default);
    }

    /**
     * Remove item form Collection.
     *
     * @param string|int $key
     */
    public function forget($key): void
    {
        Arr::forget($this->items, $key);
    }

    /**
     * Get a flattened array of the items in the collection.
     *
     * @param float|int $depth
     */
    public function flatten($depth = INF): self
    {
        return new static(Arr::flatten($this->items, $depth));
    }

    /**
     * Run a map over each of the items.
     */
    public function map(callable $callback): self
    {
        $keys = array_keys($this->items);
        $items = array_map($callback, $this->items, $keys);

        return new static(array_combine($keys, $items));
    }

    /**
     * Get and remove the last item from the collection.
     *
     * @return mixed
     */
    public function pop()
    {
        return array_pop($this->items);
    }

    /**
     * Push an item onto the beginning of the collection.
     *
     * @param mixed|null $key
     * @param mixed      $value
     */
    public function prepend($value, $key = null): self
    {
        $this->items = Arr::prepend($this->items, $value, $key);

        return $this;
    }

    /**
     * Push an item onto the end of the collection.
     *
     * @param mixed $value
     */
    public function push($value): self
    {
        $this->offsetSet(null, $value);

        return $this;
    }

    /**
     * Get and remove an item from the collection.
     *
     * @param mixed $default
     * @param mixed $key
     *
     * @return mixed
     */
    public function pull($key, $default = null)
    {
        return Arr::pull($this->items, $key, $default);
    }

    /**
     * Put an item in the collection by key.
     *
     * @param mixed $key
     * @param mixed $value
     */
    public function put($key, $value): self
    {
        $this->offsetSet($key, $value);

        return $this;
    }

    /**
     * Get one or a specified number of items randomly from the collection.
     *
     * @throws \InvalidArgumentException
     */
    public function random(?int $number = null): self
    {
        return new static(Arr::random($this->items, $number ?? 1));
    }

    /**
     * Reduce the collection to a single value.
     *
     * @param mixed|null $initial
     */
    public function reduce(callable $callback, $initial = null)
    {
        return array_reduce($this->items, $callback, $initial);
    }

    /**
     * Reset the keys on the underlying array.
     */
    public function values(): self
    {
        return new static(array_values($this->items));
    }

    /**
     * Determine if all items in the collection pass the given test.
     *
     * @param callable|string $key
     */
    public function every($key): bool
    {
        $callback = $this->valueRetriever($key);

        foreach ($this->items as $k => $v) {
            if (!$callback($v, $k)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Chunk the underlying collection array.
     */
    public function chunk(int $size): self
    {
        if ($size <= 0) {
            return new static();
        }
        $chunks = [];
        foreach (array_chunk($this->items, $size, true) as $chunk) {
            $chunks[] = new static($chunk);
        }

        return new static($chunks);
    }

    /**
     * Sort through each item with a callback.
     */
    public function sort(callable $callback = null): self
    {
        $items = $this->items;
        $callback ? uasort($items, $callback) : asort($items);

        return new static($items);
    }

    /**
     * Sort the collection using the given callback.
     *
     * @param callable|string $callback
     */
    public function sortBy($callback, int $options = SORT_REGULAR, bool $descending = false): self
    {
        $results = [];
        $callback = $this->valueRetriever($callback);
        // First we will loop through the items and get the comparator from a callback
        // function which we were given. Then, we will sort the returned values and
        // and grab the corresponding values for the sorted keys from this array.
        foreach ($this->items as $key => $value) {
            $results[$key] = $callback($value, $key);
        }
        $descending ? arsort($results, $options) : asort($results, $options);
        // Once we have sorted all of the keys in the array, we will loop through them
        // and grab the corresponding model so we can set the underlying items list
        // to the sorted version. Then we'll just return the collection instance.
        foreach (array_keys($results) as $key) {
            $results[$key] = $this->items[$key];
        }

        return new static($results);
    }

    /**
     * Sort the collection in descending order using the given callback.
     *
     * @param callable|string $callback
     */
    public function sortByDesc($callback, int $options = SORT_REGULAR): self
    {
        return $this->sortBy($callback, $options, true);
    }

    /**
     * Sort the collection keys.
     */
    public function sortKeys(int $options = SORT_REGULAR, bool $descending = false): self
    {
        $items = $this->items;
        $descending ? krsort($items, $options) : ksort($items, $options);

        return new static($items);
    }

    /**
     * Sort the collection keys in descending order.
     */
    public function sortKeysDesc(int $options = SORT_REGULAR): self
    {
        return $this->sortKeys($options, true);
    }

    public function query(int $encodingType = PHP_QUERY_RFC1738): string
    {
        return Arr::query($this->all(), $encodingType);
    }

    public function toString(string $separator = '&'): string
    {
        return Arr::toString($this->all(), $separator);
    }

    /**
     * Build to array.
     */
    public function toArray(): array
    {
        return $this->all();
    }

    /**
     * Build to json.
     */
    public function toJson(int $option = JSON_UNESCAPED_UNICODE): string
    {
        return json_encode($this->all(), $option);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Retrieve an external iterator.
     *
     * @see http://php.net/manual/en/iteratoraggregate.getiterator.php
     *
     * @return ArrayIterator An instance of an object implementing <b>Iterator</b> or
     *                       <b>ArrayIterator</b>
     */
    #[\ReturnTypeWillChange]
    public function getIterator()
    {
        return new ArrayIterator($this->items);
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Count elements of an object.
     *
     * @see http://php.net/manual/en/countable.count.php
     *
     * @return int The custom count as an integer.
     *             </p>
     *             <p>
     *             The return value is cast to an integer
     */
    #[\ReturnTypeWillChange]
    public function count()
    {
        return count($this->items);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to unset.
     *
     * @see http://php.net/manual/en/arrayaccess.offsetunset.php
     *
     * @param mixed $offset <p>
     *                      The offset to unset.
     *                      </p>
     */
    #[\ReturnTypeWillChange]
    public function offsetUnset($offset)
    {
        if ($this->offsetExists($offset)) {
            $this->forget($offset);
        }
    }

    /**
     * Determine if the given value is callable, but not a string.
     *
     * @param mixed $value
     */
    protected function useAsCallable($value): bool
    {
        return !is_string($value) && is_callable($value);
    }

    /**
     * Get a value retrieving callback.
     *
     * @param mixed $value
     */
    protected function valueRetriever($value): callable
    {
        if ($this->useAsCallable($value)) {
            return $value;
        }

        return function ($item) use ($value) {
            return data_get($item, $value);
        };
    }

    /**
     * Results array of items from Collection or Arrayable.
     *
     * @param mixed $items
     */
    protected function getArrayableItems($items): array
    {
        if (is_array($items)) {
            return $items;
        }

        if ($items instanceof self) {
            return $items->all();
        }

        if ($items instanceof JsonSerializable) {
            return $items->jsonSerialize();
        }

        return (array) $items;
    }
}

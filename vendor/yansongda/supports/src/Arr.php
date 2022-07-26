<?php

declare(strict_types=1);

namespace Yansongda\Supports;

use ArrayAccess;
use InvalidArgumentException;

/**
 * Most of the methods in this file come from illuminate/support and hyperf/support,
 * thanks provide such a useful class.
 */
class Arr
{
    /**
     * Determine whether the given value is array accessible.
     *
     * @param mixed $value
     */
    public static function accessible($value): bool
    {
        return is_array($value) || $value instanceof ArrayAccess;
    }

    /**
     * Add an element to an array using "dot" notation if it doesn't exist.
     *
     * @param mixed $value
     */
    public static function add(array $array, string $key, $value): array
    {
        if (is_null(static::get($array, $key))) {
            static::set($array, $key, $value);
        }

        return $array;
    }

    /**
     * Collapse an array of arrays into a single array.
     */
    public static function collapse(array $array): array
    {
        $results = [];
        foreach ($array as $values) {
            if ($values instanceof Collection) {
                $values = $values->all();
            } elseif (!is_array($values)) {
                continue;
            }
            $results[] = $values;
        }

        return array_merge([], ...$results);
    }

    /**
     * Cross join the given arrays, returning all possible permutations.
     *
     * @param array ...$arrays
     */
    public static function crossJoin(...$arrays): array
    {
        $results = [[]];
        foreach ($arrays as $index => $array) {
            $append = [];
            foreach ($results as $product) {
                foreach ($array as $item) {
                    $product[$index] = $item;
                    $append[] = $product;
                }
            }
            $results = $append;
        }

        return $results;
    }

    /**
     * Divide an array into two arrays. One with keys and the other with values.
     */
    public static function divide(array $array): array
    {
        return [array_keys($array), array_values($array)];
    }

    /**
     * Flatten a multi-dimensional associative array with dots.
     */
    public static function dot(array $array, string $prepend = ''): array
    {
        $results = [];
        foreach ($array as $key => $value) {
            if (is_array($value) && !empty($value)) {
                $results = array_merge($results, static::dot($value, $prepend.$key.'.'));
            } else {
                $results[$prepend.$key] = $value;
            }
        }

        return $results;
    }

    /**
     * Get all of the given array except for a specified array of keys.
     *
     * @param array|string $keys
     */
    public static function except(array $array, $keys): array
    {
        static::forget($array, $keys);

        return $array;
    }

    /**
     * Determine if the given key exists in the provided array.
     *
     * @param array|\ArrayAccess $array
     * @param int|string         $key
     */
    public static function exists($array, $key): bool
    {
        if ($array instanceof ArrayAccess) {
            return $array->offsetExists($key);
        }

        return array_key_exists($key, $array);
    }

    /**
     * Return the first element in an array passing a given truth test.
     *
     * @param mixed|null $default
     */
    public static function first(array $array, callable $callback = null, $default = null)
    {
        if (is_null($callback)) {
            if (empty($array)) {
                return $default;
            }
            foreach ($array as $item) {
                return $item;
            }
        }
        foreach ($array as $key => $value) {
            if (call_user_func($callback, $value, $key)) {
                return $value;
            }
        }

        return $default;
    }

    /**
     * Return the last element in an array passing a given truth test.
     *
     * @param mixed|null $default
     */
    public static function last(array $array, callable $callback = null, $default = null)
    {
        if (is_null($callback)) {
            return empty($array) ? $default : end($array);
        }

        return static::first(array_reverse($array, true), $callback, $default);
    }

    /**
     * Flatten a multi-dimensional array into a single level.
     *
     * @param float|int $depth
     */
    public static function flatten(array $array, $depth = INF): array
    {
        $result = [];
        foreach ($array as $item) {
            $item = $item instanceof Collection ? $item->all() : $item;
            if (!is_array($item)) {
                $result[] = $item;
            } elseif (1 === $depth) {
                $result = array_merge($result, array_values($item));
            } else {
                $result = array_merge($result, static::flatten($item, $depth - 1));
            }
        }

        return $result;
    }

    /**
     * Remove one or many array items from a given array using "dot" notation.
     *
     * @param array|string $keys
     */
    public static function forget(array &$array, $keys): void
    {
        $original = &$array;
        $keys = (array) $keys;
        if (0 === count($keys)) {
            return;
        }
        foreach ($keys as $key) {
            // if the exact key exists in the top-level, remove it
            if (static::exists($array, $key)) {
                unset($array[$key]);
                continue;
            }
            $parts = explode('.', $key);
            // clean up before each pass
            $array = &$original;
            while (count($parts) > 1) {
                $part = array_shift($parts);
                if (isset($array[$part]) && is_array($array[$part])) {
                    $array = &$array[$part];
                } else {
                    continue 2;
                }
            }
            unset($array[array_shift($parts)]);
        }
    }

    /**
     * Get an item from an array using "dot" notation.
     *
     * @param array|\ArrayAccess $array
     * @param int|string|null    $key
     * @param mixed              $default
     */
    public static function get($array, $key = null, $default = null)
    {
        if (!static::accessible($array)) {
            return $default;
        }
        if (is_null($key)) {
            return $array;
        }
        if (static::exists($array, $key)) {
            return $array[$key];
        }
        if (!is_string($key) || false === strpos($key, '.')) {
            return $array[$key] ?? $default;
        }
        foreach (explode('.', $key) as $segment) {
            if (static::accessible($array) && static::exists($array, $segment)) {
                $array = $array[$segment];
            } else {
                return $default;
            }
        }

        return $array;
    }

    /**
     * Check if an item or items exist in an array using "dot" notation.
     *
     * @param array|\ArrayAccess $array
     * @param array|string|null  $keys
     */
    public static function has($array, $keys): bool
    {
        if (is_null($keys)) {
            return false;
        }
        $keys = (array) $keys;
        if (!$array) {
            return false;
        }
        if ([] === $keys) {
            return false;
        }
        foreach ($keys as $key) {
            $subKeyArray = $array;
            if (static::exists($array, $key)) {
                continue;
            }
            foreach (explode('.', $key) as $segment) {
                if (static::accessible($subKeyArray) && static::exists($subKeyArray, $segment)) {
                    $subKeyArray = $subKeyArray[$segment];
                } else {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Determines if an array is associative.
     * An array is "associative" if it doesn't have sequential numerical keys beginning with zero.
     */
    public static function isAssoc(array $array): bool
    {
        $keys = array_keys($array);

        return array_keys($keys) !== $keys;
    }

    /**
     * Get a subset of the items from the given array.
     *
     * @param array|string $keys
     */
    public static function only(array $array, $keys): array
    {
        return array_intersect_key($array, array_flip((array) $keys));
    }

    /**
     * Pluck an array of values from an array.
     *
     * @param array|string      $value
     * @param array|string|null $key
     */
    public static function pluck(array $array, $value, $key = null): array
    {
        $results = [];

        foreach ($array as $item) {
            $itemValue = data_get($item, $value);
            // If the key is "null", we will just append the value to the array and keep
            // looping. Otherwise we will key the array using the value of the key we
            // received from the developer. Then we'll return the final array form.
            if (is_null($key)) {
                $results[] = $itemValue;
            } else {
                $itemKey = data_get($item, $key);
                if (is_object($itemKey) && method_exists($itemKey, '__toString')) {
                    $itemKey = (string) $itemKey;
                }
                $results[$itemKey] = $itemValue;
            }
        }

        return $results;
    }

    /**
     * Push an item onto the beginning of an array.
     *
     * @param mixed|null $key
     * @param mixed      $value
     */
    public static function prepend(array $array, $value, $key = null): array
    {
        if (is_null($key)) {
            array_unshift($array, $value);
        } else {
            $array = [$key => $value] + $array;
        }

        return $array;
    }

    /**
     * Get a value from the array, and remove it.
     *
     * @param mixed|null $default
     */
    public static function pull(array &$array, string $key, $default = null)
    {
        $value = static::get($array, $key, $default);
        static::forget($array, $key);

        return $value;
    }

    /**
     * Get one or a specified number of random values from an array.
     *
     * @throws \InvalidArgumentException
     */
    public static function random(array $array, int $number = 1): array
    {
        $count = count($array);
        if ($number > $count) {
            throw new InvalidArgumentException("You requested $number items, but there are only $count items available.");
        }

        if (0 === $number) {
            return [];
        }

        $keys = array_rand($array, $number);
        $results = [];
        foreach ((array) $keys as $key) {
            $results[] = $array[$key];
        }

        return $results;
    }

    /**
     * Set an array item to a given value using "dot" notation.
     * If no key is given to the method, the entire array will be replaced.
     *
     * @param int|string|null $key
     * @param mixed           $value
     */
    public static function set(array &$array, $key, $value): array
    {
        if (is_null($key)) {
            return $array = $value;
        }
        if (!is_string($key)) {
            $array[$key] = $value;

            return $array;
        }
        $keys = explode('.', $key);
        while (count($keys) > 1) {
            $key = array_shift($keys);
            // If the key doesn't exist at this depth, we will just create an empty array
            // to hold the next value, allowing us to create the arrays to hold final
            // values at the correct depth. Then we'll keep digging into the array.
            if (!isset($array[$key]) || !is_array($array[$key])) {
                $array[$key] = [];
            }
            $array = &$array[$key];
        }
        $array[array_shift($keys)] = $value;

        return $array;
    }

    /**
     * Shuffle the given array and return the result.
     */
    public static function shuffle(array $array, int $seed = null): array
    {
        if (is_null($seed)) {
            shuffle($array);
        } else {
            srand($seed);
            usort($array, function () {
                return rand(-1, 1);
            });
        }

        return $array;
    }

    /**
     * Sort the array using the given Closure.
     */
    public static function sort(array $array, callable $callback): array
    {
        $results = [];

        foreach ($array as $key => $value) {
            $results[$key] = $callback($value);
        }

        return $results;
    }

    /**
     * Recursively sort an array by keys and values.
     */
    public static function sortRecursive(array $array): array
    {
        foreach ($array as &$value) {
            if (is_array($value)) {
                $value = static::sortRecursive($value);
            }
        }
        if (static::isAssoc($array)) {
            ksort($array);
        } else {
            sort($array);
        }

        return $array;
    }

    public static function query(array $array, int $encodingType = PHP_QUERY_RFC1738): string
    {
        return http_build_query($array, '', '&', $encodingType);
    }

    public static function toString(array $array, string $separator = '&'): string
    {
        $result = '';

        foreach ($array as $key => $value) {
            $result .= $key.'='.$value.$separator;
        }

        return substr($result, 0, 0 - Str::length($separator));
    }

    /**
     * Filter the array using the given callback.
     */
    public static function where(array $array, callable $callback): array
    {
        return array_filter($array, $callback, ARRAY_FILTER_USE_BOTH);
    }

    /**
     * If the given value is not an array and not null, wrap it in one.
     *
     * @param mixed $value
     */
    public static function wrap($value): array
    {
        if (is_null($value)) {
            return [];
        }

        return !is_array($value) ? [$value] : $value;
    }

    /**
     * Make array elements unique.
     */
    public static function unique(array $array): array
    {
        $result = [];
        foreach ($array as $key => $item) {
            if (is_array($item)) {
                $result[$key] = self::unique($item);
            } else {
                $result[$key] = $item;
            }
        }

        if (!self::isAssoc($result)) {
            return array_unique($result);
        }

        return $result;
    }

    public static function merge(array $array1, array $array2, bool $unique = true): array
    {
        $isAssoc = static::isAssoc($array1 ?: $array2);
        if ($isAssoc) {
            foreach ($array2 as $key => $value) {
                if (is_array($value)) {
                    $array1[$key] = static::merge($array1[$key] ?? [], $value, $unique);
                } else {
                    $array1[$key] = $value;
                }
            }
        } else {
            foreach ($array2 as $value) {
                if ($unique && in_array($value, $array1, true)) {
                    continue;
                }
                $array1[] = $value;
            }

            $array1 = array_values($array1);
        }

        return $array1;
    }

    /**
     * Convert encoding.
     */
    public static function encoding(array $array, string $to_encoding, string $from_encoding = 'gb2312'): array
    {
        $encoded = [];

        foreach ($array as $key => $value) {
            $encoded[$key] = is_array($value) ? self::encoding($value, $to_encoding, $from_encoding) :
                                                mb_convert_encoding($value, $to_encoding, $from_encoding);
        }

        return $encoded;
    }

    /**
     * camelCaseKey.
     *
     * @param mixed $data
     *
     * @return mixed
     */
    public static function camelCaseKey($data)
    {
        if (!self::accessible($data) &&
            !(is_object($data) && method_exists($data, 'toArray'))) {
            return $data;
        }

        $result = [];

        foreach ((is_object($data) ? $data->toArray() : $data) as $key => $value) {
            $result[is_string($key) ? Str::camel($key) : $key] = self::camelCaseKey($value);
        }

        return $result;
    }

    /**
     * snakeCaseKey.
     *
     * @param mixed $data
     *
     * @return mixed
     */
    public static function snakeCaseKey($data)
    {
        if (!self::accessible($data) &&
            !(is_object($data) && method_exists($data, 'toArray'))) {
            return $data;
        }

        $result = [];

        foreach ((is_object($data) ? $data->toArray() : $data) as $key => $value) {
            $result[is_string($key) ? Str::snake($key) : $key] = self::snakeCaseKey($value);
        }

        return $result;
    }
}

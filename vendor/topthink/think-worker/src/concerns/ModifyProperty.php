<?php

namespace think\worker\concerns;

use ReflectionObject;

trait ModifyProperty
{
    protected function modifyProperty($object, $value, $property = 'app')
    {
        $reflectObject = new ReflectionObject($object);
        if ($reflectObject->hasProperty($property)) {
            $reflectProperty = $reflectObject->getProperty($property);

            // PHP 8.1+ deprecated setAccessible() as it has no effect, and PHP 8.5 raises
            // a deprecation notice. We can still set the property value via reflection without
            // forcing accessibility on modern PHP versions.
            if (PHP_VERSION_ID < 80100 && method_exists($reflectProperty, 'setAccessible')) {
                $reflectProperty->setAccessible(true);
            }

            $reflectProperty->setValue($object, $value);
        }
    }
}

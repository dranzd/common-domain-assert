<?php

namespace Dranzd\Common\EventSourcing\Util;

final class Idx
{
    /**
     * Convenience method for retrieving value of an object or array using a key
     * or array of keys
     *
     * A default value is provided to return in case property is not found or the
     * value of the property is empty if the condition use default on empty is set.
     *
     * @param  mixed   $object            Lookup data
     * @param  mixed   $key               String, numeric or array
     * @param  mixed   $default           Default value to return if index is not found
     * @param  boolean $useDefaultOnEmpty Use default value if value of index is empty
     * @return mixed
     */

    final public static function get($object, $key, $default = null, $useDefaultOnEmpty = false)
    {
        if (is_array($key)) {
            $idx = $object;
            $endKey = array_pop($key);

            foreach ($key as $k) {
                $idx = static::get($idx, $k);
            }

            return static::get($idx, $endKey, $default, $useDefaultOnEmpty);
        }

        if (is_object($object) && (property_exists($object, $key) || isset($object->$key))) {
            if ($object instanceof \ArrayObject) {
                if (!$useDefaultOnEmpty || !empty($object->$key)) {
                    return $object->$key;
                }
            } else {
                $reflection = new \ReflectionProperty($object, $key);
                if ($reflection->isPrivate() || $reflection->isProtected()) {
                    $getter = 'get' . ucfirst($key);
                    if (method_exists($object, $getter)) {
                        $result = call_user_func([$object, $getter]);
                        if (!$useDefaultOnEmpty || !empty($result)) {
                            return $result;
                        }
                    }
                    $getter = $key;
                    if (method_exists($object, $getter)) {
                        $result = call_user_func([$object, $getter]);
                        if (!$useDefaultOnEmpty || !empty($result)) {
                            return $result;
                        }
                    }
                    $reflection->setAccessible(true);
                    $result = $reflection->getValue($object);
                    $reflection->setAccessible(false);
                    if (!$useDefaultOnEmpty || !empty($result)) {
                        return $result;
                    }
                } elseif (!$useDefaultOnEmpty || !empty($object->$key)) {
                    return $object->$key;
                }
            }
        } elseif (is_array($object) && (is_string($key) || is_numeric($key)) && array_key_exists($key, $object)) {
            if (!$useDefaultOnEmpty || !empty($object[$key])) {
                return $object[$key];
            }
        }

        return $default;
    }
}

<?php

namespace Dranzd\Common\EventSourcing\Util;

use Laminas\Stdlib\ArrayUtils;

final class ArrayUtil
{
    final public static function keyToSnakeCase(array $array, $recursive = false): array
    {
        $keys = \array_keys($array);
        $values = \array_values($array);

        $keys = \array_map([StringUtil::class, 'toSnakeCase'], $keys);

        $array = \array_combine($keys, $values);

        if (!$recursive) {
            return $array;
        }

        return \array_map(
            function ($item) use ($recursive) {
                if (!\is_array($item)) {
                    return $item;
                }

                return self::keyToSnakeCase($item, $recursive);
            },
            $array
        );
    }

    final public static function keyToCamelCase(array $array, $recursive = false): array
    {
        $keys = \array_keys($array);
        $values = \array_values($array);

        $keys = \array_map([StringUtil::class, 'toCamelCase'], $keys);

        $array = \array_combine($keys, $values);

        if (!$recursive) {
            return $array;
        }

        return \array_map(
            function ($item) use ($recursive) {
                if (!\is_array($item)) {
                    return $item;
                }

                return self::keyToCamelCase($item, $recursive);
            },
            $array
        );
    }

    final public static function keyToKebabCase(array $array, $recursive = false): array
    {
        $keys = \array_keys($array);
        $values = \array_values($array);

        $keys = \array_map([StringUtil::class, 'toKebabCase'], $keys);

        $array = \array_combine($keys, $values);

        if (!$recursive) {
            return $array;
        }

        return \array_map(
            function ($item) use ($recursive) {
                if (!\is_array($item)) {
                    return $item;
                }

                return self::keyToKebabCase($item, $recursive);
            },
            $array
        );
    }

    public static function merge(array $a, array $b, $preserveNumericKeys = false): array
    {
        return ArrayUtils::merge($a, $b, $preserveNumericKeys);
    }
}

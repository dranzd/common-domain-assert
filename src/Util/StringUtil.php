<?php

namespace Dranzd\Common\EventSourcing\Util;

use Illuminate\Support\Str;

final class StringUtil
{
    final public static function toSnakeCase(string $string): string
    {
        return Str::snake($string);
    }

    public static function toCamelCase(string $string): string
    {
        return Str::camel($string);
    }

    public static function toKebabCase(string $string): string
    {
        return Str::kebab($string);
    }
}

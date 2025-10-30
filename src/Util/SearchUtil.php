<?php

namespace Dranzd\Common\EventSourcing\Util;

final class SearchUtil
{
    final public static function toLooseIncludeEnds(string $filter)
    {
        return '%' . static::toLoose($filter) . '%';
    }

    final public static function toLooseIncludeLeft(string $filter)
    {
        return '%' . static::toLoose($filter);
    }

    final public static function toLooseIncludeRight(string $filter)
    {
        return static::toLoose($filter) . '%';
    }

    final public static function toLoose(string $filter)
    {
        return preg_replace('/[^[:graph:]]+/i', '%', $filter);
    }
}

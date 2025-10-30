<?php

namespace Dranzd\Common\EventSourcing\Domain\Assert;

use Webmozart\Assert\Assert as BaseAssertion;

class Assertion extends BaseAssertion
{
    public static function datetime($value, $format, $message = '')
    {
        $datetime = \DateTime::createFromFormat($format, $value);
        if (null !== $datetime) {
            return;
        }

        static::reportInvalidArgument(
            \sprintf(
                $message ?: 'Expected a date with format %s',
                $format
            )
        );
    }
}

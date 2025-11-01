<?php

namespace Dranzd\Common\Domain\Assert;

use Webmozart\Assert\Assert as BaseAssertion;

class Assertion extends BaseAssertion
{
    public static function datetime(string $value, string $format, string $message = ''): void
    {
        $datetime = \DateTime::createFromFormat($format, $value);

        // Check if parsing failed
        if ($datetime === false) {
            static::reportInvalidArgument(
                \sprintf(
                    $message ?: 'Expected a date with format %s',
                    $format
                )
            );
        }

        // Check for warnings/errors (invalid dates like 2025-13-01 or 2025-02-29)
        $errors = \DateTime::getLastErrors();
        if ($errors && ($errors['warning_count'] > 0 || $errors['error_count'] > 0)) {
            static::reportInvalidArgument(
                \sprintf(
                    $message ?: 'Expected a date with format %s',
                    $format
                )
            );
        }
    }
}

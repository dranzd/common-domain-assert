<?php

declare(strict_types=1);

namespace Dranzd\Common\Domain\Assert;

use DateTime;
use Webmozart\Assert\Assert;

/**
 * Assertions for domain models.
 *
 * Every assertion of `Webmozart\Assert\Assert` is available here, plus `datetime()`. Depend on this
 * class rather than on the base class, and extend it to add the rules of your own domain in one place.
 *
 * @method static void nullOrDatetime(string|null $value, string $format, string $message = '')
 * @method static void allDatetime(iterable<string> $value, string $format, string $message = '')
 * @method static void allNullOrDatetime(iterable<string|null> $value, string $format, string $message = '')
 */
class Assertion extends Assert
{
    /**
     * Asserts that a string is a date or time written in the given format.
     *
     * The format is one accepted by `DateTime::createFromFormat()`. An out-of-range field in a complete
     * date or time is rejected: month 13, 29 February outside a leap year, hour 25. The parser is
     * otherwise lenient: it does not insist on zero padding (`2025-1-5` passes for `Y-m-d`) and does not
     * check a weekday name against the date.
     *
     * @param string $message Custom message, used as a `sprintf()` format: `%s` is replaced by the
     *                        format, and a literal percent sign must be written as `%%`.
     *
     * @throws \InvalidArgumentException If the value does not match the format.
     */
    public static function datetime(string $value, string $format, string $message = ''): void
    {
        $parsed = DateTime::createFromFormat($format, $value);
        $errors = DateTime::getLastErrors();

        $adjusted = $errors !== false && ($errors['warning_count'] > 0 || $errors['error_count'] > 0);

        if ($parsed === false || $adjusted) {
            static::reportInvalidArgument(
                \sprintf($message ?: 'Expected a date with format %s', $format)
            );
        }
    }
}

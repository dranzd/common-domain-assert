<?php

declare(strict_types=1);

namespace Dranzd\Common\Domain\Assert;

use DateTime;
use Webmozart\Assert\Assert;
use Webmozart\Assert\InvalidArgumentException;

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
     * The format is one accepted by `DateTime::createFromFormat()`. A month, day, hour, minute or second
     * that is out of range is rejected when the format describes a complete date or time: month 13,
     * 29 February outside a leap year, hour 25. The parser is otherwise lenient:
     *
     *  - it does not insist on zero padding, so `2025-1-5` passes for `Y-m-d`, and `25-11-02` passes as
     *    the year 25;
     *  - it does not check a weekday name against the date;
     *  - it does not range-check a partial format, so `31/02` passes for `d/m`.
     *
     * @param string $message Custom message, used as a `sprintf()` format only when the assertion
     *                        fails: `%s` is replaced by the format, and a literal percent sign must be
     *                        written as `%%`.
     *
     * @throws InvalidArgumentException If the value does not match the format. A subclass may report
     *                                  failures with its own exception type.
     */
    public static function datetime(string $value, string $format, string $message = ''): void
    {
        try {
            $parsed = DateTime::createFromFormat($format, $value);
        } catch (\ValueError) {
            // The parser refuses a value that contains a NUL byte.
            $parsed = false;
        }

        if ($parsed === false || DateTime::getLastErrors() !== false) {
            static::reportInvalidArgument(
                \sprintf($message ?: 'Expected a date with format %s', $format)
            );
        }
    }
}

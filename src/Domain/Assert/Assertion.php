<?php

declare(strict_types=1);

namespace Dranzd\Common\Domain\Assert;

use DateTime;
use Webmozart\Assert\Assert;

/**
 * Assertions for domain models.
 *
 * Every assertion of `Webmozart\Assert\Assert` is available here, together with the domain-oriented
 * checks below. Depend on this class rather than on the base class, and extend it to add the rules
 * of your own domain in one place.
 *
 * @method static void nullOrDatetime(string|null $value, string $format, string $message = '')
 * @method static void allDatetime(iterable<string> $value, string $format, string $message = '')
 */
class Assertion extends Assert
{
    /**
     * Asserts that a string is a date or time written in the given format.
     *
     * The format is one accepted by `DateTime::createFromFormat()`. A value the parser has to adjust,
     * such as `2025-02-29` or `2025-13-01`, is rejected; the parser does not insist on zero padding,
     * so `2025-1-5` passes for `Y-m-d`.
     *
     * @param string $message Custom message; `%s` is replaced by the format.
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
                \sprintf($message !== '' ? $message : 'Expected a date with format %s', $format)
            );
        }
    }
}

<?php

declare(strict_types=1);

namespace Dranzd\Common\Domain\Assert\Tests\Fixture;

use Dranzd\Common\Domain\Assert\Assertion;

/**
 * How an application extends the library: its own rules, and its own exception type.
 */
final class OrderAssertion extends Assertion
{
    public static function orderNumber(string $value, string $message = ''): void
    {
        self::regex($value, '/^ORD-\d{6}$/D', $message !== '' ? $message : 'Expected an order number, got %s');
    }

    protected static function reportInvalidArgument($message): never
    {
        throw new InvalidOrder($message);
    }
}

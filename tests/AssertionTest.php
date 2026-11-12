<?php

declare(strict_types=1);

namespace Dranzd\Common\Domain\Assert\Tests;

use Dranzd\Common\Domain\Assert\Assertion;
use Dranzd\Common\Domain\Assert\Tests\Fixture\InvalidOrder;
use Dranzd\Common\Domain\Assert\Tests\Fixture\OrderAssertion;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Webmozart\Assert\Assert;
use Webmozart\Assert\InvalidArgumentException;

#[CoversClass(Assertion::class)]
final class AssertionTest extends TestCase
{
    public function testItIsAWebmozartAssert(): void
    {
        self::assertContains(Assert::class, class_parents(Assertion::class));
    }

    #[DataProvider('provideValidValuesForInheritedAssertions')]
    public function testInheritedAssertionsPassForValidValues(string $assertion, mixed ...$arguments): void
    {
        Assertion::$assertion(...$arguments);

        $this->addToAssertionCount(1);
    }

    /**
     * @return iterable<string, list<mixed>>
     */
    public static function provideValidValuesForInheritedAssertions(): iterable
    {
        yield 'string' => ['string', 'order'];
        yield 'notEmpty' => ['notEmpty', 'order'];
        yield 'uuid' => ['uuid', '550e8400-e29b-41d4-a716-446655440000'];
        yield 'greaterThan' => ['greaterThan', 2, 1];
        yield 'inArray' => ['inArray', 'paid', ['open', 'paid']];
    }

    public function testInheritedAssertionsThrowTheLibraryException(): void
    {
        try {
            Assertion::uuid('not-a-uuid');
            self::fail('An invalid UUID was accepted.');
        } catch (\InvalidArgumentException $exception) {
            self::assertSame(InvalidArgumentException::class, $exception::class);
            self::assertSame('Value "not-a-uuid" is not a valid UUID.', $exception->getMessage());
        }
    }

    #[DataProvider('provideMatchingValues')]
    public function testDatetimeAcceptsAValueInTheFormat(string $value, string $format): void
    {
        Assertion::datetime($value, $format);

        $this->addToAssertionCount(1);
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function provideMatchingValues(): iterable
    {
        yield 'date' => ['2025-11-02', 'Y-m-d'];
        yield 'date and time' => ['2025-11-02 14:30:00', 'Y-m-d H:i:s'];
        yield 'time' => ['14:30:00', 'H:i:s'];
        yield 'day first' => ['02/11/2025', 'd/m/Y'];
        yield 'leap day' => ['2024-02-29', 'Y-m-d'];
        yield 'last second of the day' => ['23:59:59', 'H:i:s'];
        yield 'names and time zone' => ['Saturday, 02-Nov-2025 14:30:00 UTC', 'l, d-M-Y H:i:s T'];
        yield 'atom' => ['2025-11-02T10:00:00+02:00', DATE_ATOM];
        yield 'unix timestamp' => ['1700000000', 'U'];
        // The parser does not insist on zero padding.
        yield 'without zero padding' => ['2025-1-5', 'Y-m-d'];
        yield 'escaped literal' => ['Y-11', '\\Y-m'];
    }

    #[DataProvider('provideMismatchingValues')]
    public function testDatetimeRejectsAValueNotInTheFormat(string $value, string $format): void
    {
        try {
            Assertion::datetime($value, $format);
            self::fail(sprintf('"%s" was accepted for the format "%s".', $value, $format));
        } catch (\InvalidArgumentException $exception) {
            self::assertSame(InvalidArgumentException::class, $exception::class);
            self::assertSame('Expected a date with format ' . $format, $exception->getMessage());
        }
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function provideMismatchingValues(): iterable
    {
        yield 'text' => ['invalid-date', 'Y-m-d'];
        yield 'empty' => ['', 'Y-m-d'];
        yield 'date where a date and time is expected' => ['2025-11-02', 'Y-m-d H:i:s'];
        yield 'date and time where a date is expected' => ['2025-11-02 14:30:00', 'Y-m-d'];
        yield 'missing day' => ['2025-11', 'Y-m-d'];
        yield 'month 13' => ['2025-13-01', 'Y-m-d'];
        yield 'day 32' => ['2025-11-32', 'Y-m-d'];
        yield 'day 31 in a 30-day month' => ['2025-11-31', 'Y-m-d'];
        yield '29 February outside a leap year' => ['2025-02-29', 'Y-m-d'];
        yield 'hour 25' => ['25:30:00', 'H:i:s'];
        yield 'minute 60' => ['14:60:00', 'H:i:s'];
        yield 'leading space' => [' 2025-11-02', 'Y-m-d'];
        yield 'trailing space' => ['2025-11-02 ', 'Y-m-d'];
        yield 'trailing newline' => ["2025-11-02\n", 'Y-m-d'];
        yield 'wrong separator' => ['2025/11/02', 'Y-m-d'];
    }

    public function testDatetimeUsesACustomMessageAndFillsInTheFormat(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The delivery date must be written as Y-m-d.');

        Assertion::datetime('tomorrow', 'Y-m-d', 'The delivery date must be written as %s.');
    }

    public function testDatetimeUsesACustomMessageWithoutAPlaceholderAsItIs(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The delivery date is not valid.');

        Assertion::datetime('tomorrow', 'Y-m-d', 'The delivery date is not valid.');
    }

    public function testTheNullOrAndAllPrefixesWorkForDatetime(): void
    {
        Assertion::nullOrDatetime(null, 'Y-m-d');
        Assertion::nullOrDatetime('2025-11-02', 'Y-m-d');
        Assertion::allDatetime(['2025-11-02', '2025-11-03'], 'Y-m-d');
        $this->addToAssertionCount(3);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected a date with format Y-m-d');

        Assertion::allDatetime(['2025-11-02', 'tomorrow'], 'Y-m-d');
    }

    public function testASubclassCanAddItsOwnAssertions(): void
    {
        OrderAssertion::orderNumber('ORD-000123');
        OrderAssertion::datetime('2025-11-02', 'Y-m-d');

        $this->addToAssertionCount(2);
    }

    public function testASubclassReportsFailuresOfInheritedAssertionsWithItsOwnException(): void
    {
        $this->expectException(InvalidOrder::class);
        $this->expectExceptionMessage('Expected a date with format Y-m-d');

        OrderAssertion::datetime('tomorrow', 'Y-m-d');
    }

    public function testASubclassReportsFailuresOfItsOwnAssertionsWithItsOwnException(): void
    {
        $this->expectException(InvalidOrder::class);
        $this->expectExceptionMessage('Expected an order number, got "ORD-12"');

        OrderAssertion::orderNumber('ORD-12');
    }
}

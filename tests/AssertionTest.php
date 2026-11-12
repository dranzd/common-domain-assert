<?php

declare(strict_types=1);

namespace Dranzd\Common\Domain\Assert\Tests;

use Dranzd\Common\Domain\Assert\Assertion;
use Dranzd\Common\Domain\Assert\Tests\Fixture\InvalidOrder;
use Dranzd\Common\Domain\Assert\Tests\Fixture\OrderAssertion;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\AssertionFailedError;
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
        yield 'names and time zone' => ['Sunday, 02-Nov-2025 14:30:00 UTC', 'l, d-M-Y H:i:s T'];
        // The parser does not check a weekday name against the date: 2 November 2025 is a Sunday.
        yield 'weekday that does not match the date' => ['Saturday, 02-Nov-2025', 'l, d-M-Y'];
        yield 'atom' => ['2025-11-02T10:00:00+02:00', DATE_ATOM];
        yield 'unix timestamp' => ['1700000000', 'U'];
        // The parser does not insist on zero padding.
        yield 'without zero padding' => ['2025-1-5', 'Y-m-d'];
        yield 'escaped literal' => ['Y-11', '\\Y-m'];
        // The parser does not range-check a partial format: there is no year to tell that 31 February is wrong.
        yield 'out-of-range day in a partial format' => ['31/02', 'd/m'];
        yield 'two-digit year read as year 25' => ['25-11-02', 'Y-m-d'];
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
        yield 'the format itself' => ['Y-m-d', 'Y-m-d'];
        yield 'trailing NUL byte' => ["2025-11-02\0", 'Y-m-d'];
        yield 'only a NUL byte' => ["\0", 'Y-m-d'];
    }

    public function testDatetimeHasTheDocumentedSignature(): void
    {
        $method = new \ReflectionMethod(Assertion::class, 'datetime');
        $parameters = $method->getParameters();
        $describe = static fn (\ReflectionParameter $p): string => $p->getType() . ' $' . $p->getName();

        self::assertSame(
            'string $value, string $format, string $message',
            implode(', ', array_map($describe, $parameters))
        );
        self::assertSame('', $parameters[2]->getDefaultValue());
        self::assertSame('void', (string) $method->getReturnType());
        self::assertTrue($method->isPublic());
        self::assertTrue($method->isStatic());
        self::assertFalse((new \ReflectionClass(Assertion::class))->isFinal());
    }

    #[DataProvider('provideFailureMessages')]
    public function testDatetimeReportsTheMessageExactly(string $message, string $expected): void
    {
        $exception = self::failureOf(static function () use ($message): void {
            Assertion::datetime('tomorrow', 'Y-m-d', $message);
        });

        self::assertSame(InvalidArgumentException::class, $exception::class);
        self::assertSame($expected, $exception->getMessage());
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function provideFailureMessages(): iterable
    {
        yield 'custom message with the format filled in' => [
            'The delivery date must be written as %s.',
            'The delivery date must be written as Y-m-d.',
        ];
        yield 'custom message without a placeholder' => [
            'The delivery date is not valid.',
            'The delivery date is not valid.',
        ];
        yield 'escaped percent sign' => ['Only 100%% of dates, as %s', 'Only 100% of dates, as Y-m-d'];
        yield 'empty message falls back to the default' => ['', 'Expected a date with format Y-m-d'];
        yield 'zero message falls back to the default' => ['0', 'Expected a date with format Y-m-d'];
        yield 'a single space is used as it is' => [' ', ' '];
    }

    public function testDatetimeFormatsTheMessageOnlyWhenTheValueIsRejected(): void
    {
        Assertion::datetime('2025-11-02', 'Y-m-d', '100%');

        $this->addToAssertionCount(1);
    }

    public function testDatetimeFillsInNothingButTheFormat(): void
    {
        $this->expectException(\ArgumentCountError::class);

        Assertion::datetime('x', 'Y-m-d', 'Bad %2$s');
    }

    public function testDatetimeDoesNotHideAMalformedMessage(): void
    {
        $this->expectException(\ValueError::class);

        Assertion::datetime('x', 'Y-m-d', '100% wrong');
    }

    public function testNullOrDatetimeStillRejectsAnInvalidValue(): void
    {
        $exception = self::failureOf(static function (): void {
            Assertion::nullOrDatetime('tomorrow', 'Y-m-d');
        });

        self::assertSame(InvalidArgumentException::class, $exception::class);
        self::assertSame('Expected a date with format Y-m-d', $exception->getMessage());
    }

    public function testTheNullOrAndAllPrefixesWorkForDatetime(): void
    {
        Assertion::nullOrDatetime(null, 'Y-m-d');
        Assertion::nullOrDatetime('2025-11-02', 'Y-m-d');
        Assertion::allDatetime(['2025-11-02', '2025-11-03'], 'Y-m-d');
        Assertion::allDatetime([], 'Y-m-d');
        Assertion::allNullOrDatetime(['2025-11-02', null], 'Y-m-d');
        $this->addToAssertionCount(5);

        $exception = self::failureOf(static function (): void {
            Assertion::allDatetime(['2025-11-02', 'tomorrow'], 'Y-m-d');
        });

        self::assertSame(InvalidArgumentException::class, $exception::class);
        self::assertSame('Expected a date with format Y-m-d', $exception->getMessage());
    }

    public function testAllNullOrDatetimeRejectsAnInvalidValueAmongValidOnes(): void
    {
        $exception = self::failureOf(static function (): void {
            Assertion::allNullOrDatetime(['2025-11-02', null, 'tomorrow'], 'Y-m-d');
        });

        self::assertSame(InvalidArgumentException::class, $exception::class);
        self::assertSame('Expected a date with format Y-m-d', $exception->getMessage());
    }

    public function testASubclassCanAddItsOwnAssertions(): void
    {
        OrderAssertion::orderNumber('ORD-000123');
        OrderAssertion::datetime('2025-11-02', 'Y-m-d');

        $this->addToAssertionCount(2);

        $this->expectException(InvalidOrder::class);

        OrderAssertion::orderNumber("ORD-000123\n");
    }

    public function testASubclassReportsFailedDatetimeAssertionsWithItsOwnException(): void
    {
        $exception = self::failureOf(static function (): void {
            OrderAssertion::datetime('tomorrow', 'Y-m-d');
        });

        self::assertSame(InvalidOrder::class, $exception::class);
        self::assertSame('Expected a date with format Y-m-d', $exception->getMessage());
    }

    public function testASubclassReportsACustomDatetimeMessageExactly(): void
    {
        $exception = self::failureOf(static function (): void {
            OrderAssertion::datetime('tomorrow', 'Y-m-d', 'Deliver on %s');
        });

        self::assertSame(InvalidOrder::class, $exception::class);
        self::assertSame('Deliver on Y-m-d', $exception->getMessage());
    }

    public function testASubclassReportsAValueWithANulByteWithItsOwnException(): void
    {
        $exception = self::failureOf(static function (): void {
            OrderAssertion::datetime("2025-11-02\0", 'Y-m-d');
        });

        self::assertSame(InvalidOrder::class, $exception::class);
        self::assertSame('Expected a date with format Y-m-d', $exception->getMessage());
    }

    public function testASubclassReportsFailedInheritedAssertionsWithItsOwnException(): void
    {
        $exception = self::failureOf(static function (): void {
            OrderAssertion::uuid('nope');
        });

        self::assertSame(InvalidOrder::class, $exception::class);
        self::assertSame('Value "nope" is not a valid UUID.', $exception->getMessage());
    }

    public function testASubclassReportsFailedPrefixedDatetimeAssertionsWithItsOwnException(): void
    {
        $nullOr = self::failureOf(static function (): void {
            OrderAssertion::nullOrDatetime('tomorrow', 'Y-m-d');
        });
        $all = self::failureOf(static function (): void {
            OrderAssertion::allDatetime(['2025-11-02', 'tomorrow'], 'Y-m-d');
        });

        self::assertSame(InvalidOrder::class, $nullOr::class);
        self::assertSame('Expected a date with format Y-m-d', $nullOr->getMessage());
        self::assertSame(InvalidOrder::class, $all::class);
        self::assertSame('Expected a date with format Y-m-d', $all->getMessage());
    }

    public function testASubclassReportsFailuresOfItsOwnAssertionsWithItsOwnException(): void
    {
        $exception = self::failureOf(static function (): void {
            OrderAssertion::orderNumber('ORD-12');
        });

        self::assertSame(InvalidOrder::class, $exception::class);
        self::assertSame('Expected an order number, got "ORD-12"', $exception->getMessage());
    }

    private static function failureOf(callable $assertion): \Throwable
    {
        try {
            $assertion();
        } catch (\Throwable $exception) {
            return $exception;
        }

        throw new AssertionFailedError('The assertion accepted the value.');
    }
}

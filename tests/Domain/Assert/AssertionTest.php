<?php

declare(strict_types=1);

namespace Dranzd\Common\Domain\Assert\Tests;

use Dranzd\Common\Domain\Assert\Assertion;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Webmozart\Assert\Assert;

class AssertionTest extends TestCase
{
    public function testExtendsWebmozartAssert(): void
    {
        $this->assertTrue(
            is_subclass_of(Assertion::class, Assert::class),
            'Assertion class should extend Webmozart\Assert\Assert'
        );
    }

    public function testDatetimeWithValidFormatYmd(): void
    {
        // Should not throw exception
        Assertion::datetime('2025-11-02', 'Y-m-d');

        $this->assertTrue(true, 'Valid date format should pass');
    }

    public function testDatetimeWithValidFormatYmdHis(): void
    {
        // Should not throw exception
        Assertion::datetime('2025-11-02 14:30:00', 'Y-m-d H:i:s');

        $this->assertTrue(true, 'Valid datetime format should pass');
    }

    public function testDatetimeWithValidFormatDmY(): void
    {
        // Should not throw exception
        Assertion::datetime('02/11/2025', 'd/m/Y');

        $this->assertTrue(true, 'Valid date format d/m/Y should pass');
    }

    public function testDatetimeWithInvalidFormatThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected a date with format Y-m-d');

        Assertion::datetime('invalid-date', 'Y-m-d');
    }

    public function testDatetimeWithWrongFormatThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected a date with format Y-m-d H:i:s');

        // Date without time when time is expected
        Assertion::datetime('2025-11-02', 'Y-m-d H:i:s');
    }

    public function testDatetimeWithCustomErrorMessage(): void
    {
        $customMessage = 'Order date must be in Y-m-d format';

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($customMessage);

        Assertion::datetime('invalid-date', 'Y-m-d', $customMessage);
    }

    public function testDatetimeWithEmptyStringThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Assertion::datetime('', 'Y-m-d');
    }

    public function testDatetimeWithPartialDateThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        // Incomplete date
        Assertion::datetime('2025-11', 'Y-m-d');
    }

    public function testDatetimeWithInvalidMonthThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        // Month 13 doesn't exist
        Assertion::datetime('2025-13-01', 'Y-m-d');
    }

    public function testDatetimeWithInvalidDayThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        // Day 32 doesn't exist
        Assertion::datetime('2025-11-32', 'Y-m-d');
    }

    public function testDatetimeWithLeapYearDate(): void
    {
        // 2024 is a leap year, Feb 29 should be valid
        Assertion::datetime('2024-02-29', 'Y-m-d');

        $this->assertTrue(true, 'Leap year date should pass');
    }

    public function testDatetimeWithNonLeapYearDateThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        // 2025 is not a leap year, Feb 29 should fail
        Assertion::datetime('2025-02-29', 'Y-m-d');
    }

    public function testDatetimeWithTimeFormat(): void
    {
        Assertion::datetime('14:30:00', 'H:i:s');

        $this->assertTrue(true, 'Valid time format should pass');
    }

    public function testDatetimeWithInvalidTimeThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        // Hour 25 doesn't exist
        Assertion::datetime('25:30:00', 'H:i:s');
    }

    public function testDatetimeWithComplexFormat(): void
    {
        Assertion::datetime('Saturday, 02-Nov-2025 14:30:00 UTC', 'l, d-M-Y H:i:s T');

        $this->assertTrue(true, 'Complex datetime format should pass');
    }

    public function testInheritsWebmozartAssertMethods(): void
    {
        // Test that we can use methods from webmozart/assert
        Assertion::string('test', 'Should be a string');
        Assertion::integer(123, 'Should be an integer');
        Assertion::notEmpty('test', 'Should not be empty');

        $this->assertTrue(true, 'Inherited methods should work');
    }

    public function testInheritedMethodsThrowException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Assertion::string(123, 'Should be a string');
    }
}

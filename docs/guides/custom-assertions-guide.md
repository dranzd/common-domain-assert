# Custom Assertions Guide

Learn how to extend `dranzd/common-domain-assert` with your own domain-specific validation methods.

---

## Table of Contents

1. [Why Custom Assertions?](#why-custom-assertions)
2. [Creating Custom Assertions](#creating-custom-assertions)
3. [Common Patterns](#common-patterns)
4. [Testing Custom Assertions](#testing-custom-assertions)
5. [Best Practices](#best-practices)

---

## Why Custom Assertions?

Custom assertions help you:

- ✅ **Encapsulate domain validation logic** in reusable methods
- ✅ **Express domain concepts** clearly in code
- ✅ **Reduce duplication** across value objects
- ✅ **Maintain consistency** in validation rules
- ✅ **Improve readability** with domain-specific method names

---

## Creating Custom Assertions

### Step 1: Extend the Base Assertion Class

Create your own assertion class that extends the base:

```php
<?php

namespace App\Domain\Assert;

use Dranzd\Common\Domain\Assert\Assertion as BaseAssertion;

class Assertion extends BaseAssertion
{
    // Your custom assertions go here
}
```

### Step 2: Add Custom Assertion Methods

Custom assertion methods should:
- Be `public static`
- Accept the value to validate as the first parameter
- Accept an optional error message as the last parameter
- Call `static::reportInvalidArgument()` on failure

```php
public static function customValidation($value, string $message = ''): void
{
    if (!$this->isValid($value)) {
        static::reportInvalidArgument(
            $message ?: 'Default error message'
        );
    }
}
```

---

## Common Patterns

### Pattern 1: UUID Validation

```php
namespace App\Domain\Assert;

use Dranzd\Common\Domain\Assert\Assertion as BaseAssertion;

class Assertion extends BaseAssertion
{
    /**
     * Validates UUID v4 format
     *
     * @param string $value The value to validate
     * @param string $message Custom error message
     * @throws \InvalidArgumentException
     */
    public static function uuid(string $value, string $message = ''): void
    {
        $pattern = '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i';
        
        if (!preg_match($pattern, $value)) {
            static::reportInvalidArgument(
                $message ?: sprintf('Expected a valid UUID v4, got "%s"', $value)
            );
        }
    }
}
```

**Usage:**
```php
use App\Domain\Assert\Assertion;

Assertion::uuid($orderId, 'Order ID must be a valid UUID');
```

### Pattern 2: Domain-Specific ID Validation

```php
public static function aggregateId(string $value, string $message = ''): void
{
    // Reuse UUID validation
    static::uuid($value, $message ?: 'Expected a valid aggregate ID');
}

public static function entityId(string $value, string $message = ''): void
{
    static::uuid($value, $message ?: 'Expected a valid entity ID');
}

public static function orderId(string $value, string $message = ''): void
{
    static::uuid($value, $message ?: 'Expected a valid order ID');
}
```

### Pattern 3: Money/Currency Validation

```php
public static function currencyCode(string $value, string $message = ''): void
{
    static::length($value, 3, $message ?: 'Currency code must be 3 characters');
    static::regex(
        $value,
        '/^[A-Z]{3}$/',
        $message ?: 'Currency code must be uppercase ISO 4217 code'
    );
}

public static function moneyAmount(int $value, string $message = ''): void
{
    static::integer($value, $message ?: 'Money amount must be an integer');
    static::greaterThanEq($value, 0, $message ?: 'Money amount cannot be negative');
}

public static function money(int $amount, string $currency, string $message = ''): void
{
    static::moneyAmount($amount, $message);
    static::currencyCode($currency, $message);
}
```

### Pattern 4: Phone Number Validation

```php
public static function phoneNumber(string $value, string $message = ''): void
{
    // Remove all non-numeric characters
    $cleaned = preg_replace('/[^0-9]/', '', $value);
    
    static::notEmpty($cleaned, $message ?: 'Phone number cannot be empty');
    static::minLength($cleaned, 10, $message ?: 'Phone number must be at least 10 digits');
    static::maxLength($cleaned, 15, $message ?: 'Phone number cannot exceed 15 digits');
}
```

### Pattern 5: URL Validation

```php
public static function url(string $value, string $message = ''): void
{
    if (!filter_var($value, FILTER_VALIDATE_URL)) {
        static::reportInvalidArgument(
            $message ?: sprintf('Expected a valid URL, got "%s"', $value)
        );
    }
}

public static function httpUrl(string $value, string $message = ''): void
{
    static::url($value, $message);
    
    if (!preg_match('/^https?:\/\//', $value)) {
        static::reportInvalidArgument(
            $message ?: 'URL must start with http:// or https://'
        );
    }
}
```

### Pattern 6: Date Range Validation

```php
public static function dateInFuture(string $value, string $format = 'Y-m-d', string $message = ''): void
{
    static::datetime($value, $format, $message);
    
    $date = \DateTime::createFromFormat($format, $value);
    $now = new \DateTime();
    
    if ($date <= $now) {
        static::reportInvalidArgument(
            $message ?: 'Date must be in the future'
        );
    }
}

public static function dateInPast(string $value, string $format = 'Y-m-d', string $message = ''): void
{
    static::datetime($value, $format, $message);
    
    $date = \DateTime::createFromFormat($format, $value);
    $now = new \DateTime();
    
    if ($date >= $now) {
        static::reportInvalidArgument(
            $message ?: 'Date must be in the past'
        );
    }
}

public static function dateBetween(
    string $value,
    string $start,
    string $end,
    string $format = 'Y-m-d',
    string $message = ''
): void {
    static::datetime($value, $format, $message);
    static::datetime($start, $format, $message);
    static::datetime($end, $format, $message);
    
    $date = \DateTime::createFromFormat($format, $value);
    $startDate = \DateTime::createFromFormat($format, $start);
    $endDate = \DateTime::createFromFormat($format, $end);
    
    if ($date < $startDate || $date > $endDate) {
        static::reportInvalidArgument(
            $message ?: sprintf('Date must be between %s and %s', $start, $end)
        );
    }
}
```

### Pattern 7: Enum/Choice Validation

```php
public static function inEnum(string $value, array $allowedValues, string $message = ''): void
{
    if (!in_array($value, $allowedValues, true)) {
        static::reportInvalidArgument(
            $message ?: sprintf(
                'Expected one of: %s. Got: %s',
                implode(', ', $allowedValues),
                $value
            )
        );
    }
}

public static function orderStatus(string $value, string $message = ''): void
{
    static::inEnum(
        $value,
        ['pending', 'confirmed', 'shipped', 'delivered', 'cancelled'],
        $message ?: 'Invalid order status'
    );
}
```

### Pattern 8: Password Strength

```php
public static function strongPassword(string $value, string $message = ''): void
{
    static::minLength($value, 8, $message ?: 'Password must be at least 8 characters');
    
    if (!preg_match('/[A-Z]/', $value)) {
        static::reportInvalidArgument(
            $message ?: 'Password must contain at least one uppercase letter'
        );
    }
    
    if (!preg_match('/[a-z]/', $value)) {
        static::reportInvalidArgument(
            $message ?: 'Password must contain at least one lowercase letter'
        );
    }
    
    if (!preg_match('/[0-9]/', $value)) {
        static::reportInvalidArgument(
            $message ?: 'Password must contain at least one number'
        );
    }
    
    if (!preg_match('/[^A-Za-z0-9]/', $value)) {
        static::reportInvalidArgument(
            $message ?: 'Password must contain at least one special character'
        );
    }
}
```

### Pattern 9: Composite Validation

```php
public static function validEmail(string $value, string $message = ''): void
{
    static::notEmpty($value, $message ?: 'Email cannot be empty');
    static::email($value, $message ?: 'Invalid email format');
    static::maxLength($value, 255, $message ?: 'Email too long');
    
    // Additional domain rule: no disposable email domains
    $disposableDomains = ['tempmail.com', '10minutemail.com', 'guerrillamail.com'];
    $domain = substr(strrchr($value, "@"), 1);
    
    if (in_array($domain, $disposableDomains, true)) {
        static::reportInvalidArgument(
            $message ?: 'Disposable email addresses are not allowed'
        );
    }
}
```

---

## Testing Custom Assertions

### Unit Test Example

```php
<?php

namespace App\Tests\Domain\Assert;

use App\Domain\Assert\Assertion;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class AssertionTest extends TestCase
{
    public function testUuidWithValidValue(): void
    {
        // Should not throw
        Assertion::uuid('550e8400-e29b-41d4-a716-446655440000');
        
        $this->assertTrue(true);
    }

    public function testUuidWithInvalidValueThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected a valid UUID v4');
        
        Assertion::uuid('invalid-uuid');
    }

    public function testUuidWithCustomMessage(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Order ID must be valid');
        
        Assertion::uuid('invalid', 'Order ID must be valid');
    }

    public function testMoneyAmountWithValidValue(): void
    {
        Assertion::moneyAmount(1000);
        
        $this->assertTrue(true);
    }

    public function testMoneyAmountWithNegativeValueThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Money amount cannot be negative');
        
        Assertion::moneyAmount(-100);
    }

    public function testDateInFutureWithValidDate(): void
    {
        $tomorrow = (new \DateTime('+1 day'))->format('Y-m-d');
        
        Assertion::dateInFuture($tomorrow);
        
        $this->assertTrue(true);
    }

    public function testDateInFutureWithPastDateThrowsException(): void
    {
        $yesterday = (new \DateTime('-1 day'))->format('Y-m-d');
        
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Date must be in the future');
        
        Assertion::dateInFuture($yesterday);
    }
}
```

---

## Best Practices

### 1. Keep Methods Focused

Each assertion should validate one thing:

```php
// ✅ Good - focused validation
public static function uuid(string $value, string $message = ''): void
{
    // Only validates UUID format
}

// ❌ Bad - too many responsibilities
public static function validateUser(array $data, string $message = ''): void
{
    // Validates email, password, name, age, etc.
}
```

### 2. Reuse Existing Assertions

Build on top of existing assertions:

```php
// ✅ Good - reuses existing validations
public static function orderId(string $value, string $message = ''): void
{
    static::uuid($value, $message ?: 'Invalid order ID');
}

// ❌ Bad - duplicates UUID validation logic
public static function orderId(string $value, string $message = ''): void
{
    if (!preg_match('/^[0-9a-f]{8}-.../', $value)) {
        static::reportInvalidArgument($message);
    }
}
```

### 3. Provide Clear Error Messages

Default messages should be descriptive:

```php
// ✅ Good - clear default message
public static function phoneNumber(string $value, string $message = ''): void
{
    if (!$this->isValid($value)) {
        static::reportInvalidArgument(
            $message ?: sprintf(
                'Expected a valid phone number (10-15 digits), got "%s"',
                $value
            )
        );
    }
}

// ❌ Bad - vague message
public static function phoneNumber(string $value, string $message = ''): void
{
    if (!$this->isValid($value)) {
        static::reportInvalidArgument($message ?: 'Invalid value');
    }
}
```

### 4. Document Your Assertions

Add PHPDoc comments:

```php
/**
 * Validates that a string is a valid UUID v4
 *
 * @param string $value The value to validate
 * @param string $message Optional custom error message
 * @throws \InvalidArgumentException If the value is not a valid UUID v4
 *
 * @example
 * Assertion::uuid('550e8400-e29b-41d4-a716-446655440000'); // passes
 * Assertion::uuid('invalid-uuid'); // throws exception
 */
public static function uuid(string $value, string $message = ''): void
{
    // Implementation
}
```

### 5. Consider Performance

For expensive validations, consider caching or optimization:

```php
// ✅ Good - caches regex pattern
private static ?string $uuidPattern = null;

public static function uuid(string $value, string $message = ''): void
{
    if (self::$uuidPattern === null) {
        self::$uuidPattern = '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i';
    }
    
    if (!preg_match(self::$uuidPattern, $value)) {
        static::reportInvalidArgument($message ?: 'Expected a valid UUID v4');
    }
}
```

### 6. Type Hints

Use proper type hints for parameters:

```php
// ✅ Good - specific types
public static function money(int $amount, string $currency, string $message = ''): void

// ❌ Bad - mixed types
public static function money($amount, $currency, $message = ''): void
```

---

## Complete Example

Here's a complete custom assertion class for an e-commerce domain:

```php
<?php

namespace App\Domain\Assert;

use Dranzd\Common\Domain\Assert\Assertion as BaseAssertion;

class Assertion extends BaseAssertion
{
    // Identity validations
    public static function uuid(string $value, string $message = ''): void
    {
        $pattern = '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i';
        
        if (!preg_match($pattern, $value)) {
            static::reportInvalidArgument(
                $message ?: sprintf('Expected a valid UUID v4, got "%s"', $value)
            );
        }
    }

    public static function orderId(string $value, string $message = ''): void
    {
        static::uuid($value, $message ?: 'Invalid order ID');
    }

    public static function productId(string $value, string $message = ''): void
    {
        static::uuid($value, $message ?: 'Invalid product ID');
    }

    // Money validations
    public static function currencyCode(string $value, string $message = ''): void
    {
        static::length($value, 3, $message ?: 'Currency code must be 3 characters');
        static::regex(
            $value,
            '/^[A-Z]{3}$/',
            $message ?: 'Currency code must be uppercase ISO 4217 code'
        );
    }

    public static function moneyAmount(int $value, string $message = ''): void
    {
        static::integer($value, $message ?: 'Money amount must be an integer');
        static::greaterThanEq($value, 0, $message ?: 'Money amount cannot be negative');
    }

    // Order validations
    public static function orderStatus(string $value, string $message = ''): void
    {
        static::inEnum(
            $value,
            ['pending', 'confirmed', 'shipped', 'delivered', 'cancelled'],
            $message ?: 'Invalid order status'
        );
    }

    public static function quantity(int $value, string $message = ''): void
    {
        static::integer($value, $message ?: 'Quantity must be an integer');
        static::greaterThan($value, 0, $message ?: 'Quantity must be greater than zero');
        static::lessThanEq($value, 9999, $message ?: 'Quantity cannot exceed 9999');
    }

    // Helper method
    private static function inEnum(string $value, array $allowedValues, string $message): void
    {
        if (!in_array($value, $allowedValues, true)) {
            static::reportInvalidArgument(
                $message ?: sprintf(
                    'Expected one of: %s. Got: %s',
                    implode(', ', $allowedValues),
                    $value
                )
            );
        }
    }
}
```

---

## Summary

Custom assertions help you:
- ✅ **Encapsulate** domain validation logic
- ✅ **Express** domain concepts clearly
- ✅ **Reuse** validation across the domain
- ✅ **Maintain** consistency in validation rules
- ✅ **Test** validation logic in isolation

Follow the patterns and best practices in this guide to create robust, maintainable custom assertions for your domain.

---

## Related Documentation

- [Usage Guide](usage-guide.md) - Basic usage patterns
- [Value Object Integration](value-object-integration.md) - Using assertions in value objects

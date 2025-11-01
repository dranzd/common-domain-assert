# Domain Assert Usage Guide

A comprehensive guide to using `dranzd/common-domain-assert` for domain validation in DDD applications.

---

## Table of Contents

1. [Introduction](#introduction)
2. [Basic Usage](#basic-usage)
3. [Domain Validation Patterns](#domain-validation-patterns)
4. [Value Object Integration](#value-object-integration)
5. [Custom Assertions](#custom-assertions)
6. [Best Practices](#best-practices)

---

## Introduction

`dranzd/common-domain-assert` is a domain-specific assertion library designed for Domain-Driven Design (DDD) applications. It extends [webmozart/assert](https://github.com/webmozart/assert) with domain-focused validation helpers.

### When to Use

Use domain assertions to:
- ✅ Validate value object construction
- ✅ Enforce domain invariants
- ✅ Guard entity state transitions
- ✅ Validate aggregate boundaries
- ✅ Ensure domain rule compliance

### When NOT to Use

Don't use domain assertions for:
- ❌ Input validation (use form validators)
- ❌ API request validation (use API validators)
- ❌ Database constraints (use database validators)
- ❌ Business logic (use domain services)

---

## Basic Usage

### Installation

```bash
composer require dranzd/common-domain-assert
```

### Simple Assertions

```php
use Dranzd\Common\Domain\Assert\Assertion;

// String validation
Assertion::string($name, 'Name must be a string');
Assertion::notEmpty($name, 'Name cannot be empty');
Assertion::minLength($name, 3, 'Name must be at least 3 characters');

// Numeric validation
Assertion::integer($age, 'Age must be an integer');
Assertion::greaterThan($age, 0, 'Age must be positive');
Assertion::range($age, 18, 120, 'Age must be between 18 and 120');

// Type validation
Assertion::isInstanceOf($user, User::class, 'Must be a User instance');
```

### Exception Handling

All assertions throw `InvalidArgumentException` on failure:

```php
try {
    Assertion::email($email, 'Invalid email address');
} catch (InvalidArgumentException $e) {
    // Handle validation failure
    echo $e->getMessage(); // "Invalid email address"
}
```

---

## Domain Validation Patterns

### Pattern 1: Value Object Validation

Validate data when constructing value objects:

```php
use Dranzd\Common\Domain\Assert\Assertion;

final class EmailAddress
{
    private string $value;

    private function __construct(string $value)
    {
        Assertion::notEmpty($value, 'Email address cannot be empty');
        Assertion::email($value, 'Invalid email address format');
        Assertion::maxLength($value, 255, 'Email address too long');
        
        $this->value = strtolower($value);
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function equals(EmailAddress $other): bool
    {
        return $this->value === $other->value;
    }
}
```

### Pattern 2: Entity Invariants

Enforce business rules in entities:

```php
use Dranzd\Common\Domain\Assert\Assertion;

final class Order
{
    private OrderId $id;
    private OrderStatus $status;
    private Money $totalAmount;
    private array $items;

    public function __construct(OrderId $id, Money $totalAmount)
    {
        Assertion::isInstanceOf($id, OrderId::class);
        Assertion::isInstanceOf($totalAmount, Money::class);
        Assertion::greaterThan(
            $totalAmount->getAmount(), 
            0, 
            'Order total must be greater than zero'
        );

        $this->id = $id;
        $this->status = OrderStatus::pending();
        $this->totalAmount = $totalAmount;
        $this->items = [];
    }

    public function addItem(OrderItem $item): void
    {
        Assertion::false(
            $this->status->isShipped(),
            'Cannot add items to shipped order'
        );
        
        $this->items[] = $item;
    }

    public function ship(): void
    {
        Assertion::notEmpty($this->items, 'Cannot ship order without items');
        Assertion::true(
            $this->status->isPending(),
            'Can only ship pending orders'
        );

        $this->status = OrderStatus::shipped();
    }
}
```

### Pattern 3: Aggregate Root Validation

Validate aggregate boundaries:

```php
use Dranzd\Common\Domain\Assert\Assertion;

final class ShoppingCart
{
    private CartId $id;
    private CustomerId $customerId;
    private array $items;
    private const MAX_ITEMS = 50;

    public function __construct(CartId $id, CustomerId $customerId)
    {
        Assertion::isInstanceOf($id, CartId::class);
        Assertion::isInstanceOf($customerId, CustomerId::class);

        $this->id = $id;
        $this->customerId = $customerId;
        $this->items = [];
    }

    public function addItem(Product $product, Quantity $quantity): void
    {
        Assertion::isInstanceOf($product, Product::class);
        Assertion::isInstanceOf($quantity, Quantity::class);
        Assertion::lessThan(
            count($this->items),
            self::MAX_ITEMS,
            sprintf('Cart cannot contain more than %d items', self::MAX_ITEMS)
        );

        $this->items[] = new CartItem($product, $quantity);
    }
}
```

### Pattern 4: Domain Service Validation

Validate inputs to domain services:

```php
use Dranzd\Common\Domain\Assert\Assertion;

final class OrderPricingService
{
    public function calculateTotal(Order $order, Discount $discount): Money
    {
        Assertion::isInstanceOf($order, Order::class);
        Assertion::isInstanceOf($discount, Discount::class);
        Assertion::notEmpty($order->getItems(), 'Order must have items');
        Assertion::range(
            $discount->getPercentage(),
            0,
            100,
            'Discount percentage must be between 0 and 100'
        );

        // Calculate total...
    }
}
```

---

## Value Object Integration

### Simple Value Objects

```php
use Dranzd\Common\Domain\Assert\Assertion;

final class Username
{
    private string $value;

    private function __construct(string $value)
    {
        Assertion::notEmpty($value, 'Username cannot be empty');
        Assertion::minLength($value, 3, 'Username must be at least 3 characters');
        Assertion::maxLength($value, 20, 'Username cannot exceed 20 characters');
        Assertion::regex(
            $value,
            '/^[a-zA-Z0-9_]+$/',
            'Username can only contain letters, numbers, and underscores'
        );

        $this->value = $value;
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function toString(): string
    {
        return $this->value;
    }
}
```

### Complex Value Objects

```php
use Dranzd\Common\Domain\Assert\Assertion;

final class Money
{
    private int $amount; // in cents
    private string $currency;

    private function __construct(int $amount, string $currency)
    {
        Assertion::integer($amount, 'Amount must be an integer');
        Assertion::greaterThanEq($amount, 0, 'Amount cannot be negative');
        Assertion::notEmpty($currency, 'Currency cannot be empty');
        Assertion::length($currency, 3, 'Currency must be 3 characters (ISO 4217)');
        Assertion::regex(
            $currency,
            '/^[A-Z]{3}$/',
            'Currency must be uppercase ISO 4217 code'
        );

        $this->amount = $amount;
        $this->currency = $currency;
    }

    public static function fromAmount(int $amount, string $currency): self
    {
        return new self($amount, $currency);
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function add(Money $other): self
    {
        Assertion::same(
            $this->currency,
            $other->currency,
            'Cannot add money with different currencies'
        );

        return new self($this->amount + $other->amount, $this->currency);
    }
}
```

### Date/Time Value Objects

```php
use Dranzd\Common\Domain\Assert\Assertion;

final class OrderDate
{
    private string $date;

    private function __construct(string $date)
    {
        Assertion::datetime($date, 'Y-m-d', 'Order date must be in Y-m-d format');
        
        // Additional business rule: order date cannot be in the past
        $orderDate = \DateTime::createFromFormat('Y-m-d', $date);
        $today = new \DateTime('today');
        
        Assertion::greaterThanEq(
            $orderDate->getTimestamp(),
            $today->getTimestamp(),
            'Order date cannot be in the past'
        );

        $this->date = $date;
    }

    public static function fromString(string $date): self
    {
        return new self($date);
    }

    public static function today(): self
    {
        return new self(date('Y-m-d'));
    }

    public function toString(): string
    {
        return $this->date;
    }
}
```

---

## Custom Assertions

### Extending the Assertion Class

Create your own assertion class with domain-specific validations:

```php
namespace App\Domain\Assert;

use Dranzd\Common\Domain\Assert\Assertion as BaseAssertion;

class Assertion extends BaseAssertion
{
    /**
     * Validates UUID v4 format
     */
    public static function uuid(string $value, string $message = ''): void
    {
        $pattern = '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i';
        
        if (!preg_match($pattern, $value)) {
            static::reportInvalidArgument(
                $message ?: 'Expected a valid UUID v4'
            );
        }
    }

    /**
     * Validates phone number format
     */
    public static function phoneNumber(string $value, string $message = ''): void
    {
        $cleaned = preg_replace('/[^0-9]/', '', $value);
        
        if (strlen($cleaned) < 10 || strlen($cleaned) > 15) {
            static::reportInvalidArgument(
                $message ?: 'Expected a valid phone number'
            );
        }
    }

    /**
     * Validates aggregate ID (UUID)
     */
    public static function aggregateId(string $value, string $message = ''): void
    {
        static::uuid($value, $message ?: 'Expected a valid aggregate ID');
    }

    /**
     * Validates money amount (positive integer in cents)
     */
    public static function moneyAmount(int $value, string $message = ''): void
    {
        static::integer($value, $message ?: 'Money amount must be an integer');
        static::greaterThanEq($value, 0, $message ?: 'Money amount cannot be negative');
    }
}
```

### Using Custom Assertions

```php
use App\Domain\Assert\Assertion;

final class OrderId
{
    private string $value;

    private function __construct(string $value)
    {
        Assertion::uuid($value, 'Order ID must be a valid UUID');
        $this->value = $value;
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public static function generate(): self
    {
        return new self(\Ramsey\Uuid\Uuid::uuid4()->toString());
    }

    public function toString(): string
    {
        return $this->value;
    }
}
```

---

## Best Practices

### 1. Fail Fast

Validate at construction time, not later:

```php
// ✅ Good - validates immediately
final class Email
{
    private function __construct(string $value)
    {
        Assertion::email($value);
        $this->value = $value;
    }
}

// ❌ Bad - deferred validation
final class Email
{
    private string $value;
    
    public function setValue(string $value): void
    {
        $this->value = $value; // No validation!
    }
    
    public function validate(): bool
    {
        return filter_var($this->value, FILTER_VALIDATE_EMAIL);
    }
}
```

### 2. Use Descriptive Messages

Provide clear, actionable error messages:

```php
// ✅ Good - clear message
Assertion::range($age, 18, 120, 'Age must be between 18 and 120 years');

// ❌ Bad - generic message
Assertion::range($age, 18, 120);
```

### 3. Validate Business Rules, Not Data Types

Use assertions for domain rules, not basic type checking:

```php
// ✅ Good - domain rule
Assertion::greaterThan(
    $order->getTotalAmount(),
    $minimumOrderAmount,
    'Order total must meet minimum order amount'
);

// ⚠️ Less useful - basic type check (PHP type system handles this)
Assertion::integer($age);
```

### 4. Keep Value Objects Immutable

Never expose setters, always validate in constructor:

```php
// ✅ Good - immutable
final class Price
{
    private function __construct(private int $amount)
    {
        Assertion::greaterThan($amount, 0);
    }
    
    public function add(Price $other): self
    {
        return new self($this->amount + $other->amount);
    }
}

// ❌ Bad - mutable
final class Price
{
    private int $amount;
    
    public function setAmount(int $amount): void
    {
        $this->amount = $amount; // Breaks immutability!
    }
}
```

### 5. Use Type Hints + Assertions

Combine PHP type system with domain assertions:

```php
// ✅ Good - type hint + domain validation
public function __construct(int $age)
{
    Assertion::range($age, 0, 150, 'Age must be between 0 and 150');
    $this->age = $age;
}

// ⚠️ Redundant - type hint already ensures integer
public function __construct(int $age)
{
    Assertion::integer($age); // Unnecessary
    $this->age = $age;
}
```

### 6. Group Related Validations

Create helper methods for complex validations:

```php
final class Password
{
    private string $value;

    private function __construct(string $value)
    {
        $this->validatePasswordStrength($value);
        $this->value = password_hash($value, PASSWORD_DEFAULT);
    }

    private function validatePasswordStrength(string $password): void
    {
        Assertion::minLength($password, 8, 'Password must be at least 8 characters');
        Assertion::regex(
            $password,
            '/[A-Z]/',
            'Password must contain at least one uppercase letter'
        );
        Assertion::regex(
            $password,
            '/[a-z]/',
            'Password must contain at least one lowercase letter'
        );
        Assertion::regex(
            $password,
            '/[0-9]/',
            'Password must contain at least one number'
        );
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }
}
```

---

## Common Patterns

### Pattern: Optional Values

```php
final class UserProfile
{
    private Username $username;
    private ?Bio $bio;

    public function __construct(Username $username, ?Bio $bio = null)
    {
        Assertion::isInstanceOf($username, Username::class);
        
        if ($bio !== null) {
            Assertion::isInstanceOf($bio, Bio::class);
        }

        $this->username = $username;
        $this->bio = $bio;
    }
}
```

### Pattern: Collection Validation

```php
final class OrderItems
{
    private array $items;

    private function __construct(array $items)
    {
        Assertion::notEmpty($items, 'Order must have at least one item');
        Assertion::maxCount($items, 100, 'Order cannot have more than 100 items');
        Assertion::allIsInstanceOf($items, OrderItem::class);

        $this->items = $items;
    }

    public static function fromArray(array $items): self
    {
        return new self($items);
    }

    public function toArray(): array
    {
        return $this->items;
    }
}
```

---

## Related Documentation

- [Custom Assertions Guide](custom-assertions-guide.md) - Detailed guide on creating custom assertions
- [Value Object Patterns](value-object-integration.md) - Advanced value object patterns

---

## Summary

Domain assertions help enforce:
- ✅ **Domain Invariants** - Business rules that must always be true
- ✅ **Type Safety** - Beyond PHP's type system
- ✅ **Fail Fast** - Catch errors at construction time
- ✅ **Clear Errors** - Descriptive validation messages
- ✅ **Immutability** - Validate once, never change

Use them wisely in your domain layer to build robust, maintainable DDD applications.

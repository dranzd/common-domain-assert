# Common Domain Assert

A Domain-Driven Design (DDD) assertion library for domain validation, extending [webmozart/assert](https://github.com/webmozart/assert) with domain-specific validation helpers.

## Features

- **Domain-Specific Assertions**: Custom validation methods for DDD domain layer
- **Extends webmozart/assert**: Inherits all powerful assertions from webmozart/assert
- **DateTime Validation**: Built-in format validation for domain datetime values
- **Framework Agnostic**: Clean, reusable assertions with no framework dependencies
- **Type Safety**: Full PHP 8.3+ type hints for strict validation
- **Zero Dependencies**: Only depends on webmozart/assert
- **DDD-Focused**: Designed specifically for domain model validation

## Requirements

- PHP 8.3 or higher
- webmozart/assert ^1.11

## Installation

```bash
composer require dranzd/common-domain-assert
```

## Quick Start

### Using Built-in Assertions from webmozart/assert

Since `Assertion` extends `webmozart/assert`, you have access to all its methods:

```php
use Dranzd\Common\Domain\Assert\Assertion;

// String assertions
Assertion::string($value, 'Value must be a string');
Assertion::notEmpty($value, 'Value cannot be empty');
Assertion::length($value, 10, 'Value must be exactly 10 characters');

// Numeric assertions
Assertion::integer($value, 'Value must be an integer');
Assertion::greaterThan($value, 0, 'Value must be positive');

// Array assertions
Assertion::isArray($value, 'Value must be an array');
Assertion::keyExists($array, 'key', 'Array must contain key');

// Type assertions
Assertion::isInstanceOf($object, User::class, 'Must be a User instance');
```

### Custom Domain Assertions

#### DateTime Format Validation

Validate datetime strings against specific formats in your domain:

```php
use Dranzd\Common\Domain\Assert\Assertion;

// Validate datetime format
Assertion::datetime('2025-11-02', 'Y-m-d');
// Passes 

Assertion::datetime('invalid-date', 'Y-m-d');
// Throws InvalidArgumentException 

// Custom error message
Assertion::datetime(
    $dateString, 
    'Y-m-d H:i:s', 
    'Order date must be in format Y-m-d H:i:s'
);
```

### Usage in Domain Models

Use assertions to validate domain invariants:

```php
use Dranzd\Common\Domain\Assert\Assertion;

class OrderDate
{
    private string $date;

    public function __construct(string $date)
    {
        Assertion::datetime($date, 'Y-m-d', 'Order date must be in Y-m-d format');
        $this->date = $date;
    }

    public function toString(): string
    {
        return $this->date;
    }
}

class Order
{
    private OrderId $id;
    private OrderDate $orderDate;
    private Money $amount;

    public function __construct(OrderId $id, OrderDate $orderDate, Money $amount)
    {
        Assertion::isInstanceOf($id, OrderId::class);
        Assertion::isInstanceOf($orderDate, OrderDate::class);
        Assertion::isInstanceOf($amount, Money::class);
        
        $this->id = $id;
        $this->orderDate = $orderDate;
        $this->amount = $amount;
    }
}
```

## Architecture

This library is designed for the **Domain Layer** in DDD applications:

```
┌─────────────────────────────────────┐
│   Infrastructure Layer              │
│   (Repositories, External Services) │
├─────────────────────────────────────┤
│   Application Layer                 │
│   (Use Cases, Command Handlers)     │
├─────────────────────────────────────┤
│   Domain Layer                      │
│   (Entities, Value Objects)         │
│   ← This Library validates here     │
└─────────────────────────────────────┘
```

Use assertions to enforce domain invariants and validate value objects.

## Development

### Running Tests

```bash
composer test
```

### Static Analysis

```bash
composer phpstan
```

### Code Style

```bash
# Check code style
composer cs-check

# Fix code style automatically
composer cs-fix
```

### Using Docker

```bash
# Build the container
docker-compose build

# Run tests in container
docker-compose run --rm php ./vendor/bin/phpunit

# Interactive shell
docker-compose run --rm php bash
```

## Available Assertions

### From webmozart/assert

All assertions from [webmozart/assert](https://github.com/webmozart/assert#assertions) are available:

- **Type Assertions**: `string()`, `integer()`, `float()`, `boolean()`, `array()`, `object()`, `resource()`, `scalar()`
- **Comparison**: `eq()`, `notEq()`, `same()`, `notSame()`, `greaterThan()`, `lessThan()`, `range()`
- **String**: `startsWith()`, `endsWith()`, `contains()`, `length()`, `minLength()`, `maxLength()`, `regex()`
- **Array**: `keyExists()`, `keyNotExists()`, `count()`, `minCount()`, `maxCount()`
- **Object**: `isInstanceOf()`, `notInstanceOf()`, `propertyExists()`, `methodExists()`
- **Null**: `null()`, `notNull()`, `true()`, `false()`, `isEmpty()`, `notEmpty()`

### Custom Domain Assertions

#### `datetime($value, $format, $message = '')`

Validates that a string matches a specific datetime format.

```php
Assertion::datetime('2025-11-02', 'Y-m-d');
Assertion::datetime('2025-11-02 14:30:00', 'Y-m-d H:i:s');
Assertion::datetime($orderDate, 'Y-m-d', 'Order date must be in Y-m-d format');
```

## Design Principles

- **Domain-Driven Design**: Focused on domain layer validation
- **Fail Fast**: Validate invariants at construction time
- **Explicit Validation**: Clear, expressive assertion methods
- **Framework Agnostic**: No framework dependencies
- **Type Safety**: Full PHP 8.3+ type hints
- **Extensible**: Easy to add custom domain assertions

## License

MIT

## Contributing

Contributions are welcome! Please ensure:
- All tests pass
- Code follows PSR-12 standards
- PHPStan analysis passes at level 9
- New features include tests and documentation

## Extending with Custom Assertions

You can extend the `Assertion` class to add your own domain-specific validations:

```php
namespace YourApp\Domain\Assert;

use Dranzd\Common\Domain\Assert\Assertion as BaseAssertion;

class Assertion extends BaseAssertion
{
    public static function uuid(string $value, string $message = ''): void
    {
        if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $value)) {
            static::reportInvalidArgument(
                $message ?: 'Expected a valid UUID'
            );
        }
    }
    
    public static function aggregateId(string $value, string $message = ''): void
    {
        static::uuid($value, $message ?: 'Expected a valid aggregate ID');
    }
}
```

## Related Libraries

This library is part of the `dranzd/common-*` DDD toolkit:

- **dranzd/common-domain-assert** - Domain assertion utilities (this library)
- **dranzd/common-valueobject** - Value object base classes
- **dranzd/common-messaging** - Message primitives for CQRS
- **dranzd/common-event-sourcing** - Event sourcing infrastructure

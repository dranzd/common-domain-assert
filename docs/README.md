# Common Domain Assert Documentation

Welcome to the documentation for `dranzd/common-domain-assert` - a Domain-Driven Design (DDD) assertion library for domain validation.

---

## Quick Links

### Getting Started
- [Main README](../README.md) - Installation and quick start
- [Usage Guide](guides/usage-guide.md) - Comprehensive usage patterns

### Guides
- **[Usage Guide](guides/usage-guide.md)** - Learn how to use domain assertions effectively
- **[Custom Assertions Guide](guides/custom-assertions-guide.md)** - Create your own domain-specific validations

### Migration & Planning
- [Library Separation Plan](migration/library-separation-plan.md) - Overall library separation strategy
- [Strip-Down Plan](migration/common-assert-stripdown-plan.md) - This library's creation process

---

## What is Common Domain Assert?

`dranzd/common-domain-assert` is a focused assertion library designed specifically for validating domain models in DDD applications. It extends [webmozart/assert](https://github.com/webmozart/assert) with domain-specific validation helpers.

### Key Features

- ✅ **Domain-Specific** - Built for DDD domain layer validation
- ✅ **Extends webmozart/assert** - All powerful assertions included
- ✅ **DateTime Validation** - Built-in format validation
- ✅ **Extensible** - Easy to add custom domain assertions
- ✅ **Type Safe** - Full PHP 8.3+ type hints
- ✅ **Zero Dependencies** - Only webmozart/assert required

---

## Documentation Structure

### For Users

**New to the library?** Start here:
1. Read the [Main README](../README.md) for installation
2. Follow the [Usage Guide](guides/usage-guide.md) for patterns
3. Learn to [Create Custom Assertions](guides/custom-assertions-guide.md)

### For Contributors

**Want to contribute?** Check these:
- [Strip-Down Plan](migration/common-assert-stripdown-plan.md) - Implementation details
- [Library Separation Plan](migration/library-separation-plan.md) - Overall architecture

---

## Common Use Cases

### 1. Value Object Validation

```php
use Dranzd\Common\Domain\Assert\Assertion;

final class EmailAddress
{
    private function __construct(private string $value)
    {
        Assertion::email($value, 'Invalid email address');
        Assertion::maxLength($value, 255, 'Email too long');
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }
}
```

### 2. Entity Invariants

```php
final class Order
{
    public function __construct(OrderId $id, Money $total)
    {
        Assertion::isInstanceOf($id, OrderId::class);
        Assertion::greaterThan($total->getAmount(), 0, 'Order total must be positive');
        
        $this->id = $id;
        $this->total = $total;
    }
}
```

### 3. Custom Domain Assertions

```php
namespace App\Domain\Assert;

use Dranzd\Common\Domain\Assert\Assertion as BaseAssertion;

class Assertion extends BaseAssertion
{
    public static function uuid(string $value, string $message = ''): void
    {
        if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $value)) {
            static::reportInvalidArgument($message ?: 'Expected a valid UUID');
        }
    }
}
```

---

## Available Assertions

### From webmozart/assert

All assertions from webmozart/assert are available. See the [webmozart/assert documentation](https://github.com/webmozart/assert#assertions) for the complete list.

**Common assertions:**
- Type: `string()`, `integer()`, `boolean()`, `array()`, `object()`
- Comparison: `eq()`, `same()`, `greaterThan()`, `lessThan()`, `range()`
- String: `startsWith()`, `contains()`, `length()`, `regex()`, `email()`
- Array: `keyExists()`, `count()`, `allIsInstanceOf()`
- Null: `null()`, `notNull()`, `notEmpty()`

### Custom Domain Assertions

#### `datetime($value, $format, $message = '')`

Validates that a string matches a specific datetime format.

```php
Assertion::datetime('2025-11-02', 'Y-m-d');
Assertion::datetime('2025-11-02 14:30:00', 'Y-m-d H:i:s');
```

---

## Philosophy

### Fail Fast

Validate at construction time, not later:

```php
// ✅ Good
final class Age
{
    private function __construct(private int $value)
    {
        Assertion::range($value, 0, 150, 'Age must be between 0 and 150');
    }
}

// ❌ Bad
final class Age
{
    public function setValue(int $value): void
    {
        $this->value = $value; // No validation!
    }
}
```

### Domain Rules, Not Data Types

Use assertions for domain rules, not basic type checking:

```php
// ✅ Good - domain rule
Assertion::greaterThan($order->getTotal(), $minimumOrderAmount);

// ⚠️ Less useful - type system handles this
Assertion::integer($age);
```

### Immutability

Keep value objects immutable:

```php
// ✅ Good
final class Money
{
    private function __construct(private int $amount)
    {
        Assertion::greaterThanEq($amount, 0);
    }
    
    public function add(Money $other): self
    {
        return new self($this->amount + $other->amount);
    }
}
```

---

## Related Libraries

This library is part of the `dranzd/common-*` DDD toolkit:

- **dranzd/common-domain-assert** - Domain assertion utilities (this library)
- **dranzd/common-valueobject** - Value object base classes
- **dranzd/common-messaging** - Message primitives for CQRS
- **dranzd/common-event-sourcing** - Event sourcing infrastructure

---

## Contributing

Contributions are welcome! Please ensure:
- All tests pass
- Code follows PSR-12 standards
- PHPStan analysis passes at level 9
- New features include tests and documentation

---

## License

MIT License - see [LICENSE](../LICENSE) file for details.

---

## Support

- **Issues**: [GitHub Issues](https://github.com/dranzd/common-domain-assert/issues)
- **Documentation**: This documentation
- **Examples**: See the [Usage Guide](guides/usage-guide.md)

---

**Last Updated**: 2025-11-02

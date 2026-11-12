# Common Domain Assert

[![CI](https://github.com/dranzd/common-domain-assert/actions/workflows/ci.yml/badge.svg)](https://github.com/dranzd/common-domain-assert/actions/workflows/ci.yml)
[![Latest version](https://img.shields.io/packagist/v/dranzd/common-domain-assert.svg)](https://packagist.org/packages/dranzd/common-domain-assert)
[![PHP version](https://img.shields.io/packagist/php-v/dranzd/common-domain-assert.svg)](composer.json)
[![License: MIT](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)

Assertions for domain models. `Assertion` is [webmozart/assert](https://github.com/webmozarts/assert)
plus a `datetime()` check, behind a class meant to be extended with the rules of your own domain.

The point of the indirection is ownership. Value objects and entities call `Assertion::...` instead
of a third-party class, so a domain layer has one place to add its own rules and to decide which
exception a broken rule throws.

## Installation

```bash
composer require dranzd/common-domain-assert
```

Requires PHP 8.3 or newer.

## Usage

Every assertion of `webmozart/assert` is available with the same name, arguments and messages.

```php
use Dranzd\Common\Domain\Assert\Assertion;

final class EmailAddress
{
    public function __construct(private readonly string $value)
    {
        Assertion::email($value, 'Expected an email address, got %s');
        Assertion::maxLength($value, 254);
    }
}

new EmailAddress('ada@example.com'); // passes
new EmailAddress('not-an-email');
// throws Webmozart\Assert\InvalidArgumentException: Expected an email address, got "not-an-email"
```

### Added assertions

| Method | Passes when |
|---|---|
| `datetime(string $value, string $format, string $message = '')` | `$value` is a date or time written in `$format` |

```php
Assertion::datetime('2025-11-02', 'Y-m-d');           // passes
Assertion::datetime('2025-02-29', 'Y-m-d');           // throws: Expected a date with format Y-m-d
Assertion::datetime('2025-11-02', 'Y-m-d H:i:s');     // throws: Expected a date with format Y-m-d H:i:s

Assertion::datetime('tomorrow', 'Y-m-d', 'The delivery date must be written as %s.');
// throws: The delivery date must be written as Y-m-d.
```

The format is one accepted by `DateTime::createFromFormat()`. An out-of-range field in a complete
date or time is rejected: month 13, 29 February outside a leap year, hour 25. The parser is otherwise
lenient: it does not insist on zero padding, so `2025-1-5` passes for `Y-m-d`, and it does not check a
weekday name against the date.

The `nullOr` and `all` prefixes of the base library work for added assertions too:

```php
Assertion::nullOrDatetime(null, 'Y-m-d');                        // passes
Assertion::allDatetime(['2025-11-02', 'tomorrow'], 'Y-m-d');     // throws
```

### Extending it for your domain

Subclass `Assertion` to add your own rules. Overriding `reportInvalidArgument()` changes the
exception for every assertion, inherited ones included.

```php
use Dranzd\Common\Domain\Assert\Assertion;

final class InvalidOrder extends \DomainException
{
}

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

OrderAssertion::orderNumber('ORD-000123'); // passes
OrderAssertion::orderNumber('ORD-12');     // throws InvalidOrder: Expected an order number, got "ORD-12"
OrderAssertion::uuid('nope');              // throws InvalidOrder: Value "nope" is not a valid UUID.
```

## Compatibility

This package requires `webmozart/assert` 1.x (`^1.11`). The inherited assertions behave exactly as
that version documents; this package does not change them. Version 2.x is not supported yet: it
changes method signatures that subclasses override and removes the mechanism behind the `nullOr`
and `all` prefixes for added assertions.

## Development

```bash
composer install
composer check        # coding standards, static analysis and tests
```

| Command | Tool |
|---|---|
| `composer test` | PHPUnit |
| `composer test:coverage` | PHPUnit with coverage (needs pcov or Xdebug) |
| `composer phpstan` | PHPStan, max level |
| `composer cs-check` / `composer cs-fix` | PHP_CodeSniffer, PSR-12 |

A Docker setup is included for running the same commands without a local PHP: `make install`, then
`make check`.

CI runs the suite on PHP 8.3, 8.4 and 8.5, against the locked and the lowest allowed dependency
versions, and requires at least 95% line coverage.

## Contributing

See [CONTRIBUTING.md](CONTRIBUTING.md). Security issues: see [SECURITY.md](SECURITY.md).

## License

[MIT](LICENSE)

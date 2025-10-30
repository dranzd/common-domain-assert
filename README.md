# Common Event Sourcing

A framework-agnostic event sourcing library that provides common abstractions and utilities for building event-sourced applications using Domain-Driven Design (DDD) principles and Onion Architecture.

## Features

- **Event Sourcing Primitives**: Base classes and interfaces for event-sourced aggregates
- **CQRS Support**: Command, Event, and Query message types with generic infrastructure
- **Event Versioning**: Built-in support for event schema evolution with upcasters
- **Named Constructor Pattern**: Expressive, domain-focused event creation
- **Value Object Integration**: Type-safe domain modeling with validation
- **Metadata Support**: Causation, correlation, and custom metadata tracking
- **Framework Support**: Compatible with Laravel and Laminas frameworks
- **DDD Building Blocks**: Domain events, aggregates, and value objects
- **Onion Architecture**: Clean separation of concerns with framework-agnostic core
- **Type Safety**: Full PHP 8.1+ type hints and strict typing

## Requirements

- PHP 8.1 or higher
- ext-json

## Installation

```bash
composer require dranzd/common-event-sourcing
```

### Dependencies

```bash
composer require ramsey/uuid:^4.0
composer require doctrine/collections:^2.0
composer require webmozart/assert:^1.11
```

**Optional** (for framework integration):
```bash
# For Laravel
composer require illuminate/support

# For Laminas
composer require laminas/laminas-stdlib
```

## Quick Start

### 1. Create a Domain Event

```php
use Dranzd\Common\EventSourcing\Domain\EventSourcing\AbstractAggregateEvent;
use Dranzd\Common\EventSourcing\Domain\EventSourcing\AggregateEventWithPrivateConstructorTrait;

final class OrderPlaced extends AbstractAggregateEvent
{
    use AggregateEventWithPrivateConstructorTrait;
    
    const MESSAGE_NAME = 'order.order.placed';
    
    private OrderId $orderId;
    private CustomerId $customerId;
    private Money $totalAmount;
    
    // Named constructor - expressive and domain-focused
    public static function withAmount(
        OrderId $orderId,
        CustomerId $customerId,
        Money $totalAmount
    ): self {
        $instance = new self();
        $instance->orderId = $orderId;
        $instance->customerId = $customerId;
        $instance->totalAmount = $totalAmount;
        return $instance;
    }
    
    public function getPayload(): array
    {
        return [
            'order_id' => $this->orderId->toNative(),
            'customer_id' => $this->customerId->toNative(),
            'total_amount' => $this->totalAmount->toArray(),
        ];
    }
    
    protected function setPayload(array $payload): void
    {
        if (empty($payload)) {
            return;
        }
        
        $this->orderId = OrderId::fromNative($payload['order_id']);
        $this->customerId = CustomerId::fromNative($payload['customer_id']);
        $this->totalAmount = Money::fromArray($payload['total_amount']);
    }
}
```

### 2. Create an Aggregate Root

Extend the `AggregateRoot` interface to create your domain aggregates:

```php
use Dranzd\Common\EventSourcing\EventSourcedAggregate;

class Order extends EventSourcedAggregate
{
    private string $orderId;
    private string $status;
    
    protected function handleEvent($event): void
    {
        match (get_class($event)) {
            OrderPlaced::class => $this->applyOrderPlaced($event),
            OrderConfirmed::class => $this->applyOrderConfirmed($event),
            default => null
        };
    }
    
    private function applyOrderPlaced(OrderPlaced $event): void
    {
        $this->orderId = $event->orderId;
        $this->status = 'placed';
    }
    
    private function applyOrderConfirmed(OrderConfirmed $event): void
    {
        $this->status = 'confirmed';
    }
    
    public function placeOrder(string $orderId): void
    {
        $this->apply(new OrderPlaced($orderId));
    }
    
    public function confirmOrder(): void
    {
        $this->apply(new OrderConfirmed($this->orderId));
    }
}
```

### Working with Events

```php
// Create and apply events
$order = new Order();
$order->placeOrder('order-123');
$order->confirmOrder();

// Retrieve uncommitted events
$events = $order->releaseEvents();

// Events are cleared after release
$moreEvents = $order->releaseEvents(); // Returns empty array
```

## Architecture

This library follows **Onion Architecture** principles:

```
┌─────────────────────────────────────┐
│   Infrastructure Layer              │
│   (Laravel/Laminas Adapters)        │
├─────────────────────────────────────┤
│   Application Layer                 │
│   (Use Cases, Command Handlers)     │
├─────────────────────────────────────┤
│   Domain Layer                      │
│   (Aggregates, Events, Value Objs)  │
│   ← This Library                    │
└─────────────────────────────────────┘
```

The core domain logic remains framework-agnostic, with framework-specific implementations in outer layers.

## Framework Integration

### Laravel

```php
// Service Provider
use Dranzd\Common\EventSourcing\EventSourcedAggregate;

class EventSourcingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Register your event store, repositories, etc.
    }
}
```

### Laminas

```php
// Module configuration
return [
    'service_manager' => [
        'factories' => [
            // Your event sourcing services
        ],
    ],
];
```

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

## Design Principles

- **Domain-Driven Design**: Aggregates, entities, value objects, and domain events
- **Event Sourcing**: State changes captured as immutable events
- **CQRS Ready**: Separate command and query models
- **Framework Agnostic**: Core domain logic independent of infrastructure
- **Testability**: Easy to unit test without framework dependencies
- **Named Constructors**: Expressive event creation enforced by design
- **Value Object Validation**: Type safety through domain objects
- **Event Versioning**: Schema evolution without data migration

## Documentation

- **[Event Design Patterns](docs/guides/event-patterns.md)** - Comprehensive guide to event patterns
- **[Event Versioning Example](docs/examples/event-versioning-example.md)** - Complete versioning guide
- **[Domain Event Design Review](docs/reviews/domain-event-design-review.md)** - Design analysis
- **[Namespace Migration Notes](docs/migration/namespace-migration.md)** - Migration documentation

## Key Features Explained

### Named Constructor Pattern

Events use static factory methods for creation, making code more expressive:

```php
// ✅ Expressive - reads like domain language
$event = OrderPlaced::withAmount($orderId, $customerId, $totalAmount);

// ❌ Not allowed - constructor is private
$event = new OrderPlaced($orderId, $customerId, $totalAmount);
```

### Event Versioning

Built-in support for event schema evolution:

```php
final class OrderPlaced extends AbstractAggregateEvent implements VersionedEvent
{
    use VersionedEventTrait;
    
    const CURRENT_VERSION = 2;  // Increment when schema changes
    
    // Upcasters handle old event versions automatically
}
```

### Metadata Tracking

Convenient methods for causation and correlation:

```php
$event = OrderPlaced::withAmount($orderId, $customerId, $amount);

// Add causation (this event was caused by a command)
$event = $event->withCausationId($command->getMessageUuid());

// Add correlation (for distributed tracing)
$event = $event->withCorrelationId($traceId);

// Read metadata
$causationId = $event->causationId();
$correlationId = $event->correlationId();
$occurredAt = $event->occurredAt();
```

## License

MIT

## Contributing

Contributions are welcome! Please ensure:
- All tests pass
- Code follows PSR-12 standards
- PHPStan analysis passes at level 8 or higher
- New features include tests and documentation

## Changelog

### Recent Updates

- ✅ **Event Versioning System** - Complete versioning support with upcasters
- ✅ **Convenience Methods** - Added metadata helpers to AbstractEvent
- ✅ **Namespace Migration** - Migrated to `Dranzd\Common\EventSourcing`
- ✅ **Documentation** - Comprehensive guides and examples

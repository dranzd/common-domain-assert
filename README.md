# Common Event Sourcing

A framework-agnostic event sourcing library that provides common abstractions and utilities for building event-sourced applications using Domain-Driven Design (DDD) principles and Onion Architecture.

## Features

- **Event Sourcing Primitives**: Base classes and interfaces for event-sourced aggregates
- **CQRS Support**: Command, Event, and Query message types with generic infrastructure
- **Event Versioning**: Built-in support for event schema evolution with upcasters and VersionedEventTrait
- **Aggregate Root Pattern**: AggregateRootTrait with automatic event replay and version management
- **Flexible Metadata**: Trait-based causation, correlation, and actor tracking
- **Metadata Injectors**: Utilities to transfer metadata from commands to events
- **Named Constructor Pattern**: Expressive, domain-focused event creation
- **DDD Building Blocks**: Domain events, aggregates, and value objects
- **Framework Agnostic**: Clean separation of concerns with no framework dependencies
- **Type Safety**: Full PHP 8.1+ type hints with static return types for fluent interfaces
- **Comprehensive Tests**: 123 tests with 288 assertions ensuring reliability

## Requirements

- PHP 8.1 or higher
- ext-json
- ramsey/uuid ^4.7
- webmozart/assert ^1.11

## Installation

```bash
composer require dranzd/common-event-sourcing
```

The required dependencies (`ramsey/uuid` and `webmozart/assert`) will be installed automatically.

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

Implement the `AggregateRoot` interface using the `AggregateRootTrait`:

```php
use Dranzd\Common\EventSourcing\Domain\EventSourcing\AggregateRoot;
use Dranzd\Common\EventSourcing\Domain\EventSourcing\AggregateRootTrait;

class Order implements AggregateRoot
{
    use AggregateRootTrait;
    
    private string $orderId;
    private string $status;
    
    public function __construct(string $orderId)
    {
        $this->orderId = $orderId;
    }
    
    public function getAggregateRootUuid(): string
    {
        return $this->orderId;
    }
    
    public function placeOrder(CustomerId $customerId, Money $amount): void
    {
        $event = OrderPlaced::withAmount(
            OrderId::fromNative($this->orderId),
            $customerId,
            $amount
        );
        
        $this->recordThat($event);
    }
    
    public function confirmOrder(): void
    {
        $event = OrderConfirmed::forOrder(OrderId::fromNative($this->orderId));
        $this->recordThat($event);
    }
    
    // Apply methods are called automatically by the trait
    protected function applyOnOrderPlaced(OrderPlaced $event): void
    {
        $payload = $event->getPayload();
        $this->orderId = $payload['order_id'];
        $this->status = 'placed';
    }
    
    protected function applyOnOrderConfirmed(OrderConfirmed $event): void
    {
        $this->status = 'confirmed';
    }
}
```

### Working with Events

```php
// Create and record events
$order = new Order('order-123');
$order->placeOrder($customerId, $amount);
$order->confirmOrder();

// Retrieve recorded events
$events = $order->getRecordedEvents();

// Pop events (retrieve and clear)
$events = $order->popRecordedEvents();

// Reconstitute from event history
$order = Order::reconstituteFromHistory($events);
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

Events that extend `AbstractAggregateEvent` automatically include causation, correlation, and actor tracking:

```php
$event = OrderPlaced::withAmount($orderId, $customerId, $amount);

// AbstractAggregateEvent includes these traits:
// - CausationCapableTrait
// - CorrelationCapableTrait  
// - ActorCapableTrait

// Add causation (this event was caused by a command)
$event = $event->withCausationId($command->getMessageUuid());
$event = $event->withCausationName('PlaceOrderCommand');

// Add correlation (for distributed tracing)
$event = $event->withCorrelationId($traceId);

// Add actor information
$event = $event->withActorId($userId);
$event = $event->withActorName('John Doe');

// Read metadata
$causationId = $event->getCausationId();
$correlationId = $event->getCorrelationId();
$actorId = $event->getActorId();
$occurredAt = $event->occurredAt();
$eventId = $event->eventId();

// Access metadata directly
$value = $event->getMetadataValue('custom_key');
$hasKey = $event->hasMetadata('custom_key');
```

For custom events, you can selectively use traits:

```php
use Dranzd\Common\EventSourcing\Domain\Event\AbstractEvent;
use Dranzd\Common\EventSourcing\Domain\Message\CausationCapable;
use Dranzd\Common\EventSourcing\Domain\Message\CausationCapableTrait;

class CustomEvent extends AbstractEvent implements CausationCapable
{
    use CausationCapableTrait;
    
    const MESSAGE_NAME = 'custom.event';
    
    // Now has withCausationId(), getCausationId(), etc.
}
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

- ✅ **Comprehensive Test Suite** - 123 tests with 288 assertions covering all core functionality
- ✅ **Event Versioning System** - Complete versioning support with upcasters and `VersionedEventTrait`
- ✅ **Trait-Based Capabilities** - Flexible metadata support via `CausationCapableTrait`, `CorrelationCapableTrait`, `ActorCapableTrait`
- ✅ **Aggregate Root Pattern** - `AggregateRootTrait` with automatic event application via `applyOn{EventName}` methods
- ✅ **Type Safety** - Full `static` return types for fluent interfaces and method chaining
- ✅ **Metadata Injectors** - Command-to-Event metadata transfer utilities
- ✅ **Namespace Migration** - Migrated to `Dranzd\Common\EventSourcing`
- ✅ **Documentation** - Comprehensive guides and examples

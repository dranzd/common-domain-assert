# Common Event Sourcing

A framework-agnostic event sourcing library that provides common abstractions and utilities for building event-sourced applications using Domain-Driven Design (DDD) principles and Onion Architecture.

## Features

- **Event Sourcing Primitives**: Base classes and interfaces for event-sourced aggregates
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

## Core Concepts

### Event Sourced Aggregates

Extend the `EventSourcedAggregate` class to create your domain aggregates:

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

## License

MIT

## Contributing

Contributions are welcome! Please ensure:
- All tests pass
- Code follows PSR-12 standards
- PHPStan analysis passes at level 8 or higher
- New features include tests and documentation

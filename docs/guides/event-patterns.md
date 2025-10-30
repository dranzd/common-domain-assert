# Event Design Patterns Guide

This guide documents the design patterns and best practices used in the event sourcing library.

---

## Table of Contents

1. [Named Constructor Pattern](#named-constructor-pattern)
2. [Event Payload Serialization](#event-payload-serialization)
3. [Value Object Integration](#value-object-integration)
4. [Event Versioning](#event-versioning)
5. [Metadata Usage Patterns](#metadata-usage-patterns)
6. [Event Naming Conventions](#event-naming-conventions)

---

## Named Constructor Pattern

### Why Named Constructors?

The library enforces the **Named Constructor Pattern** for creating events. This provides several benefits:

- **Expressive Domain Language**: Creation methods read like business language
- **Multiple Creation Scenarios**: Different named constructors for different contexts
- **Type Safety**: Value Objects ensure data integrity at creation
- **Immutability**: Properties set once during construction
- **Clean Serialization**: Separation between creation and persistence

### How It Works

The `AbstractEvent` constructor is intentionally empty (no payload parameter), forcing you to use static factory methods:

```php
abstract class AbstractEvent extends GenericMessage implements Event
{
    public function __construct()
    {
        parent::__construct(
            Uuid::generateAsString(),
            static::expectedMessageName(),
            []  // ← Empty payload forces named constructors
        );
    }
}
```

### Implementation Pattern

#### Step 1: Use Private Constructor Trait

```php
use Dranzd\Common\EventSourcing\Domain\EventSourcing\AbstractAggregateEvent;
use Dranzd\Common\EventSourcing\Domain\EventSourcing\AggregateEventWithPrivateConstructorTrait;

final class OrderPlaced extends AbstractAggregateEvent
{
    use AggregateEventWithPrivateConstructorTrait;  // ← Makes constructor private
    
    const MESSAGE_NAME = 'order.placed';
    
    private OrderId $orderId;
    private CustomerId $customerId;
    private Money $totalAmount;
}
```

#### Step 2: Create Named Constructors

```php
final class OrderPlaced extends AbstractAggregateEvent
{
    use AggregateEventWithPrivateConstructorTrait;
    
    const MESSAGE_NAME = 'order.placed';
    
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
    
    // Alternative constructor for different scenario
    public static function fromCart(
        OrderId $orderId,
        CustomerId $customerId,
        ShoppingCart $cart
    ): self {
        $instance = new self();
        $instance->orderId = $orderId;
        $instance->customerId = $customerId;
        $instance->totalAmount = $cart->calculateTotal();
        return $instance;
    }
}
```

#### Step 3: Use in Domain Logic

```php
// Expressive, reads like domain language
$event = OrderPlaced::withAmount($orderId, $customerId, $totalAmount);

// Or from a different context
$event = OrderPlaced::fromCart($orderId, $customerId, $cart);
```

### Benefits

✅ **Self-Documenting**: Method names explain intent  
✅ **Type Safety**: Value Objects validated at creation  
✅ **Flexibility**: Multiple ways to create same event  
✅ **Testability**: Easy to create events in tests  
✅ **Immutability**: Properties set once, never changed  

---

## Event Payload Serialization

### The Payload Pattern

Events need to be serialized for storage and deserialized for reconstitution. The library uses a clean pattern with `getPayload()` and `setPayload()`.

### Implementation

```php
final class OrderPlaced extends AbstractAggregateEvent
{
    private OrderId $orderId;
    private CustomerId $customerId;
    private Money $totalAmount;
    
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
    
    // Serialize to array for storage
    public function getPayload(): array
    {
        return [
            'order_id' => $this->orderId->toNative(),
            'customer_id' => $this->customerId->toNative(),
            'total_amount' => $this->totalAmount->toArray(),
        ];
    }
    
    // Deserialize from array when loading
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

### Serialization Flow

```
Creation:
OrderPlaced::withAmount() → Properties set → Event created

Storage:
Event → getPayload() → Array → JSON → Database

Loading:
Database → JSON → Array → setPayload() → Properties restored → Event reconstituted
```

### Best Practices

1. **Use Value Object serialization methods**:
   ```php
   'order_id' => $this->orderId->toNative()  // ✅ Good
   'order_id' => (string) $this->orderId     // ⚠️ Avoid
   ```

2. **Handle missing fields gracefully** (for versioning):
   ```php
   protected function setPayload(array $payload): void
   {
       $this->orderId = OrderId::fromNative($payload['order_id']);
       
       // Handle optional field for backward compatibility
       $this->notes = isset($payload['notes']) 
           ? Notes::fromNative($payload['notes'])
           : Notes::empty();
   }
   ```

3. **Keep payload flat when possible**:
   ```php
   // ✅ Good - flat structure
   return [
       'order_id' => $this->orderId->toNative(),
       'amount' => $this->amount->toNative(),
   ];
   
   // ⚠️ Avoid - deep nesting
   return [
       'order' => [
           'id' => $this->orderId->toNative(),
           'details' => [
               'amount' => $this->amount->toNative(),
           ],
       ],
   ];
   ```

---

## Value Object Integration

### Why Value Objects?

Events should use **Value Objects** for all domain data. This provides:

- **Validation**: Data validated at Value Object construction
- **Type Safety**: Invalid data cannot reach events
- **Immutability**: Value Objects are immutable
- **Reusability**: Same Value Objects used throughout domain
- **Expressiveness**: Domain concepts as first-class objects

### Pattern

```php
final class UserRegistered extends AbstractAggregateEvent
{
    private UserId $userId;
    private EmailAddress $email;
    private Username $username;
    
    public static function withCredentials(
        UserId $userId,
        EmailAddress $email,      // ← Value Object validates email format
        Username $username        // ← Value Object validates username rules
    ): self {
        // No validation needed here - Value Objects guarantee correctness
        $instance = new self();
        $instance->userId = $userId;
        $instance->email = $email;
        $instance->username = $username;
        return $instance;
    }
}
```

### Value Object Requirements

Value Objects used in events should implement:

1. **Static factory method** for creation:
   ```php
   public static function fromNative($value): self
   ```

2. **Serialization method**:
   ```php
   public function toNative()  // For simple values
   public function toArray(): array  // For complex values
   ```

3. **Validation in constructor**:
   ```php
   public function __construct(string $value)
   {
       Assertion::email($value, 'Invalid email address');
       $this->value = $value;
   }
   ```

### Example Value Object

```php
final class EmailAddress
{
    private string $value;
    
    private function __construct(string $value)
    {
        Assertion::email($value, 'Invalid email address');
        $this->value = $value;
    }
    
    public static function fromNative(string $value): self
    {
        return new self($value);
    }
    
    public function toNative(): string
    {
        return $this->value;
    }
    
    public function equals(EmailAddress $other): bool
    {
        return $this->value === $other->value;
    }
}
```

---

## Event Versioning

See [Event Versioning Example](../examples/event-versioning-example.md) for complete documentation.

### Quick Reference

```php
final class OrderPlaced extends AbstractAggregateEvent implements VersionedEvent
{
    use VersionedEventTrait;
    
    const MESSAGE_NAME = 'order.placed';
    const CURRENT_VERSION = 2;  // Increment when schema changes
    
    // ... implementation
}
```

---

## Metadata Usage Patterns

### What is Metadata?

Metadata is additional information about an event that is not part of the domain payload. Common metadata includes:

- **Causation ID**: What caused this event (command/event ID)
- **Correlation ID**: Trace ID for distributed systems
- **Actor ID**: Who/what triggered the event
- **Timestamp**: When the event occurred (automatic)
- **Aggregate Info**: Aggregate root UUID, type, version (automatic for AggregateEvent)

### Using Convenience Methods

```php
$event = OrderPlaced::withAmount($orderId, $customerId, $amount);

// Add causation (this event was caused by a command)
$event = $event->withCausationId($command->getMessageUuid());

// Add correlation (for distributed tracing)
$event = $event->withCorrelationId($traceId);

// Add custom metadata
$event = $event->withAddedMetadata('user_id', $userId);
$event = $event->withAddedMetadata('ip_address', $ipAddress);
```

### Reading Metadata

```php
// Check if metadata exists
if ($event->hasMetadata('user_id')) {
    $userId = $event->metadata('user_id');
}

// Get causation/correlation
$causationId = $event->causationId();  // Returns null if not set
$correlationId = $event->correlationId();

// Get event info
$eventId = $event->eventId();
$occurredAt = $event->occurredAt();
```

### Metadata in Event Handlers

```php
class OrderPlacedHandler
{
    public function handle(OrderPlaced $event): void
    {
        // Use metadata for logging
        $this->logger->info('Order placed', [
            'event_id' => $event->eventId(),
            'occurred_at' => $event->occurredAt()->format('Y-m-d H:i:s'),
            'correlation_id' => $event->correlationId(),
            'order_id' => $event->getOrderId()->toNative(),
        ]);
        
        // Use metadata for authorization
        if ($event->hasMetadata('user_id')) {
            $this->auditLog->record($event->metadata('user_id'), 'order.placed');
        }
    }
}
```

### Metadata Injection

For aggregate events, metadata is often injected automatically:

```php
// In your aggregate root
class Order extends AggregateRoot
{
    public function place(CustomerId $customerId, Money $amount): void
    {
        $event = OrderPlaced::withAmount(
            $this->orderId,
            $customerId,
            $amount
        );
        
        // Metadata automatically injected:
        // - aggregate_root_uuid
        // - aggregate_root_type
        // - aggregate_root_version
        // - aggregate_event_version
        
        $this->recordThat($event);
    }
}
```

---

## Event Naming Conventions

### Message Name Constant

Always define a `MESSAGE_NAME` constant for events:

```php
final class OrderPlaced extends AbstractAggregateEvent
{
    const MESSAGE_NAME = 'order.placed';
    
    // ... implementation
}
```

### Naming Guidelines

#### 1. **Use Past Tense**
Events represent things that have happened:

```php
// ✅ Good - past tense
OrderPlaced
UserRegistered
PaymentProcessed
ItemAddedToCart

// ❌ Bad - present/future tense
PlaceOrder
RegisterUser
ProcessPayment
AddItemToCart
```

#### 2. **Be Specific**
Use specific, descriptive names:

```php
// ✅ Good - specific
OrderCancelled
OrderShipped
OrderDelivered

// ❌ Bad - generic
OrderUpdated
OrderChanged
OrderModified
```

#### 3. **Include Context**
Include the aggregate/context in the name:

```php
// ✅ Good - includes context
OrderPlaced
OrderCancelled
PaymentReceived
PaymentRefunded

// ⚠️ Less clear - missing context
Placed
Cancelled
Received
Refunded
```

#### 4. **Message Name Format**
Use dot notation for message names:

```php
const MESSAGE_NAME = 'bounded-context.aggregate.event-name';

// Examples:
const MESSAGE_NAME = 'order.order.placed';
const MESSAGE_NAME = 'payment.payment.received';
const MESSAGE_NAME = 'inventory.product.stock-updated';
const MESSAGE_NAME = 'booking.event.attachment-added';
```

### Class Organization

```php
namespace App\Domain\Order\Event;

use Dranzd\Common\EventSourcing\Domain\EventSourcing\AbstractAggregateEvent;
use Dranzd\Common\EventSourcing\Domain\EventSourcing\AggregateEventWithPrivateConstructorTrait;

/**
 * Event fired when an order is placed
 * 
 * This event contains all information needed to reconstitute
 * the order placement action.
 */
final class OrderPlaced extends AbstractAggregateEvent
{
    use AggregateEventWithPrivateConstructorTrait;
    
    const MESSAGE_NAME = 'order.order.placed';
    
    // ... implementation
}
```

---

## Complete Example

Here's a complete example combining all patterns:

```php
<?php

namespace App\Domain\Order\Event;

use App\Domain\Order\ValueObject\OrderId;
use App\Domain\Customer\ValueObject\CustomerId;
use App\Domain\Money\ValueObject\Money;
use App\Domain\Order\ValueObject\OrderItems;
use Dranzd\Common\EventSourcing\Domain\EventSourcing\AbstractAggregateEvent;
use Dranzd\Common\EventSourcing\Domain\EventSourcing\AggregateEventWithPrivateConstructorTrait;
use Dranzd\Common\EventSourcing\Domain\Event\VersionedEvent;
use Dranzd\Common\EventSourcing\Domain\Event\VersionedEventTrait;

/**
 * Event fired when an order is placed
 * 
 * Version History:
 * - V1: Initial version with order_id, customer_id, items
 * - V2: Added total_amount field
 */
final class OrderPlaced extends AbstractAggregateEvent implements VersionedEvent
{
    use AggregateEventWithPrivateConstructorTrait;
    use VersionedEventTrait;
    
    const MESSAGE_NAME = 'order.order.placed';
    const CURRENT_VERSION = 2;
    
    private OrderId $orderId;
    private CustomerId $customerId;
    private OrderItems $items;
    private Money $totalAmount;
    
    /**
     * Named constructor - create from order details
     */
    public static function withDetails(
        OrderId $orderId,
        CustomerId $customerId,
        OrderItems $items,
        Money $totalAmount
    ): self {
        $instance = new self();
        $instance->orderId = $orderId;
        $instance->customerId = $customerId;
        $instance->items = $items;
        $instance->totalAmount = $totalAmount;
        return $instance;
    }
    
    public function getOrderId(): OrderId
    {
        return $this->orderId;
    }
    
    public function getCustomerId(): CustomerId
    {
        return $this->customerId;
    }
    
    public function getItems(): OrderItems
    {
        return $this->items;
    }
    
    public function getTotalAmount(): Money
    {
        return $this->totalAmount;
    }
    
    public function getPayload(): array
    {
        return [
            'order_id' => $this->orderId->toNative(),
            'customer_id' => $this->customerId->toNative(),
            'items' => $this->items->toArray(),
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
        $this->items = OrderItems::fromArray($payload['items']);
        
        // Handle V1 events that don't have total_amount
        $this->totalAmount = isset($payload['total_amount'])
            ? Money::fromArray($payload['total_amount'])
            : $this->items->calculateTotal();
    }
}
```

---

## Testing Events

### Unit Tests

```php
class OrderPlacedTest extends TestCase
{
    public function test_creates_event_with_details()
    {
        $orderId = OrderId::generate();
        $customerId = CustomerId::generate();
        $items = OrderItems::fromArray([/* ... */]);
        $totalAmount = Money::fromAmount(100, 'USD');
        
        $event = OrderPlaced::withDetails($orderId, $customerId, $items, $totalAmount);
        
        $this->assertEquals($orderId, $event->getOrderId());
        $this->assertEquals($customerId, $event->getCustomerId());
        $this->assertEquals($items, $event->getItems());
        $this->assertEquals($totalAmount, $event->getTotalAmount());
    }
    
    public function test_serializes_and_deserializes()
    {
        $event = OrderPlaced::withDetails(/* ... */);
        
        $payload = $event->getPayload();
        $array = $event->toArray();
        
        $reconstituted = OrderPlaced::fromArray($array);
        
        $this->assertEquals($event->getOrderId(), $reconstituted->getOrderId());
        $this->assertEquals($event->getPayload(), $reconstituted->getPayload());
    }
    
    public function test_has_correct_version()
    {
        $event = OrderPlaced::withDetails(/* ... */);
        
        $this->assertEquals(2, $event->getEventVersion());
        $this->assertEquals(2, OrderPlaced::getLatestVersion());
    }
}
```

---

## Summary

### Key Patterns

1. ✅ **Named Constructors** - Expressive event creation
2. ✅ **Payload Serialization** - Clean persistence pattern
3. ✅ **Value Objects** - Type safety and validation
4. ✅ **Event Versioning** - Schema evolution support
5. ✅ **Metadata** - Cross-cutting concerns
6. ✅ **Naming Conventions** - Consistent, clear names

### Benefits

- **Type Safety**: Value Objects prevent invalid data
- **Expressiveness**: Code reads like domain language
- **Maintainability**: Clear patterns, easy to understand
- **Testability**: Easy to create and test events
- **Evolvability**: Versioning supports schema changes
- **Traceability**: Metadata enables debugging and auditing

---

## Related Documentation

- [Event Versioning Example](../examples/event-versioning-example.md)
- [Domain Event Design Review](../reviews/domain-event-design-review.md)
- [Domain Event Improvements](../reviews/domain-event-improvements.md)

# Event Versioning Implementation Summary

**Date**: October 31, 2025  
**Status**: ✅ Completed  
**Priority**: Medium

---

## Overview

Implemented a complete event versioning system to support event schema evolution in event sourcing applications. This allows events to evolve over time while maintaining backward compatibility with historical events stored in the event store.

---

## Files Created

### 1. Core Interfaces and Traits

#### `src/Domain/Event/VersionedEvent.php`
Interface for events that support versioning.

```php
interface VersionedEvent extends Event
{
    public function getEventVersion(): int;
    public static function getLatestVersion(): int;
}
```

**Purpose**: Define contract for versioned events

---

#### `src/Domain/Event/VersionedEventTrait.php`
Trait to simplify implementation of versioned events.

**Features**:
- Automatic version detection from metadata
- Reads from `CURRENT_VERSION` constant
- Helper methods: `needsUpcasting()`, `versionDifference()`

**Usage**:
```php
final class MyEvent extends AbstractAggregateEvent implements VersionedEvent
{
    use VersionedEventTrait;
    const CURRENT_VERSION = 2;
}
```

---

### 2. Upcasting System

#### `src/Domain/Event/EventUpcaster.php`
Interface for event upcasters that transform old event versions to new ones.

```php
interface EventUpcaster
{
    public function canUpcast(string $eventName, int $version): bool;
    public function upcast(array $eventData): array;
    public function toVersion(): int;
}
```

**Purpose**: Define contract for version transformations

---

#### `src/Domain/Event/EventUpcasterChain.php`
Chain of upcasters to handle multiple version migrations.

**Features**:
- Apply multiple upcasters in sequence (V1 → V2 → V3)
- Automatic version detection
- Prevents infinite loops (max 100 iterations)
- Check if event needs upcasting

**Usage**:
```php
$chain = new EventUpcasterChain([
    new MyEventV1ToV2Upcaster(),
    new MyEventV2ToV3Upcaster(),
]);

$upcastedEvent = $chain->upcast($oldEventData);
```

---

### 3. Documentation

#### `docs/examples/event-versioning-example.md`
Comprehensive guide with examples covering:
- Creating versioned events
- Evolving event schemas
- Writing upcasters
- Configuring upcaster chains
- Multiple version migrations
- Best practices
- Testing strategies

---

## Key Features

### 1. **Simple Version Declaration**
```php
const CURRENT_VERSION = 2;  // Just increment this constant
```

### 2. **Automatic Version Tracking**
Version stored in `_aggregate_event_version` metadata automatically.

### 3. **Easy Implementation**
```php
final class UserRegistered extends AbstractAggregateEvent implements VersionedEvent
{
    use VersionedEventTrait;  // ← Just add this
    const CURRENT_VERSION = 2;
}
```

### 4. **Helper Methods**
```php
if ($event->needsUpcasting()) {
    echo "Needs " . $event->versionDifference() . " version upgrades";
}
```

### 5. **Flexible Upcasting**
```php
class MyEventV1ToV2Upcaster implements EventUpcaster
{
    public function canUpcast(string $eventName, int $version): bool
    {
        return $eventName === 'my.event' && $version === 1;
    }
    
    public function upcast(array $eventData): array
    {
        // Transform V1 to V2
        $eventData['payload']['new_field'] = 'default_value';
        $eventData['metadata']['_aggregate_event_version'] = 2;
        return $eventData;
    }
    
    public function toVersion(): int
    {
        return 2;
    }
}
```

### 6. **Chain Multiple Versions**
```php
$chain = new EventUpcasterChain([
    new V1ToV2Upcaster(),
    new V2ToV3Upcaster(),
    new V3ToV4Upcaster(),
]);

// Automatically applies: V1 → V2 → V3 → V4
$v4Event = $chain->upcast($v1EventData);
```

---

## Usage Workflow

### Step 1: Mark Event as Versioned
```php
final class OrderPlaced extends AbstractAggregateEvent implements VersionedEvent
{
    use VersionedEventTrait;
    const CURRENT_VERSION = 1;  // Initial version
    
    // ... event implementation
}
```

### Step 2: Evolve the Schema
When you need to change the event structure:

```php
final class OrderPlaced extends AbstractAggregateEvent implements VersionedEvent
{
    use VersionedEventTrait;
    const CURRENT_VERSION = 2;  // ← Increment version
    
    private Money $totalAmount;  // ← New field added
    
    // ... updated implementation
}
```

### Step 3: Create Upcaster
```php
class OrderPlacedV1ToV2Upcaster implements EventUpcaster
{
    public function canUpcast(string $eventName, int $version): bool
    {
        return $eventName === 'order.placed' && $version === 1;
    }
    
    public function upcast(array $eventData): array
    {
        // Calculate total from items for old events
        $items = $eventData['payload']['items'] ?? [];
        $total = array_sum(array_column($items, 'price'));
        
        $eventData['payload']['total_amount'] = $total;
        $eventData['metadata']['_aggregate_event_version'] = 2;
        
        return $eventData;
    }
    
    public function toVersion(): int
    {
        return 2;
    }
}
```

### Step 4: Configure in Event Store
```php
class EventStore
{
    private EventUpcasterChain $upcasterChain;
    
    public function __construct()
    {
        $this->upcasterChain = new EventUpcasterChain([
            new OrderPlacedV1ToV2Upcaster(),
            // Add more upcasters as events evolve
        ]);
    }
    
    public function loadEvents(string $aggregateId): array
    {
        $rawEvents = $this->loadFromDatabase($aggregateId);
        
        $events = [];
        foreach ($rawEvents as $rawEvent) {
            $eventData = json_decode($rawEvent['data'], true);
            
            // Apply upcasting if needed
            $upcastedData = $this->upcasterChain->upcast($eventData);
            
            // Reconstitute event
            $eventClass = $this->resolveEventClass($upcastedData['message_name']);
            $events[] = $eventClass::fromArray($upcastedData);
        }
        
        return $events;
    }
}
```

---

## Benefits

### 1. **Backward Compatibility**
Old events in the event store continue to work without migration.

### 2. **Schema Evolution**
Events can evolve naturally as requirements change.

### 3. **No Data Migration**
Events are upcasted on-the-fly when loaded, no need to rewrite historical data.

### 4. **Audit Trail Preserved**
Original events remain unchanged in storage, maintaining perfect audit trail.

### 5. **Gradual Migration**
Can migrate events lazily as they're accessed, or batch process if needed.

### 6. **Type Safety**
Current code always works with latest event version.

---

## Best Practices

### 1. **Always Increment Version**
```php
const CURRENT_VERSION = 3;  // Increment when schema changes
```

### 2. **Document Changes**
```php
/**
 * OrderPlaced Event
 * 
 * Version History:
 * - V1: Initial version with items array
 * - V2: Added total_amount field
 * - V3: Added customer_id field
 */
```

### 3. **Make Upcasters Idempotent**
```php
public function upcast(array $eventData): array
{
    // Check if already upcasted
    if (isset($eventData['payload']['total_amount'])) {
        return $eventData;
    }
    
    // Apply transformation
    $eventData['payload']['total_amount'] = $this->calculateTotal($eventData);
    return $eventData;
}
```

### 4. **Test Thoroughly**
```php
class OrderPlacedV1ToV2UpcasterTest extends TestCase
{
    public function test_adds_total_amount_from_items()
    {
        $upcaster = new OrderPlacedV1ToV2Upcaster();
        
        $v1Event = [
            'message_name' => 'order.placed',
            'payload' => [
                'items' => [
                    ['price' => 100],
                    ['price' => 200],
                ]
            ],
            'metadata' => ['_aggregate_event_version' => 1],
        ];
        
        $v2Event = $upcaster->upcast($v1Event);
        
        $this->assertEquals(300, $v2Event['payload']['total_amount']);
        $this->assertEquals(2, $v2Event['metadata']['_aggregate_event_version']);
    }
}
```

### 5. **Keep Upcasters Simple**
Each upcaster should handle one version transition only (V1 → V2, not V1 → V3).

---

## Testing Strategy

### Unit Tests for Upcasters
```php
class EventUpcasterTest extends TestCase
{
    public function test_can_upcast_correct_version()
    {
        $upcaster = new MyEventV1ToV2Upcaster();
        
        $this->assertTrue($upcaster->canUpcast('my.event', 1));
        $this->assertFalse($upcaster->canUpcast('my.event', 2));
        $this->assertFalse($upcaster->canUpcast('other.event', 1));
    }
    
    public function test_upcast_transforms_correctly()
    {
        // Test transformation logic
    }
    
    public function test_upcast_is_idempotent()
    {
        // Test running twice produces same result
    }
}
```

### Integration Tests for Chain
```php
class EventUpcasterChainTest extends TestCase
{
    public function test_chain_applies_multiple_upcasters()
    {
        $chain = new EventUpcasterChain([
            new V1ToV2Upcaster(),
            new V2ToV3Upcaster(),
        ]);
        
        $v1Event = $this->createV1Event();
        $v3Event = $chain->upcast($v1Event);
        
        $this->assertEquals(3, $v3Event['metadata']['_aggregate_event_version']);
    }
}
```

---

## Migration Path

### For Existing Events

If you have existing events without versioning:

1. **Add VersionedEvent interface**
2. **Set CURRENT_VERSION = 1** (for existing schema)
3. **Future changes increment to V2, V3, etc.**

```php
// Existing event becomes V1
final class ExistingEvent extends AbstractAggregateEvent implements VersionedEvent
{
    use VersionedEventTrait;
    const CURRENT_VERSION = 1;  // Current schema is now V1
    
    // Existing implementation unchanged
}
```

---

## Summary

The event versioning system provides:

✅ **Interface**: `VersionedEvent` - Define versioned events  
✅ **Trait**: `VersionedEventTrait` - Easy implementation with helpers  
✅ **Upcaster**: `EventUpcaster` - Transform old event versions  
✅ **Chain**: `EventUpcasterChain` - Handle multiple version migrations  
✅ **Documentation**: Complete examples and best practices  
✅ **Testing**: Strategies for testing upcasters

This enables **safe event schema evolution** while maintaining **backward compatibility** with historical events, which is essential for long-term event sourcing applications.

---

## Related Files

- ✅ `src/Domain/Event/VersionedEvent.php`
- ✅ `src/Domain/Event/VersionedEventTrait.php`
- ✅ `src/Domain/Event/EventUpcaster.php`
- ✅ `src/Domain/Event/EventUpcasterChain.php`
- ✅ `docs/examples/event-versioning-example.md`
- 📝 `docs/reviews/domain-event-design-review.md`
- 📝 `docs/reviews/domain-event-improvements.md`

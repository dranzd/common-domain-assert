# Event Versioning Example

This document demonstrates how to use the event versioning system for schema evolution in event sourcing.

---

## Basic Versioned Event

### Step 1: Create a Versioned Event

```php
<?php

namespace App\Domain\Event;

use Dranzd\Common\EventSourcing\Domain\EventSourcing\AbstractAggregateEvent;
use Dranzd\Common\EventSourcing\Domain\Event\VersionedEvent;
use Dranzd\Common\EventSourcing\Domain\Event\VersionedEventTrait;

final class UserRegistered extends AbstractAggregateEvent implements VersionedEvent
{
    use VersionedEventTrait;
    
    const MESSAGE_NAME = 'app.user.registered';
    const CURRENT_VERSION = 1;  // Initial version
    
    private string $userId;
    private string $email;
    
    public static function withEmail(string $userId, string $email): self
    {
        $instance = new self();
        $instance->userId = $userId;
        $instance->email = $email;
        return $instance;
    }
    
    public function getUserId(): string
    {
        return $this->userId;
    }
    
    public function getEmail(): string
    {
        return $this->email;
    }
    
    public function getPayload(): array
    {
        return [
            'user_id' => $this->userId,
            'email' => $this->email,
        ];
    }
    
    protected function setPayload(array $payload): void
    {
        if (empty($payload)) {
            return;
        }
        
        $this->userId = $payload['user_id'];
        $this->email = $payload['email'];
    }
}
```

---

## Evolving the Event Schema

### Step 2: Add New Fields (Version 2)

Let's say we want to add a `username` field to the event:

```php
<?php

namespace App\Domain\Event;

use Dranzd\Common\EventSourcing\Domain\EventSourcing\AbstractAggregateEvent;
use Dranzd\Common\EventSourcing\Domain\Event\VersionedEvent;
use Dranzd\Common\EventSourcing\Domain\Event\VersionedEventTrait;

final class UserRegistered extends AbstractAggregateEvent implements VersionedEvent
{
    use VersionedEventTrait;
    
    const MESSAGE_NAME = 'app.user.registered';
    const CURRENT_VERSION = 2;  // ← Incremented version
    
    private string $userId;
    private string $email;
    private string $username;  // ← New field
    
    public static function withEmailAndUsername(
        string $userId, 
        string $email,
        string $username  // ← New parameter
    ): self {
        $instance = new self();
        $instance->userId = $userId;
        $instance->email = $email;
        $instance->username = $username;
        return $instance;
    }
    
    public function getUserId(): string
    {
        return $this->userId;
    }
    
    public function getEmail(): string
    {
        return $this->email;
    }
    
    public function getUsername(): string
    {
        return $this->username;
    }
    
    public function getPayload(): array
    {
        return [
            'user_id' => $this->userId,
            'email' => $this->email,
            'username' => $this->username,  // ← New field in payload
        ];
    }
    
    protected function setPayload(array $payload): void
    {
        if (empty($payload)) {
            return;
        }
        
        $this->userId = $payload['user_id'];
        $this->email = $payload['email'];
        
        // Handle old events that don't have username
        $this->username = $payload['username'] ?? $this->deriveUsernameFromEmail();
    }
    
    private function deriveUsernameFromEmail(): string
    {
        // Fallback for V1 events
        return explode('@', $this->email)[0];
    }
}
```

---

## Creating an Upcaster

### Step 3: Create Upcaster for V1 → V2

```php
<?php

namespace App\Infrastructure\EventStore\Upcaster;

use Dranzd\Common\EventSourcing\Domain\Event\EventUpcaster;

class UserRegisteredV1ToV2Upcaster implements EventUpcaster
{
    public function canUpcast(string $eventName, int $version): bool
    {
        return $eventName === 'app.user.registered' && $version === 1;
    }
    
    public function upcast(array $eventData): array
    {
        // Extract email to derive username
        $email = $eventData['payload']['email'] ?? '';
        $username = explode('@', $email)[0];
        
        // Add username to payload
        $eventData['payload']['username'] = $username;
        
        // Update version in metadata
        $eventData['metadata']['_aggregate_event_version'] = 2;
        
        return $eventData;
    }
    
    public function toVersion(): int
    {
        return 2;
    }
}
```

---

## Using the Upcaster Chain

### Step 4: Configure Upcaster Chain

```php
<?php

namespace App\Infrastructure\EventStore;

use Dranzd\Common\EventSourcing\Domain\Event\EventUpcasterChain;
use App\Infrastructure\EventStore\Upcaster\UserRegisteredV1ToV2Upcaster;

class EventStoreWithUpcasting
{
    private EventUpcasterChain $upcasterChain;
    
    public function __construct()
    {
        $this->upcasterChain = new EventUpcasterChain([
            new UserRegisteredV1ToV2Upcaster(),
            // Add more upcasters as events evolve
        ]);
    }
    
    public function loadEvents(string $aggregateId): array
    {
        // Load raw event data from storage
        $rawEvents = $this->loadRawEventsFromDatabase($aggregateId);
        
        // Upcast events if needed
        $upcastedEvents = [];
        foreach ($rawEvents as $rawEvent) {
            $eventData = json_decode($rawEvent['data'], true);
            
            // Apply upcasting
            $upcastedData = $this->upcasterChain->upcast($eventData);
            
            // Reconstitute event object
            $eventClass = $this->getEventClass($upcastedData['message_name']);
            $upcastedEvents[] = $eventClass::fromArray($upcastedData);
        }
        
        return $upcastedEvents;
    }
    
    private function loadRawEventsFromDatabase(string $aggregateId): array
    {
        // Your database loading logic
        return [];
    }
    
    private function getEventClass(string $messageName): string
    {
        // Map message name to event class
        return UserRegistered::class;
    }
}
```

---

## Multiple Version Migrations

### Example: V1 → V2 → V3

```php
// V1: Original event with email only
// V2: Added username field
// V3: Added registration_source field

const CURRENT_VERSION = 3;

// Upcaster V1 → V2
class UserRegisteredV1ToV2Upcaster implements EventUpcaster
{
    public function canUpcast(string $eventName, int $version): bool
    {
        return $eventName === 'app.user.registered' && $version === 1;
    }
    
    public function upcast(array $eventData): array
    {
        $email = $eventData['payload']['email'] ?? '';
        $eventData['payload']['username'] = explode('@', $email)[0];
        $eventData['metadata']['_aggregate_event_version'] = 2;
        return $eventData;
    }
    
    public function toVersion(): int
    {
        return 2;
    }
}

// Upcaster V2 → V3
class UserRegisteredV2ToV3Upcaster implements EventUpcaster
{
    public function canUpcast(string $eventName, int $version): bool
    {
        return $eventName === 'app.user.registered' && $version === 2;
    }
    
    public function upcast(array $eventData): array
    {
        // Add default registration source for old events
        $eventData['payload']['registration_source'] = 'legacy';
        $eventData['metadata']['_aggregate_event_version'] = 3;
        return $eventData;
    }
    
    public function toVersion(): int
    {
        return 3;
    }
}

// Chain will automatically apply: V1 → V2 → V3
$chain = new EventUpcasterChain([
    new UserRegisteredV1ToV2Upcaster(),
    new UserRegisteredV2ToV3Upcaster(),
]);

$upcastedEvent = $chain->upcast($v1EventData);
// Result: V3 event with all fields
```

---

## Checking Event Versions

```php
$event = UserRegistered::withEmailAndUsername($userId, $email, $username);

// Check current version
echo $event->getEventVersion();  // 2
echo UserRegistered::getLatestVersion();  // 2

// Check if needs upcasting (using trait helper)
if ($event->needsUpcasting()) {
    echo "Event needs upcasting by " . $event->versionDifference() . " versions";
}
```

---

## Best Practices

### 1. **Always Increment CURRENT_VERSION**
```php
const CURRENT_VERSION = 2;  // Increment when schema changes
```

### 2. **Store Version in Metadata**
The version is automatically stored in `_aggregate_event_version` metadata when using `VersionedEventTrait`.

### 3. **Make Upcasters Idempotent**
Upcasters should be safe to run multiple times:
```php
public function upcast(array $eventData): array
{
    // Check if already upcasted
    if (isset($eventData['payload']['username'])) {
        return $eventData;
    }
    
    // Apply transformation
    $eventData['payload']['username'] = $this->deriveUsername($eventData);
    return $eventData;
}
```

### 4. **Test Upcasters Thoroughly**
```php
class UserRegisteredV1ToV2UpcasterTest extends TestCase
{
    public function test_upcasts_v1_to_v2()
    {
        $upcaster = new UserRegisteredV1ToV2Upcaster();
        
        $v1Event = [
            'message_name' => 'app.user.registered',
            'payload' => ['user_id' => '123', 'email' => 'john@example.com'],
            'metadata' => ['_aggregate_event_version' => 1],
        ];
        
        $v2Event = $upcaster->upcast($v1Event);
        
        $this->assertEquals('john', $v2Event['payload']['username']);
        $this->assertEquals(2, $v2Event['metadata']['_aggregate_event_version']);
    }
}
```

### 5. **Document Schema Changes**
Keep a changelog of event schema versions:
```php
/**
 * UserRegistered Event
 * 
 * Version History:
 * - V1: Initial version with user_id and email
 * - V2: Added username field (derived from email for old events)
 * - V3: Added registration_source field (defaults to 'legacy')
 */
```

---

## Summary

The event versioning system provides:

✅ **Interface**: `VersionedEvent` - Define versioned events  
✅ **Trait**: `VersionedEventTrait` - Easy implementation  
✅ **Upcaster**: `EventUpcaster` - Transform old events  
✅ **Chain**: `EventUpcasterChain` - Handle multiple versions  
✅ **Helpers**: `needsUpcasting()`, `versionDifference()` - Utility methods

This allows you to evolve your event schemas over time while maintaining backward compatibility with historical events.

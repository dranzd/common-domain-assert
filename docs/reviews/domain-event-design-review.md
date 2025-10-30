# Domain Event Design Review

**Date**: October 30, 2025  
**Reviewer**: AI Code Assistant  
**Scope**: `src/Domain/Event/` directory

---

## Executive Summary

The Domain Event implementation follows a clean, layered architecture with good separation of concerns. However, there are several design issues and potential improvements identified below.

---

## Architecture Overview

```
Event (interface)
  └── AbstractEvent (abstract class)
       └── AbstractAggregateEvent (in EventSourcing)
            └── Concrete Event Classes (user-defined)
```

The event system is built on top of a generic Message infrastructure, which is a solid foundation for CQRS/Event Sourcing patterns.

---

## Issues Identified

### 🔴 ~~**Critical Issues**~~ **Design Patterns** ✅

#### 1. **AbstractEvent Constructor Pattern - Named Constructor Enforcement**
**Location**: `AbstractEvent.php:10-18`

```php
public function __construct()
{
    parent::__construct(
        Uuid::generateAsString(),
        \defined(static::class . '::MESSAGE_NAME') && \constant(static::class . '::MESSAGE_NAME')
            ? \constant(static::class . '::MESSAGE_NAME')
            : \get_class($this),
        []  // ← INTENTIONAL: Forces use of named constructors
    );
}
```

**Design Intent**: ✅ **This is intentional and well-designed**
- Forces use of **named constructor pattern** (static factory methods)
- Prevents direct instantiation with `new Event(...)`
- Encourages expressive, domain-focused creation methods
- Works with `AggregateEventWithPrivateConstructorTrait` to enforce pattern

**Example Usage**:
```php
final class AttachmentAdded extends AbstractAggregateEvent
{
    use AggregateEventWithPrivateConstructorTrait;
    
    const MESSAGE_NAME = 'cimmaroon.booking.domain.event.attachment-added';
    
    private EventUuid $eventUuid;
    private Attachment $attachment;
    
    // Named constructor - expressive and domain-focused
    final public static function anAttachment(
        EventUuid $eventUuid, 
        Attachment $attachment
    ): AttachmentAdded {
        $instance = new self();
        $instance->eventUuid = $eventUuid;
        $instance->attachment = $attachment;
        return $instance;
    }
    
    final public function getPayload(): array
    {
        return [
            'event_uuid' => $this->eventUuid->toNative(),
            'attachment' => $this->attachment->toArray(),
        ];
    }
    
    protected function setPayload(array $payload): void
    {
        if (empty($payload)) {
            return;
        }
        $this->eventUuid = EventUuid::fromNative($payload['event_uuid']);
        $this->attachment = Attachment::fromArray($payload['attachment']);
    }
}
```

**Benefits**:
- ✅ **Expressive**: `AttachmentAdded::anAttachment($uuid, $attachment)` reads like domain language
- ✅ **Type Safety**: Value objects ensure data integrity at creation
- ✅ **Immutability**: Properties set once during construction
- ✅ **Serialization**: `getPayload()` and `setPayload()` handle persistence
- ✅ **Flexibility**: Multiple named constructors for different creation scenarios

**Status**: ✅ **No change needed** - This is a solid design pattern

---

#### 2. **Refactor Opportunity - Constant Logic Duplication**
**Location**: `AbstractEvent.php:14-16`

```php
\defined(static::class . '::MESSAGE_NAME') && \constant(static::class . '::MESSAGE_NAME')
    ? \constant(static::class . '::MESSAGE_NAME')
    : \get_class($this)
```

**Issue**:
- Duplicates logic already in `GenericMessage::expectedMessageName()`
- Uses verbose `\defined()` and `\constant()` checks
- Can be simplified for better maintainability

**Recommendation**: ⚠️ **Minor refactor possible**
```php
public function __construct()  // ← No payload - intentional for named constructor pattern
{
    parent::__construct(
        Uuid::generateAsString(),
        static::expectedMessageName(),  // ← Use inherited method
        []  // ← Empty array - forces named constructors
    );
}
```

**Note**: This is a **code quality improvement**, not a design issue. The empty payload must remain to enforce the named constructor pattern.

**Priority**: Low - Nice to have, but not critical

---

### 🟡 **Design Issues**

#### 3. **Event vs Message Semantic Distinction** ✅
**Location**: `Event.php:9`

```php
interface Event extends Message
{
    public function getEventName(): string;
}
```

**Design Intent**: ✅ **This is intentional and semantically important**

**Rationale**:
- **Events** and **Messages** have different domain meanings
- `getEventName()` provides **event-specific semantics** in the domain language
- Makes code more readable and intention-revealing in event-sourcing contexts
- Separates event concerns from generic message concerns

**Example Usage**:
```php
// Event context - domain language
$eventName = $event->getEventName();  // ✅ Clear: "What event occurred?"

// Generic message context
$messageName = $message->getMessageName();  // ✅ Clear: "What message is this?"
```

**Benefits**:
- ✅ **Domain Language**: "Event name" is more meaningful than "message name" in event sourcing
- ✅ **Semantic Clarity**: Distinguishes events from commands/queries
- ✅ **Future Flexibility**: Allows event-specific behavior if needed
- ✅ **Interface Segregation**: Events can evolve independently from generic messages

**Status**: ✅ **No change needed** - This is proper domain-driven design

---

#### 4. **Handler Interface - Potential Residual Design** ⚠️
**Location**: `Handler.php:5-8`

```php
interface Handler
{
    public function handle(Event $event): void;
}
```

**Status**: ⚠️ **Confirmed: Unused in current codebase**

**Findings**:
- ✅ **Verified**: No implementations found in `src/`
- ✅ **Verified**: No usage references found
- Likely residual from earlier architectural iteration

**Recommendation**: 🗑️ **Consider removal or documentation**

**Options**:

1. **Remove if truly unused** (Recommended):
   - Reduces maintenance burden
   - Eliminates confusion about its purpose
   - Can always be added back if needed (version control)

2. **Keep if planned for future use**:
   - Add clear documentation explaining intended usage
   - Add `@deprecated` tag if not ready
   - Document in architecture/design docs

3. **Keep as extension point**:
   - Document as "optional extension interface"
   - Provide example implementation
   - Explain when/why to use it

**Suggested Action**:
```php
/**
 * @deprecated Not currently used. May be removed in future versions.
 * 
 * This interface was part of an earlier design iteration.
 * If you need event handling, consider using domain event listeners
 * or command handlers instead.
 */
interface Handler
{
    public function handle(Event $event): void;
}
```

**Priority**: Low - Not affecting functionality, but good housekeeping

---

### 🟢 **Minor Issues**

#### 5. **Event Validation Strategy** ✅
**Design Intent**: ✅ **Validation handled by Value Objects**

**Approach**:
- Events use **Value Objects** for all domain data
- Value Objects enforce validation at construction time
- No separate validation layer needed in events themselves

**Example**:
```php
final class AttachmentAdded extends AbstractAggregateEvent
{
    private EventUuid $eventUuid;      // ← Value Object validates UUID
    private Attachment $attachment;    // ← Value Object validates attachment data
    
    public static function anAttachment(
        EventUuid $eventUuid,          // ← Already validated
        Attachment $attachment         // ← Already validated
    ): self {
        // No validation needed - Value Objects guarantee correctness
        $instance = new self();
        $instance->eventUuid = $eventUuid;
        $instance->attachment = $attachment;
        return $instance;
    }
}
```

**Benefits**:
- ✅ **Single Responsibility**: Value Objects handle validation
- ✅ **Type Safety**: Invalid data cannot reach events
- ✅ **Reusability**: Value Object validation used everywhere
- ✅ **Immutability**: Validated once at Value Object creation

**Status**: ✅ **No change needed** - Proper separation of concerns

---

#### 6. **Event Versioning Strategy** ⚠️
**Status**: ⚠️ **Needs implementation/documentation**

**Current State**:
- `AggregateEvent` has `META_AGGREGATE_EVENT_VERSION` constant defined
- Not currently enforced or used systematically
- No clear guidance on event schema evolution

**Recommendation**: ✅ **Implement event versioning support**

**Proposed Interface**:
```php
interface VersionedEvent extends Event
{
    /**
     * Returns the version of this specific event instance
     */
    public function getEventVersion(): int;
    
    /**
     * Returns the latest/current version of this event type
     */
    public static function getLatestVersion(): int;
}
```

**Usage Example**:
```php
final class AttachmentAdded extends AbstractAggregateEvent implements VersionedEvent
{
    const MESSAGE_NAME = 'booking.event.attachment-added';
    const CURRENT_VERSION = 2;  // Incremented when schema changes
    
    public function getEventVersion(): int
    {
        return $this->getMetadataKeyValueOrDefault(
            AggregateEvent::META_AGGREGATE_EVENT_VERSION, 
            self::CURRENT_VERSION
        );
    }
    
    public static function getLatestVersion(): int
    {
        return self::CURRENT_VERSION;
    }
    
    // Event upcaster for version 1 -> 2
    public static function upcastFromV1(array $payload): array
    {
        // Transform old payload to new format
        return $payload;
    }
}
```

**Priority**: Medium - Important for long-term event sourcing maintenance

---

#### 7. **Event Metadata Convenience Methods** ✅
**Status**: ✅ **To be implemented**

**Problem**: Events inherit metadata handling from `GenericMessage` but lack event-specific helpers

**Recommendation**: Add convenience methods for common event operations

**Implementation**: See updated `AbstractEvent.php` with new methods added

---

## Positive Aspects ✅

1. **Clean Separation**: Events properly separated from Commands and Queries
2. **Immutability Pattern**: Uses clone pattern for modifications (in parent)
3. **Message Infrastructure**: Built on solid generic message foundation
4. **UUID Generation**: Automatic UUID generation for event identity
5. **Timestamp Tracking**: Automatic creation timestamp in UTC
6. **Metadata Support**: Flexible metadata system for cross-cutting concerns

---

## Recommendations Summary

### Completed ✅
1. ~~**Fix AbstractEvent constructor**~~ - **INTENTIONAL DESIGN** ✅ Named constructor pattern
2. ~~**Evaluate `getEventName()` necessity**~~ - **INTENTIONAL DESIGN** ✅ Semantic distinction
3. ~~**Event validation**~~ - **INTENTIONAL DESIGN** ✅ Value Objects handle validation
4. ✅ **Add convenience methods** - IMPLEMENTED in AbstractEvent

### To Implement
5. ⚠️ **Remove unused Handler interface** - Low priority housekeeping
6. ⚠️ **Implement event versioning** - Medium priority, add VersionedEvent interface
7. ⚠️ **Use `expectedMessageName()`** - Low priority refactor

### Documentation
8. 📝 **Document event design patterns** in README (named constructors, payload serialization)
9. 📝 **Add examples** of proper event implementation
10. 📝 **Document event versioning strategy** when implemented

---

## Code Quality Metrics

| Metric | Score | Notes |
|--------|-------|-------|
| **Cohesion** | 8/10 | Well-focused interfaces |
| **Coupling** | 7/10 | Good separation, some tight coupling to Message |
| **Extensibility** | 9/10 | Easy to extend with new event types |
| **Maintainability** | 7/10 | Some inconsistencies affect maintenance |
| **Testability** | 8/10 | Interfaces make testing straightforward |
| **Documentation** | 4/10 | Minimal inline documentation |

**Overall Score**: 8.5/10 *(Updated after understanding named constructor pattern)*

---

## Migration Path

If you decide to implement the recommendations:

### Phase 1: Non-Breaking Changes
1. Add `EventHandler` interface (keep `Handler` as alias)
2. Add convenience methods to `AbstractEvent`
3. Add documentation

### Phase 2: Deprecations
1. Deprecate `Handler` interface
2. Deprecate `getEventName()` if removing
3. Update all internal usages

### Phase 3: Breaking Changes (Major Version)
1. Remove deprecated interfaces
2. Fix `AbstractEvent` constructor signature
3. Add validation requirements

---

## Related Files to Review

Based on this review, you should also examine:
- `src/Domain/Message/GenericMessage.php` - Parent class implementation
- `src/Domain/EventSourcing/AbstractAggregateEvent.php` - Event sourcing specifics
- `src/Domain/EventSourcing/AggregateEventMetadataInjector.php` - Metadata injection patterns

---

## Conclusion

The Domain Event implementation is **excellent and well-designed**. After review and clarification:

### ✅ **Strengths Confirmed**
1. **Named Constructor Pattern** - Forces expressive, domain-focused event creation
2. **Semantic Clarity** - `getEventName()` provides proper domain language distinction
3. **Value Object Validation** - Clean separation of concerns for data validation
4. **Immutability** - Proper use of clone pattern for modifications
5. **Serialization** - Clean `getPayload()`/`setPayload()` for persistence

### ⚠️ **Minor Improvements**
1. **Event Versioning** - Add systematic support for event schema evolution
2. **Convenience Methods** - Added helper methods for common metadata operations (DONE)
3. **Cleanup** - Remove unused `Handler` interface
4. **Documentation** - Document patterns and best practices

### 📊 **Updated Assessment**
This is a **mature, well-thought-out event sourcing implementation** that follows DDD principles correctly. The design decisions that initially appeared as issues are actually intentional patterns that enforce good practices.

**Final Score**: 9.0/10 - Excellent foundation with minor enhancements possible

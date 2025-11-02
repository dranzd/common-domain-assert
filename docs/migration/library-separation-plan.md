# Library Separation Plan

## Overview

This document outlines the plan to separate the monolithic `dranzd/common-event-sourcing` library into focused, reusable components. This separation will improve modularity, reusability, and maintainability while following proper dependency management principles.

## Motivation

The current library combines several distinct concerns:
- **Messaging Infrastructure** - Generic message abstractions (Command, Query, Event)
- **Event Sourcing** - Aggregate roots, event replay, event versioning
- **Value Objects** - Domain value object base classes
- **Assertions** - Domain assertion utilities
- **CQRS Support** - Command/Query metadata and infrastructure

These concerns should be separated into independent libraries that can be composed as needed.

## Target Library Structure

```
dranzd/common-messaging
├── Message primitives (Command, Query, Event interfaces)
├── Metadata capabilities (Causation, Correlation, Actor)
└── Generic message implementation

dranzd/common-valueobject
├── ValueObject base classes
├── Common value object utilities
└── Value object interfaces

dranzd/common-assert
├── Domain assertion utilities
└── Custom assertion helpers

dranzd/common-event-sourcing (refactored)
├── Depends on: common-messaging, common-valueobject, common-assert
├── Event versioning and upcasting
├── Aggregate roots and event sourcing patterns
└── Event-specific abstractions

dranzd/common-cqrs (new)
├── Depends on: common-messaging
├── CommandBus, QueryBus, EventBus
├── Handler infrastructure
└── Middleware support
```

## Dependency Graph

```
common-assert (no dependencies)
    ↑
common-valueobject (depends on: common-assert)
    ↑
common-messaging (depends on: common-valueobject, common-assert)
    ↑
    ├── common-event-sourcing (depends on: messaging, valueobject, assert)
    └── common-cqrs (depends on: messaging)
```

## Execution Plan

### ✅ Step 1: Analyze Dependencies
**Status:** Pending  
**Estimated Time:** 2-3 hours

**Tasks:**
- [ ] Map all current dependencies between components
- [ ] Identify circular dependencies (if any)
- [ ] Create detailed dependency graph
- [ ] Document which files belong to which library
- [ ] Identify shared utilities and decide their placement

**Deliverables:**
- `docs/migration/dependency-analysis.md`
- File-to-library mapping spreadsheet/document

---

### ✅ Step 2: Create dranzd/common-messaging Library
**Status:** Pending  
**Estimated Time:** 4-6 hours

**Tasks:**
- [ ] Create new repository: `dranzd/common-messaging`
- [ ] Set up composer.json with proper namespace
- [ ] Extract Message interface and implementations
  - `Message.php`
  - `GenericMessage.php`
  - `Command.php`
  - `Query.php`
  - `Event.php` (basic interface)
- [ ] Extract metadata traits
  - `CausationCapable.php` + `CausationCapableTrait.php`
  - `CorrelationCapable.php` + `CorrelationCapableTrait.php`
  - `ActorCapable.php` + `ActorCapableTrait.php`
  - `PayloadContainerCapableTrait.php`
- [ ] Set up PHPUnit, PHPStan, PHPCS
- [ ] Add MIT license
- [ ] Create initial README.md

**Files to Extract:**
```
src/Domain/Message/
├── Message.php
├── GenericMessage.php
├── Command.php
├── Query.php
├── Event.php
├── CausationCapable.php
├── CausationCapableTrait.php
├── CorrelationCapable.php
├── CorrelationCapableTrait.php
├── ActorCapable.php
├── ActorCapableTrait.php
└── PayloadContainerCapableTrait.php
```

**Deliverables:**
- Working `dranzd/common-messaging` library
- Published to Packagist (or private repository)

---

### ✅ Step 3: Create dranzd/common-valueobject Library
**Status:** Pending  
**Estimated Time:** 3-5 hours

**Tasks:**
- [ ] Create new repository: `dranzd/common-valueobject`
- [ ] Set up composer.json with proper namespace
- [ ] Extract ValueObject base classes and interfaces
- [ ] Add dependency on `dranzd/common-assert` (if needed)
- [ ] Set up PHPUnit, PHPStan, PHPCS
- [ ] Add MIT license
- [ ] Create initial README.md

**Files to Extract:**
```
src/Domain/ValueObject/
└── [All 30 value object files]
```

**Deliverables:**
- Working `dranzd/common-valueobject` library
- Published to Packagist (or private repository)

---

### ✅ Step 4: Create dranzd/common-assert Library
**Status:** Pending  
**Estimated Time:** 2-3 hours

**Tasks:**
- [ ] Create new repository: `dranzd/common-assert`
- [ ] Set up composer.json with proper namespace
- [ ] Extract assertion utilities
- [ ] Decide if wrapping `webmozart/assert` or creating custom
- [ ] Set up PHPUnit, PHPStan, PHPCS
- [ ] Add MIT license
- [ ] Create initial README.md

**Files to Extract:**
```
src/Domain/Assert/
└── [All assertion files]
```

**Deliverables:**
- Working `dranzd/common-assert` library
- Published to Packagist (or private repository)

---

### ✅ Step 5: Update dranzd/common-event-sourcing Dependencies
**Status:** Pending  
**Estimated Time:** 3-4 hours

**Tasks:**
- [ ] Update composer.json to require new libraries
  ```json
  "require": {
      "dranzd/common-messaging": "^1.0",
      "dranzd/common-valueobject": "^1.0",
      "dranzd/common-assert": "^1.0"
  }
  ```
- [ ] Remove extracted files from src/
- [ ] Update all import statements to use new namespaces
- [ ] Update tests to use new namespaces
- [ ] Run full test suite to verify nothing broke
- [ ] Update README.md with new dependencies

**Files Remaining in Event Sourcing:**
```
src/Domain/Event/
├── AbstractEvent.php
├── Event.php
├── EventUpcaster.php
├── EventUpcasterChain.php
├── VersionedEvent.php
└── VersionedEventTrait.php

src/Domain/EventSourcing/
├── AbstractAggregateEvent.php
├── AggregateEvent.php
├── AggregateRoot.php
├── AggregateRootTrait.php
├── AggregateRootRepository.php
├── Metadata injectors (all)
└── Visitor patterns (all)

src/Util/
└── [Utility files]
```

**Deliverables:**
- Updated `dranzd/common-event-sourcing` with external dependencies
- All tests passing
- Updated documentation

---

### ✅ Step 6: Create dranzd/common-cqrs Library
**Status:** Pending  
**Estimated Time:** 6-8 hours

**Tasks:**
- [ ] Create new repository: `dranzd/common-cqrs`
- [ ] Set up composer.json with dependency on `dranzd/common-messaging`
- [ ] Implement CommandBus interface and implementation
- [ ] Implement QueryBus interface and implementation
- [ ] Implement EventBus interface and implementation
- [ ] Create Handler interfaces
  - `CommandHandler`
  - `QueryHandler`
  - `EventHandler`
- [ ] Implement middleware support
- [ ] Add handler registration/discovery
- [ ] Set up PHPUnit, PHPStan, PHPCS
- [ ] Add MIT license
- [ ] Create comprehensive README.md

**Proposed Structure:**
```
src/
├── CommandBus/
│   ├── CommandBus.php
│   ├── SimpleCommandBus.php
│   └── CommandHandler.php
├── QueryBus/
│   ├── QueryBus.php
│   ├── SimpleQueryBus.php
│   └── QueryHandler.php
├── EventBus/
│   ├── EventBus.php
│   ├── SimpleEventBus.php
│   └── EventHandler.php
├── Middleware/
│   ├── Middleware.php
│   └── [Common middleware implementations]
└── HandlerRegistry/
    └── [Handler registration utilities]
```

**Deliverables:**
- Working `dranzd/common-cqrs` library
- Published to Packagist (or private repository)

---

### ✅ Step 7: Migrate Tests for Each Library
**Status:** Pending  
**Estimated Time:** 4-6 hours

**Tasks:**
- [ ] Migrate messaging tests to `dranzd/common-messaging`
- [ ] Migrate value object tests to `dranzd/common-valueobject`
- [ ] Migrate assertion tests to `dranzd/common-assert`
- [ ] Update event sourcing tests for new dependencies
- [ ] Create comprehensive CQRS tests
- [ ] Ensure each library has 100% coverage for critical paths
- [ ] Run PHPStan on all libraries

**Test Files to Migrate:**
```
common-messaging:
├── tests/Domain/Message/GenericMessageTest.php
└── tests/Domain/Message/CausationAndCorrelationCapableTest.php

common-event-sourcing (remaining):
├── tests/Domain/Event/AbstractEventTest.php
├── tests/Domain/Event/EventUpcasterChainTest.php
├── tests/Domain/Event/VersionedEventTraitTest.php
├── tests/Domain/EventSourcing/AbstractAggregateEventTest.php
├── tests/Domain/EventSourcing/AggregateRootTraitTest.php
└── tests/Domain/EventSourcing/MetadataInjectorsTest.php
```

**Deliverables:**
- All libraries have comprehensive test coverage
- All tests passing
- PHPStan level 8+ passing

---

### ✅ Step 8: Update composer.json and Autoload Paths
**Status:** Pending  
**Estimated Time:** 2-3 hours

**Tasks:**
- [ ] Verify composer.json for each library
- [ ] Ensure proper PSR-4 autoloading
- [ ] Set correct minimum PHP version (8.3+)
- [ ] Add proper version constraints for dependencies
- [ ] Configure scripts (test, phpstan, cs-check, cs-fix)
- [ ] Add keywords for Packagist discoverability
- [ ] Set up GitHub Actions CI/CD for each repository

**Composer Configuration Template:**
```json
{
    "name": "dranzd/common-[library]",
    "description": "[Description]",
    "type": "library",
    "license": "MIT",
    "minimum-stability": "stable",
    "require": {
        "php": "^8.3"
    },
    "require-dev": {
        "phpunit/phpunit": "^10.0",
        "phpstan/phpstan": "^1.10",
        "squizlabs/php_codesniffer": "^3.7"
    },
    "autoload": {
        "psr-4": {
            "Dranzd\\Common\\[Namespace]\\": "src/"
        }
    }
}
```

**Deliverables:**
- All composer.json files properly configured
- CI/CD pipelines running

---

### ✅ Step 9: Create Documentation for Each Library
**Status:** Pending  
**Estimated Time:** 6-8 hours

**Tasks:**
- [ ] Create README.md for `dranzd/common-messaging`
  - Installation instructions
  - Basic usage examples
  - Metadata capabilities guide
  - API reference
- [ ] Create README.md for `dranzd/common-valueobject`
  - Installation instructions
  - Creating custom value objects
  - Built-in value objects
  - Validation patterns
- [ ] Create README.md for `dranzd/common-assert`
  - Installation instructions
  - Available assertions
  - Custom assertion examples
- [ ] Create README.md for `dranzd/common-cqrs`
  - Installation instructions
  - CommandBus usage
  - QueryBus usage
  - EventBus usage
  - Middleware examples
  - Handler registration
- [ ] Update `dranzd/common-event-sourcing` README
  - Update dependencies section
  - Add migration guide
  - Update examples to use new libraries
- [ ] Create migration guide for existing users
- [ ] Add CHANGELOG.md for each library

**Deliverables:**
- Comprehensive documentation for all libraries
- Migration guide for existing users
- Code examples and tutorials

---

### ✅ Step 10: Verification and Integration Testing
**Status:** Pending  
**Estimated Time:** 3-4 hours

**Tasks:**
- [ ] Create integration test project using all libraries
- [ ] Verify each library works independently
- [ ] Test library composition (event-sourcing + cqrs)
- [ ] Run full test suite across all libraries
- [ ] Check for any circular dependencies
- [ ] Verify Packagist listings (if public)
- [ ] Test installation in fresh project
- [ ] Performance benchmarking (optional)
- [ ] Security audit (optional)

**Integration Test Scenarios:**
```php
// Scenario 1: Using messaging alone
$command = new CreateOrderCommand(...);

// Scenario 2: Using CQRS with messaging
$commandBus->dispatch($command);

// Scenario 3: Using event sourcing with messaging
$aggregate->recordThat($event);

// Scenario 4: Full stack (CQRS + Event Sourcing)
$commandBus->dispatch($command);
$aggregate = $repository->load($aggregateId);
```

**Deliverables:**
- All libraries verified working
- Integration tests passing
- Performance baseline established
- Ready for production use

---

## Post-Separation Tasks

### Version Management
- [ ] Tag v1.0.0 for each new library
- [ ] Tag v2.0.0 for `dranzd/common-event-sourcing` (breaking change)
- [ ] Set up semantic versioning strategy

### Community
- [ ] Announce separation on relevant channels
- [ ] Update any existing projects using the library
- [ ] Create upgrade guide for v1.x to v2.x users

### Maintenance
- [ ] Set up issue templates for each repository
- [ ] Create CONTRIBUTING.md for each repository
- [ ] Establish release process

---

## Risk Assessment

### High Risk
- **Breaking changes for existing users** - Mitigate with comprehensive migration guide
- **Circular dependencies** - Careful analysis in Step 1 will prevent this

### Medium Risk
- **Test coverage gaps** - Addressed in Step 7 with comprehensive testing
- **Performance regression** - Addressed in Step 10 with benchmarking

### Low Risk
- **Namespace conflicts** - Using consistent `Dranzd\Common\*` namespace
- **Packagist naming** - All names follow `dranzd/common-*` pattern

---

## Success Criteria

- ✅ All libraries installable via Composer
- ✅ Each library has >90% test coverage
- ✅ PHPStan level 8+ passes for all libraries
- ✅ Zero circular dependencies
- ✅ Comprehensive documentation for each library
- ✅ Existing event-sourcing users can migrate with clear guide
- ✅ New users can compose libraries as needed

---

## Timeline Estimate

**Total Estimated Time:** 35-50 hours

- Week 1: Steps 1-4 (Foundation libraries)
- Week 2: Steps 5-7 (Integration and testing)
- Week 3: Steps 8-10 (Documentation and verification)

---

## Notes

- Each step should be completed and committed before moving to the next
- Create feature branches for each step
- Tag releases after each major milestone
- Keep this document updated with actual progress and learnings

---

**Document Version:** 1.0  
**Created:** 2025-11-02  
**Last Updated:** 2025-11-02  
**Status:** Planning Phase

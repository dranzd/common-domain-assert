# Common Assert Library Strip-Down Plan

## Document Information
- **Created**: 2025-11-02
- **Purpose**: Reference guide for stripping down the monolithic `dranzd/common-event-sourcing` repository into the focused `dranzd/common-assert` library
- **Target PHP Version**: 8.3+
- **Target Dependencies**: Latest stable versions

---

## Current State Analysis

### Repository Status
- **Current Package Name**: `dranzd/common-event-sourcing`
- **Target Package Name**: `dranzd/common-assert`
- **Current Namespace**: `Dranzd\Common\EventSourcing\Domain\Assert`
- **Target Namespace**: `Dranzd\Common\Assert`
- **Current PHP Requirement**: ^8.2
- **Target PHP Requirement**: ^8.3

### Existing Files
```
/media/dev/dranzd/common-assert/
├── src/
│   └── Assertion.php (wrong namespace, needs fix)
├── tests/ (empty, needs tests)
├── docs/ (event-sourcing content, needs cleanup)
├── composer.json (wrong package name and namespace)
├── README.md (describes event-sourcing, needs rewrite)
├── LICENSE (empty, needs MIT license content)
├── phpunit.xml (wrong test suite name)
├── .github/workflows/php.yml (already has PHP 8.3 ✓)
├── Dockerfile
└── docker-compose.yml
```

### Current Dependencies
```json
"require": {
    "php": "^8.2",
    "ext-json": "*",
    "ramsey/uuid": "^4.7",      // NOT NEEDED - Remove
    "webmozart/assert": "^1.11"  // KEEP
}
```

---

## Latest Versions (2024)

### Production Dependencies
- **PHP**: ^8.3
- **webmozart/assert**: ^1.11 (latest stable)

### Development Dependencies
- **PHPUnit**: ^11.0 (latest for PHP 8.3+)
- **PHPStan**: ^2.0 (latest stable)
- **PHP_CodeSniffer**: ^3.10 (latest stable)

---

## Task Breakdown

### ✅ Task 1: Update composer.json
**Priority**: High  
**Estimated Time**: 15 minutes

**Changes Required**:
```json
{
    "name": "dranzd/common-assert",  // Changed from common-event-sourcing
    "description": "Domain assertion utilities extending webmozart/assert with custom validation helpers",
    "type": "library",
    "license": "MIT",
    "authors": [
        {
            "name": "Dranzd",
            "email": "dranzd@gmail.com"
        }
    ],
    "minimum-stability": "stable",
    "require": {
        "php": "^8.3",                    // Updated from ^8.2
        "webmozart/assert": "^1.11"       // Kept, removed ramsey/uuid and ext-json
    },
    "require-dev": {
        "phpunit/phpunit": "^11.0",       // Updated from ^10.0
        "phpstan/phpstan": "^2.0",        // Updated from ^1.10
        "squizlabs/php_codesniffer": "^3.10"  // Updated from ^3.7
    },
    "autoload": {
        "psr-4": {
            "Dranzd\\Common\\Assert\\": "src/"  // Changed namespace
        }
    },
    "autoload-dev": {
        "psr-4": {
            "Dranzd\\Common\\Assert\\Tests\\": "tests/"  // Changed namespace
        }
    },
    "scripts": {
        "test": "phpunit",
        "phpstan": "phpstan analyse src tests --level=9",  // Increased to level 9
        "cs-check": "phpcs --standard=PSR12 src tests",
        "cs-fix": "phpcbf --standard=PSR12 src tests"
    },
    "config": {
        "sort-packages": true
    }
}
```

**Verification**:
- [ ] Package name changed
- [ ] PHP version updated to ^8.3
- [ ] Dependencies updated to latest versions
- [ ] Namespace updated in autoload sections
- [ ] Removed unnecessary dependencies (ramsey/uuid, ext-json)

---

### ✅ Task 2: Fix Assertion.php Namespace
**Priority**: High  
**Estimated Time**: 5 minutes

**Current Code** (`src/Assertion.php`):
```php
<?php

namespace Dranzd\Common\EventSourcing\Domain\Assert;  // WRONG

use Webmozart\Assert\Assert as BaseAssertion;

class Assertion extends BaseAssertion
{
    public static function datetime($value, $format, $message = '')
    {
        $datetime = \DateTime::createFromFormat($format, $value);
        if (null !== $datetime) {
            return;
        }

        static::reportInvalidArgument(
            \sprintf(
                $message ?: 'Expected a date with format %s',
                $format
            )
        );
    }
}
```

**Required Change**:
```php
namespace Dranzd\Common\Assert;  // FIXED
```

**Verification**:
- [ ] Namespace updated
- [ ] File still extends webmozart/assert correctly
- [ ] Custom datetime() method preserved

---

### ✅ Task 3: Update README.md
**Priority**: High  
**Estimated Time**: 30 minutes

**New README Structure**:
```markdown
# Common Assert

A focused assertion library that extends webmozart/assert with custom domain validation helpers.

## Features
- Extends webmozart/assert with all its powerful assertions
- Custom datetime format validation
- Framework-agnostic
- PHP 8.3+ with strict typing
- Zero dependencies beyond webmozart/assert

## Requirements
- PHP 8.3 or higher
- webmozart/assert ^1.11

## Installation
composer require dranzd/common-assert

## Usage

### Basic Assertions (from webmozart/assert)
[Examples of inherited assertions]

### Custom Assertions

#### DateTime Format Validation
[Example of datetime() method]

## Development
[Test, PHPStan, Code Style commands]

## License
MIT
```

**Verification**:
- [ ] All event-sourcing references removed
- [ ] Focus on assertion functionality
- [ ] Installation command updated
- [ ] Usage examples provided
- [ ] Development instructions included

---

### ✅ Task 4: Create Comprehensive Tests
**Priority**: High  
**Estimated Time**: 45 minutes

**New File**: `tests/AssertionTest.php`

**Test Coverage Required**:
```php
<?php

namespace Dranzd\Common\Assert\Tests;

use Dranzd\Common\Assert\Assertion;
use PHPUnit\Framework\TestCase;
use InvalidArgumentException;

class AssertionTest extends TestCase
{
    // Test datetime() with valid format
    public function testDatetimeWithValidFormat(): void
    
    // Test datetime() with invalid format
    public function testDatetimeWithInvalidFormatThrowsException(): void
    
    // Test datetime() with custom error message
    public function testDatetimeWithCustomErrorMessage(): void
    
    // Test inheritance from webmozart/assert
    public function testInheritsFromWebmozartAssert(): void
    
    // Test edge cases (empty string, null, etc.)
    public function testDatetimeEdgeCases(): void
}
```

**Verification**:
- [ ] All test methods implemented
- [ ] Tests cover success and failure cases
- [ ] Tests verify custom error messages
- [ ] Tests confirm inheritance from webmozart/assert
- [ ] Edge cases covered
- [ ] Aim for 100% code coverage

---

### ✅ Task 5: Update phpunit.xml
**Priority**: Medium  
**Estimated Time**: 5 minutes

**Current**:
```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit bootstrap="vendor/autoload.php" colors="true">
    <testsuites>
        <testsuite name="Common Event Sourcing Tests">  <!-- WRONG -->
            <directory>./tests</directory>
        </testsuite>
    </testsuites>
</phpunit>
```

**Updated**:
```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit bootstrap="vendor/autoload.php" 
         colors="true"
         cacheDirectory=".phpunit.cache">
    <testsuites>
        <testsuite name="Common Assert Tests">
            <directory>./tests</directory>
        </testsuite>
    </testsuites>
    <source>
        <include>
            <directory>./src</directory>
        </include>
    </source>
</phpunit>
```

**Verification**:
- [ ] Test suite name updated
- [ ] Coverage configuration added
- [ ] Modern PHPUnit 11 configuration

---

### ✅ Task 6: Clean Up Documentation
**Priority**: Medium  
**Estimated Time**: 10 minutes

**Files to Remove**:
```
docs/examples/event-versioning-example.md          ❌ Remove
docs/guides/event-patterns.md                      ❌ Remove
docs/migration/library-separation-plan.md          ✓ Keep (archive)
docs/migration/namespace-migration.md              ❌ Remove
docs/reviews/domain-event-design-review.md         ❌ Remove
docs/reviews/domain-event-improvements.md          ❌ Remove
docs/reviews/event-versioning-implementation.md    ❌ Remove
docs/reviews/[other event-sourcing files]          ❌ Remove
```

**Files to Keep**:
```
docs/migration/common-assert-stripdown-plan.md     ✓ Keep (this document)
docs/migration/library-separation-plan.md          ✓ Keep (historical reference)
```

**Optional New Files**:
```
docs/usage-guide.md                                ? Create if needed
docs/custom-assertions.md                          ? Create if needed
```

**Verification**:
- [ ] Event-sourcing docs removed
- [ ] Migration docs preserved for reference
- [ ] Consider creating assert-specific guides

---

### ✅ Task 7: Update GitHub Actions Workflow
**Priority**: Low  
**Estimated Time**: 5 minutes

**Current** (`.github/workflows/php.yml`):
```yaml
name: PHP Tests
on: [push, pull_request]
jobs:
  test:
    runs-on: ubuntu-latest
    strategy:
      matrix:
        php-version: ['8.2', '8.3']  # Already has 8.3 ✓
    name: PHP ${{ matrix.php-version }} Tests
    steps:
    - uses: actions/checkout@v4
    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: ${{ matrix.php-version }}
    - name: Install dependencies
      run: composer install --no-progress --prefer-dist
    - name: Run tests
      run: ./vendor/bin/phpunit --configuration phpunit.xml --testdox
```

**Suggested Enhancements**:
```yaml
name: PHP Tests
on: [push, pull_request]
jobs:
  test:
    runs-on: ubuntu-latest
    strategy:
      matrix:
        php-version: ['8.3']  # Remove 8.2 since we require 8.3+
    name: PHP ${{ matrix.php-version }} Tests
    steps:
    - uses: actions/checkout@v4
    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: ${{ matrix.php-version }}
    - name: Install dependencies
      run: composer install --no-progress --prefer-dist
    - name: Run tests
      run: ./vendor/bin/phpunit --configuration phpunit.xml --testdox
    - name: Run PHPStan
      run: composer phpstan
    - name: Check code style
      run: composer cs-check
```

**Verification**:
- [ ] PHP version matrix updated (remove 8.2)
- [ ] Consider adding PHPStan and code style checks
- [ ] Workflow name is appropriate

---

### ✅ Task 8: Update LICENSE
**Priority**: Medium  
**Estimated Time**: 2 minutes

**Current**: Empty file

**Required**: MIT License content
```
MIT License

Copyright (c) 2025 Dranzd

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
```

**Verification**:
- [ ] MIT License content added
- [ ] Copyright year is current (2025)
- [ ] Author name is correct

---

### ✅ Task 9: Verification & Testing
**Priority**: High  
**Estimated Time**: 20 minutes

**Verification Checklist**:

1. **Composer Setup**:
   ```bash
   composer install
   ```
   - [ ] All dependencies install without errors
   - [ ] No conflicts with PHP 8.3

2. **Run Tests**:
   ```bash
   composer test
   ```
   - [ ] All tests pass
   - [ ] No errors or warnings

3. **Static Analysis**:
   ```bash
   composer phpstan
   ```
   - [ ] PHPStan level 9 passes
   - [ ] No type errors

4. **Code Style**:
   ```bash
   composer cs-check
   ```
   - [ ] Code follows PSR-12
   - [ ] No style violations

5. **Manual Verification**:
   - [ ] No references to "event-sourcing" in code
   - [ ] No references to "event-sourcing" in docs (except archives)
   - [ ] All namespaces use `Dranzd\Common\Assert`
   - [ ] Package name is `dranzd/common-assert` everywhere
   - [ ] README accurately describes the library

6. **Test Installation**:
   ```bash
   # In a separate test project
   composer require dranzd/common-assert:@dev
   ```
   - [ ] Library can be installed
   - [ ] Autoloading works correctly

---

## Dependency Rationale

### Why These Versions?

**PHP 8.3+**:
- Latest stable PHP version
- Improved performance
- Modern type system features
- Long-term support

**webmozart/assert ^1.11**:
- Battle-tested assertion library
- Comprehensive assertion methods
- Active maintenance
- No breaking changes expected

**PHPUnit ^11.0**:
- Latest major version
- Full PHP 8.3 support
- Modern testing features
- Better error reporting

**PHPStan ^2.0**:
- Latest major version
- Strictest type checking
- Better inference
- PHP 8.3 support

**PHP_CodeSniffer ^3.10**:
- Latest stable version
- PSR-12 support
- Active maintenance

---

## Questions & Decisions

### 1. Documentation Cleanup
**Question**: Should we completely remove the `docs/` folder or keep it for future assert-specific documentation?

**Options**:
- A) Remove everything except migration docs (minimal)
- B) Keep structure and add assert-specific guides (comprehensive)
- C) Remove all docs, rely only on README (simplest)

**Recommendation**: Option A - Keep migration docs as historical reference, remove event-sourcing content

---

### 2. Additional Assertions
**Question**: Should we add more custom assertion methods beyond just `datetime()`?

**Potential Additions**:
- `uuid()` - Validate UUID format
- `email()` - Validate email format
- `url()` - Validate URL format
- `json()` - Validate JSON string
- `phoneNumber()` - Validate phone number format
- `ipAddress()` - Validate IP address

**Recommendation**: Start minimal, add based on actual needs

---

### 3. PHPStan Level
**Question**: Is PHPStan level 9 (strictest) acceptable?

**Options**:
- Level 8: Very strict, good balance
- Level 9: Maximum strictness, may require more type hints

**Recommendation**: Level 9 - Small codebase, worth the strictness

---

### 4. Docker Files
**Question**: Should we keep `Dockerfile` and `docker-compose.yml`?

**Options**:
- Keep: Useful for consistent development environment
- Remove: Adds complexity for simple library

**Recommendation**: Keep - Useful for testing in isolated environment

---

### 5. Git History
**Question**: Keep git history or start fresh?

**Options**:
- Keep: Preserves history, shows evolution
- Fresh: Clean start, no baggage

**Recommendation**: Keep - History is valuable, separation plan is documented

---

## Execution Order

Execute tasks in this order to minimize conflicts:

1. **Task 8** - Update LICENSE (independent)
2. **Task 1** - Update composer.json (foundation)
3. **Task 2** - Fix Assertion.php namespace (depends on Task 1)
4. **Task 5** - Update phpunit.xml (independent)
5. **Task 4** - Create tests (depends on Tasks 1, 2)
6. **Task 3** - Update README.md (independent)
7. **Task 6** - Clean up documentation (independent)
8. **Task 7** - Update GitHub Actions (independent)
9. **Task 9** - Verification (depends on all previous tasks)

---

## Success Criteria

- ✅ Package name is `dranzd/common-assert`
- ✅ Namespace is `Dranzd\Common\Assert` throughout
- ✅ PHP 8.3+ requirement enforced
- ✅ All dependencies are latest stable versions
- ✅ All tests pass with 100% coverage
- ✅ PHPStan level 9 passes
- ✅ Code style checks pass
- ✅ No event-sourcing references remain (except archives)
- ✅ README accurately describes assertion library
- ✅ LICENSE file contains MIT license
- ✅ GitHub Actions workflow runs successfully

---

## Post-Strip-Down Tasks

After completing all tasks:

1. **Tag Release**:
   ```bash
   git tag -a v1.0.0 -m "Initial release of common-assert library"
   git push origin v1.0.0
   ```

2. **Publish to Packagist**:
   - Submit package to packagist.org
   - Configure GitHub webhook for auto-updates

3. **Update Other Libraries**:
   - Update `dranzd/common-valueobject` to depend on this
   - Update `dranzd/common-messaging` to depend on this
   - Update `dranzd/common-event-sourcing` to depend on this

4. **Announce**:
   - Update any project documentation
   - Notify team members
   - Update internal dependency lists

---

## Timeline Estimate

- **Task 1**: 15 minutes
- **Task 2**: 5 minutes
- **Task 3**: 30 minutes
- **Task 4**: 45 minutes
- **Task 5**: 5 minutes
- **Task 6**: 10 minutes
- **Task 7**: 5 minutes
- **Task 8**: 2 minutes
- **Task 9**: 20 minutes

**Total**: ~2.5 hours

---

## Notes

- This is a strip-down operation, not a migration
- The goal is to create a focused, single-purpose library
- Keep it simple - resist feature creep
- Document decisions for future reference
- Maintain backward compatibility where possible

---

**Document Status**: Reference Plan  
**Last Updated**: 2025-11-02  
**Next Review**: After Task 9 completion

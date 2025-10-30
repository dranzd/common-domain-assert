# Namespace Migration Notes

## Completed Migration

All namespaces have been successfully migrated from `App\Core\Common` to `Dranzd\Common\EventSourcing`.

### Files Updated
- ✅ Domain/Event (3 files)
- ✅ Domain/Message (11 files)
- ✅ Domain/EventSourcing (15 files)
- ✅ Domain/ValueObject (30 files)
- ✅ Domain/Assert (1 file)
- ✅ Util (7 files)

**Total: 67 PHP files updated**

## Missing Dependencies

### 1. Assert\Assertion Class
**Status**: ✅ Fixed - Namespace updated

**Referenced in:**
- `src/Domain/ValueObject/Identity/Uuid.php`
- `src/Domain/ValueObject/DateTime/Date.php`
- `src/Domain/ValueObject/DateTime/DateTime.php`
- `src/Domain/ValueObject/DateTime/Time.php`
- `src/Domain/ValueObject/Search/Page.php`
- `src/Domain/ValueObject/Search/Query.php`
- `src/Domain/ValueObject/Search/Result.php`

**Original namespace**: `App\Core\Common\Domain\Assert\Assertion`

**Completed**: 
- ✅ Assertion class namespace updated to `Dranzd\Common\EventSourcing\Domain\Assert\Assertion`
- ✅ All use statements in dependent files updated
- Uses `webmozart/assert` as base class

### 2. External Package Dependencies

The following external dependencies are referenced but need to be installed via composer:

**Framework Dependencies:**
- `illuminate/support` (Laravel) - Used in `Util/StringUtil.php`
- `laminas/laminas-stdlib` - Used in `Util/ArrayUtil.php`

**Library Dependencies:**
- `ramsey/uuid` - Used in `Domain/ValueObject/Identity/Uuid.php`
- `doctrine/collections` - Used in `Domain/ValueObject/Collection/Collection.php`

**Recommended composer.json additions:**
```json
{
  "require": {
    "ramsey/uuid": "^4.0",
    "doctrine/collections": "^2.0",
    "webmozart/assert": "^1.11"
  },
  "suggest": {
    "illuminate/support": "Required for Laravel framework integration and string utilities",
    "laminas/laminas-stdlib": "Required for Laminas framework integration and array utilities"
  }
}
```

## Known Issues

### Lint Warnings
The following lint warnings are expected and will resolve once dependencies are installed:
- `Undefined type 'Illuminate\Support\Str'` - Install `illuminate/support`
- `Undefined type 'Laminas\Stdlib\ArrayUtils'` - Install `laminas/laminas-stdlib`
- `Undefined type 'Ramsey\Uuid\Uuid'` - Install `ramsey/uuid`
- `Undefined type 'Doctrine\Common\Collections\ArrayCollection'` - Install `doctrine/collections`
- `Undefined type 'Webmozart\Assert\Assert'` - Install `webmozart/assert`
- `Undefined method` errors for Assertion methods - Install `webmozart/assert`

### ✅ All Namespace References Updated
All `App\Core\Common` references have been successfully migrated to `Dranzd\Common\EventSourcing`

## Next Steps

1. **✅ COMPLETED: Assertion library namespace updated**
   - Using `webmozart/assert` as base class

2. **Update composer.json**
   - Add required dependencies
   - Add suggested dependencies

3. **Run composer install**
   ```bash
   composer install
   ```

4. **Verify namespace migration**
   ```bash
   composer phpstan
   composer cs-check
   ```

5. **Update tests**
   - Ensure all test namespaces are correct
   - Run test suite to verify functionality

## Migration Date
October 30, 2025

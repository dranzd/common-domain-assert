# Changelog

All notable changes to this project are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.1.0] - 2026-11-12

First public release. `Assertion` keeps its API, and `datetime()` accepts and rejects the same values as in 1.0.0, with the one fix below.

### Added

- `@method` annotations for `nullOrDatetime()`, `allDatetime()` and `allNullOrDatetime()`, so static analysis knows the prefixed variants that already worked at runtime.
- A release workflow, contributor guide and security policy.

### Changed

- The test suite is rewritten and now shows how to extend `Assertion`. PHPStan runs at max level without ignored errors, and CI also covers PHP 8.5.
- The source file declares `strict_types`. This does not affect callers.
- The `dev-master` branch alias is `1.x-dev`. Requirements such as `^1.0` and `dev-master` resolve as before.
- The installed package no longer contains the tests and development files.
- Documentation is reduced to a README that covers usage and extension.
- When a value does not parse, `reportInvalidArgument()` is called once. It used to be called twice, which only mattered to a subclass whose override returned instead of throwing.

### Fixed

- `datetime()` rejects a value containing a NUL byte with the usual assertion exception. It used to let a `ValueError` from the date parser escape.

## [1.0.0] - 2026-06-19

Initial release.

### Added

- `Assertion`, extending [webmozart/assert](https://github.com/webmozarts/assert) with access to
  all of its assertions.
- `Assertion::datetime()`, which checks that a string is a date or time in a given format.

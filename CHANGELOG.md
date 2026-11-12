# Changelog

All notable changes to this project are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.1.0] - 2026-11-12

First public release. `Assertion` and `datetime()` behave exactly as in 1.0.0.

### Added

- `@method` annotations for `nullOrDatetime()`, `allDatetime()` and `allNullOrDatetime()`, so static
  analysis knows the prefixed variants that already worked at runtime.
- A test suite that pins `datetime()` and shows how to extend `Assertion`, PHPStan at max level,
  and CI on PHP 8.3, 8.4 and 8.5.
- A release workflow, contributor guide and security policy.

### Changed

- The source file declares `strict_types`. This does not affect callers.
- The `dev-master` branch alias is `1.x-dev` (was `1.0.x-dev`). Requirements such as `^1.0` and
  `dev-master` resolve as before.
- The installed package no longer contains the tests and development files.
- Documentation is reduced to a README that covers usage and extension.

## [1.0.0] - 2026-06-19

Initial release.

### Added

- `Assertion`, extending [webmozart/assert](https://github.com/webmozarts/assert) with access to
  all of its assertions.
- `Assertion::datetime()`, which checks that a string is a date or time in a given format.

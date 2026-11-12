# Changelog

All notable changes to this project are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.1.0] - 2026-11-12

First public release. `Assertion` and `datetime()` behave exactly as in 1.0.0.

### Added

- A test suite that pins `datetime()` and shows how to extend `Assertion`, PHPStan at max level,
  and CI on PHP 8.3, 8.4 and 8.5.
- A release workflow, contributor guide and security policy.

### Changed

- The source file declares `strict_types`. This does not affect callers.
- Documentation is reduced to a README that covers usage and extension.

## [1.0.0] - 2026-06-19

Initial release of dranzd/common-domain-assert — a Domain-Driven Design (DDD)
assertion library extending [webmozart/assert](https://github.com/webmozart/assert)
with domain-specific validation helpers.

### Added
- `Assertion` class extending `webmozart/assert` with full access to its assertions
- Domain-specific assertion helpers for the DDD domain layer
- DateTime format validation for domain datetime values
- Framework-agnostic, dependency-light design (only `webmozart/assert`)
- Full PHP 8.3+ strict type hints

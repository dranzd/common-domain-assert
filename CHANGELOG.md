# Changelog

All notable changes to `dranzd/common-domain-assert` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

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

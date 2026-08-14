# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/).

## [Unreleased]

### Changed
- **BREAKING**: Variable cache storage switched from PHP serialization to JSON payloads with `SPCJSON1:` marker.
  Caching PHP objects is no longer supported.
- Legacy serialized variable cache files are treated as invalid cache entries,
  dropped, and handled as cache miss.

### Added
- Additional tests for large variable payloads with multiline text and legacy invalid-cache handling.

## [0.2.0] - 2026-08-13

### Changed
- **BREAKING**: Minimum PHP version raised from 5.2 to 8.2. See [migration notes in README](README.md#migration).
- **Security**: `unserialize()` now passes `['allowed_classes' => false']` to prevent PHP Object Injection.
  If you were caching PHP objects, they will now deserialize as `__PHP_Incomplete_Class`. Switch to caching arrays or scalar values.
- `clearCache()` and `getCacheCount()`: `$idPrefix` is sanitized (non-alphanumeric characters stripped) to prevent glob-pattern injection.
- `clearCache()` and `getCacheCount()`: `glob()` return value is now guarded against `false` (filesystem error).
- `composer.json`: added `license`, `keywords`, PHPUnit / PHPStan dev dependencies, and Composer scripts (`lint`, `test`, `analyse`, `check`).

### Added
- PHPUnit 11 test suite covering core behavior (var cache set/get, TTL expiry, force refresh, HTML cache, clear/count).
- PHPStan level-3 static analysis configuration (`phpstan.neon`).
- GitHub Actions CI workflow (`ci.yml`) targeting PHP 8.2, 8.3, 8.4.
- `SECURITY.md` with vulnerability reporting policy and known risk notes.
- `TODO.md` with prioritized list of deferred improvements.

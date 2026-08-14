# TODO

Deferred improvements — implement when explicitly requested or when capacity allows.

## High Priority

- **[HIGH] Add namespace `Tschueller\SimplePhpCache`**
  Currently a global class. Adding a namespace is a breaking API change and requires
  a major version bump. Should be paired with moving to `src/` and PSR-4 autoloading.

## Medium Priority

- **[MEDIUM] Move source to `src/` and switch to PSR-4 autoloading**
  Current `class/` layout with `classmap` is non-standard. Migration path: move file,
  add namespace, update `composer.json`. Breaking change for manual `include` users.

- **[MEDIUM] Add proper return types and parameter types to all public methods**
  Current code has no type declarations. Requires PHP 8.x syntax review.
  Non-breaking as long as callers pass correct types.
  Prerequisite for raising PHPStan level beyond 3.

- **[MEDIUM] Raise PHPStan analysis level (target: 6–8)**
  Currently level 3. Level 10 reveals 16 findings, all related to missing type declarations.
  Can only be raised sustainably after type declarations are added.

- **[MEDIUM] Implement PSR-16 SimpleCache interface**
  Makes the library interoperable with PSR-16 consumers. Requires namespace + new
  API surface. Large refactor — deserves its own PR.

## Low Priority

- **[LOW] Add code coverage reporting to CI (Xdebug/PCOV)**
  Track test coverage over time. Optional quality gate.

- **[LOW] Add PHP_CodeSniffer / PHP-CS-Fixer for consistent code style**
  Current code mixes Allman and K&R brace styles. Low urgency.

- **[LOW] Add a `has(string $id): bool` method**
  Convenience method to check cache existence without starting a session.

- **[LOW] Expand test suite**
  Edge cases: empty string IDs, very long IDs, unreadable cache directory,
  Unicode in cache IDs.

- **[MEDIUM] Make cache writes atomic and protect readers from partial files**
  `LOCK_EX` does not prevent readers from observing a file while it is being
  written. Add an atomic-write strategy and concurrent-read/write tests.

- **[LOW] Publish to Packagist**
  Run `composer validate --strict`, submit to packagist.org.
  Prerequisite: stable tagged release with namespace.

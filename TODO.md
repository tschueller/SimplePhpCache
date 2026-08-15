# TODO

Deferred improvements — implement when explicitly requested or when capacity allows.

## Medium Priority

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

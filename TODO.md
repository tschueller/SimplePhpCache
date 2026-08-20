# TODO

Deferred improvements — implement when explicitly requested or when capacity allows.

## v1.x

- **[LOW] Add code coverage reporting to CI (Xdebug/PCOV)**
  Track test coverage over time. Optional quality gate.

- **[LOW] Add PHP_CodeSniffer / PHP-CS-Fixer for consistent code style**
  Current code mixes Allman and K&R brace styles. Low urgency.

- **[LOW] Add a `has(string $id): bool` method**
  Convenience method to check cache existence without starting a session.

- **[LOW] Expand test suite**
  Edge cases: empty string IDs, very long IDs, unreadable cache directory,
  Unicode in cache IDs.

- **[OPTIONAL] Prevent cache stampedes for concurrent cache misses**
  Add per-key coordination so concurrent requests do not regenerate the same
  missing or expired entry. Relevant for expensive cache generation.

- **[OPTIONAL] Define `clearCache()` behaviour during concurrent writes**
  Decide whether an active writer may republish an entry after it was cleared;
  implement synchronization only if strict clearing semantics are required.

## v2.x

- **[MEDIUM] Remove the deprecated implicit cache directory**
  The v1.x fallback to the system temporary directory emits `E_USER_DEPRECATED`.
  In 2.0, require an explicit `$cacheBaseDir` and throw a clear exception when
  it is missing. Keep the migration guidance in README.md.

- **[MEDIUM] Implement PSR-16 SimpleCache interface**
  Adds a separate PSR-16 cache API and storage model. Deliberately out of scope
  for the stable 1.x SimplePhpCache API; deserves its own major-version PR.

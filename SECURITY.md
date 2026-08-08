# Security Policy

## Reporting a Vulnerability

Please report security vulnerabilities by email to **thorsten@schueller.me**.

Include:
- description of the vulnerability
- steps to reproduce
- impact assessment

Do **not** open a public issue for security vulnerabilities.

You can expect an initial response within 5 business days.

## Supported Versions

| Version | Supported |
|---------|-----------|
| latest  | Yes       |

## Known Security Notes

### Deserialization (Medium – addressed in current release)

`SimplePhpCache` uses PHP's `serialize()`/`unserialize()` for variable caching.
Since version 0.2.0, `unserialize()` is called with `['allowed_classes' => false]`
to prevent PHP Object Injection attacks. This means **PHP objects cannot be stored
in the variable cache** — arrays and scalar values work fine.

Avoid storing attacker-controlled data in the cache without sanitization.

### Cache Directory Permissions (Low)

The `.simplePhpCache` directory is created with mode `0770`. Ensure the web server
user is the only entity with write access to `$cacheBaseDir`. Avoid placing the
cache directory inside the web root.

### Cache Key Predictability (Low)

Cache file names are derived from `urlencode($id) + md5($id)`. Cache IDs should not
be derived from fully user-controlled input without validation, as a valid ID is
sufficient to read a cache file if an attacker gains filesystem access.

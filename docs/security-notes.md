# Security Notes

## Deserialization (Medium — mitigated)

`SimplePhpCache` uses `serialize()`/`unserialize()` for variable caching.
`unserialize()` is called with `['allowed_classes' => false]` to prevent PHP Object
Injection: no class constructors or magic methods (`__wakeup`, `__destruct`) are
invoked on deserialization. Arrays and scalar values are unaffected.

**What this means for you:** PHP objects cannot be stored in the variable cache.
Use arrays or scalar values instead. A full replacement with `json_encode`/`json_decode`
(which has no deserialization attack surface at all) is tracked in TODO.md.

## Cache Directory Permissions (Low)

The `.simplePhpCache` subdirectory is created with mode `0770`. Recommendations:

- Set `$cacheBaseDir` to a directory outside the web root.
- Ensure only the web server user has write access to that directory.
- Never expose the cache directory via a public URL.

## Cache Key / ID Guidance (Low)

Cache file names are derived from `urlencode($id) . '-' . md5($id)`. Recommendations:

- Do not derive cache IDs directly from unvalidated user input.
- Keep IDs short and predictable within your own code.
- Filesystem access to the cache directory is sufficient to read cached data —
  treat the cache dir as sensitive if cached content is sensitive.

# Security Notes

## Variable Cache Format (Low)

`SimplePhpCache` now stores variable cache data as a JSON payload with a format
marker (`SPCJSON1:`). This removes the regular deserialization attack surface.

Legacy serialized payloads are not deserialized anymore.
They are treated as invalid, deleted, and handled as cache miss.

**What this means for you:** cache arrays and scalar values only. Do not cache objects.

## Cache Directory Permissions (Medium)

The `.simplePhpCache` subdirectory is created with mode `0770` when it does not
already exist. The implicit fallback base directory is the system temporary
directory. Its use emits an `E_USER_DEPRECATED` warning once per request and
will be removed in version 2.0. On shared systems, another local user could
create the cache subdirectory first or otherwise control its contents. This can
enable cache poisoning and, with unsafe filesystem permissions, symlink attacks.

Recommendations:

- Always set `$cacheBaseDir` in production to an application-specific directory
  outside the web root; do not rely on the system temporary directory.
- Ensure the base directory and `.simplePhpCache` are owned and writable only by
  the web server user, and do not allow untrusted users to create entries there.
- Never expose the cache directory via a public URL.

## Cache Key / ID Guidance (Low)

Cache file names are derived from `urlencode($id) . '-' . md5($id)`. Recommendations:

- Do not derive cache IDs directly from unvalidated user input.
- Keep IDs short and predictable within your own code.
- Filesystem access to the cache directory is sufficient to read cached data —
  treat the cache dir as sensitive if cached content is sensitive.

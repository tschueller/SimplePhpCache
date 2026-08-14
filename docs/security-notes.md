# Security Notes

## Variable Cache Format (Low)

`SimplePhpCache` now stores variable cache data as a JSON payload with a format
marker (`SPCJSON1:`). This removes the regular deserialization attack surface.

Legacy serialized payloads are not deserialized anymore.
They are treated as invalid, deleted, and handled as cache miss.

**What this means for you:** cache arrays and scalar values only. Do not cache objects.

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

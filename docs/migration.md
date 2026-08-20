# Migration

## From pre-1.0 (global class)

**Namespace and autoloading changed (breaking):** `SimplePhpCache` is now the
`Tschueller\SimplePhpCache\SimplePhpCache` class and is loaded via Composer
PSR-4 autoloading. Replace manual includes such as `include "SimplePhpCache.php";`
with Composer's `vendor/autoload.php`, then import the class:

	use Tschueller\SimplePhpCache\SimplePhpCache;

## From pre-0.2 (PHP 5.x / 7.x)

**PHP version:** Minimum requirement is now PHP 8.2.

**Variable cache format changed (breaking):** variable cache now uses a JSON-based
payload format (`SPCJSON1:` marker + JSON) instead of PHP `serialize()`.
Caching PHP objects is no longer supported.

**Legacy cache files:** old serialized variable cache files are treated as invalid
cache entries. They are deleted and treated as cache miss, then regenerated in
the new JSON format on the next write.

**Cache ID prefix sanitization:** Non-alphanumeric characters in the `$idPrefix`
parameter of `clearCache()` and `getCacheCount()` are now stripped. If you relied
on special characters in prefixes, update your prefix strings accordingly.

## From a PhaseUtils fork using `cacheSubDir`

The separately maintained `PhaseUtils\SimplePhpCache` variant added
`$cacheSubDir` to separate caches that share one base directory. It was not part
of an upstream SimplePhpCache release.

Replace it with `$cacheNamespace`:

```php
// PhaseUtils fork
PhaseUtils\SimplePhpCache::$cacheBaseDir = __DIR__ . '/var/cache';
PhaseUtils\SimplePhpCache::$cacheSubDir = 'customer-a';

// Current package
SimplePhpCache::$cacheBaseDir = __DIR__ . '/var/cache';
SimplePhpCache::$cacheNamespace = 'customer-a';
```

Both configurations use `<cacheBaseDir>/.simplePhpCache/customer-a`.

If `$cacheSubDir` was absent, empty, or `.simplePhpCache`, do not set
`$cacheNamespace`; the default remains `<cacheBaseDir>/.simplePhpCache`.

`$cacheNamespace` is one safe directory name. It can contain letters, numbers,
dots, hyphens, and underscores, but no path separators, spaces, `..`, or empty
value. Replace nested or path-like old values with one namespace; the cache is
then regenerated in the new location.

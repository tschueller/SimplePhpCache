SimplePhpCache
==============

A very simple PHP caching system which can cache HTML output or variables.

Requirements
------------

* PHP 8.2 or higher

Preparation
-----------

Install the package with Composer:

	composer require tschueller/simplephpcache

Load Composer's autoloader and import the class:

	<?php
	require __DIR__ . '/vendor/autoload.php';

	use Tschueller\SimplePhpCache\SimplePhpCache;

Set the cache base directory (required for production):

	SimplePhpCache::$cacheBaseDir = __DIR__ . "/var/cache";

Optionally set a namespace to keep cache entries for several applications or
tenants separate below the same base directory:

	SimplePhpCache::$cacheNamespace = "customer-a";

If no cache base directory is configured, SimplePhpCache temporarily falls back
to the system temporary directory for backward compatibility. This emits an
`E_USER_DEPRECATED` warning once per request and will be removed in version 2.0.
Set an application-owned directory outside the web root. Standard PHP error
configuration controls whether this warning is displayed or logged; for example,
exclude `E_USER_DEPRECATED` from `error_reporting` to suppress it.

Set the max cache time in seconds (Optional, default is 86400 (1 day)):

	SimplePhpCache::$maxCacheTime = 3600; // 1h

Cache HTML
----------
	<?php
	$cacheName = "html_cache_1";
	if (SimplePhpCache::initHTMLCaching($cacheName)) {
	?>
    	// Output your html stuff here
    	<h1>Test</h1>
    	<?=generateOutput();?>
	<?php
	}
	echo SimplePhpCache::finishHTMLCaching($cacheName);
	?>

Cache a variable
----------------
	<?php
	$cacheName = "var_cache_1";
	if (SimplePhpCache::initVarCaching($cacheName)) {
		// Variable to cache:
    	$dataToCache = array(1, 2, 3);
    	SimplePhpCache::setVarCaching($cacheName, $dataToCache);
	}
	print_r(SimplePhpCache::finishVarCaching($cacheName));
	?>

Clear the cache
---------------

Clear all cache files

	SimplePhpCache::clearCache();

To refresh a special cache file, set in the init method as the second parameter **true**

	// HTML
	SimplePhpCache::initHTMLCaching($cacheName, true)

	// Variable
	SimplePhpCache::initVarCaching($cacheName, true)

To override the cache lifetime for a single cache entry without changing the
global `SimplePhpCache::$maxCacheTime`, pass its lifetime in seconds as the
third parameter:

	// Cache this entry for 5 minutes
	SimplePhpCache::initHTMLCaching($cacheName, false, 300)
	SimplePhpCache::initVarCaching($cacheName, false, 300)

---

Migration
---------

See [docs/migration.md](docs/migration.md) for all migration paths, including
the separately maintained `PhaseUtils` fork.

Release Process
---------------

For the release process, versioning rules, changelog requirements, and GitHub
Actions automation, see [docs/releasing.md](docs/releasing.md).

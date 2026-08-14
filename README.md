SimplePhpCache
==============

A very simple PHP caching system which can cache HTML output or variables.

Requirements
------------

* PHP 8.2 or higher

Preparation
-----------

Include the SimplePhpCache class

	include "SimplePhpCache.php";

Set the cacheBaseDir (Optional, default is the system temp directory)

	SimplePhpCache::$cacheBaseDir = "./";

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

---

Migration
---------

### From pre-0.2 (PHP 5.x / 7.x)

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

Release Process
---------------

When creating a version tag, add a matching section to `CHANGELOG.md` first:

- `## [x.y.z] - YYYY-MM-DD`

This repository includes a GitHub Actions guard that runs on tag pushes and fails
if the corresponding changelog release section is missing.

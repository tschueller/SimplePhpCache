<?php
/*
 * Copyright (C) 2014 Thorsten Schüller
 * http://schueller.me/projects/
 * Licensed under the MIT license.
 */

namespace Tschueller\SimplePhpCache;

use RuntimeException;

class SimplePhpCache
{
    /** Prefix marker for JSON variable cache payloads. */
    private const VAR_CACHE_PREFIX = "SPCJSON1:";

     /** Cache id from the current started cache. */
    private static $startedCache = null;

    /** The cached content. */
    private static $cacheContent = null;

    /** Whether the current cache session contains a cached value. */
    private static bool $hasCacheContent = false;

    /** The cache base directory. */
    public static $cacheBaseDir = null;

    /** The max cache time. */
    public static $maxCacheTime = 86400;

    /**
     * Start the HTML output caching.
     *
     * @param string $id
     *               The cache identifier.
     * @param boolean $refresh
     *               Refresh the cache. Optional, default is false
     * @return boolean Returned true if no cache is available otherwise false
     * @throws RuntimeException
     *               When the cache is already started
     */
    public static function initHTMLCaching($id, $refresh = false)
    {
        if (self::$startedCache != null)
        {
            throw new RuntimeException("Cache is already started");
        }

        self::$startedCache = $id;
        self::$cacheContent = null;
        self::$hasCacheContent = false;

        $cacheFile = self::getCacheDir() . "/" . self::getFilename($id);

        // Check if the cached file is older then the configured time
        if (!$refresh && file_exists($cacheFile) &&
                (time() - filemtime($cacheFile)) < self::$maxCacheTime) {
            $content = file_get_contents($cacheFile);
            if ($content !== false) {
                self::$cacheContent = $content;
                self::$hasCacheContent = true;
                return false;
            }
        }

        ob_start();
        return true;
    }


    /**
     * Stops the HTML output caching and returned the cached content.
     *
     * @param string $id
     *               The cache identifier.
     * @throws RuntimeException
     *               When the cache is not started
     */
    public static function finishHTMLCaching($id)
    {
        if (self::$startedCache != $id)
        {
            throw new RuntimeException("Cache isn't started");
        }

        if (self::$hasCacheContent)
        {
            $content = self::$cacheContent;
        }
        else
        {
            $cacheFile = self::getCacheDir() . "/" . self::getFilename($id);
            $content = ob_get_clean();
            if (file_put_contents($cacheFile, $content, LOCK_EX) === false)
                throw new RuntimeException("Error writing cache: '$cacheFile'");
        }

        self::$startedCache = null;
        self::$cacheContent = null;
        self::$hasCacheContent = false;

        return $content;
    }



    /**
     * Start the variable caching.
     *
     * @param string $id
     *               The cache identifier.
     * @param boolean $refresh
     *               Refresh the cache. Option, default is false
     * @return boolean Returned true if no cache is available otherwise false
     * @throws RuntimeException
     *               When the cache is already started
     */
    public static function initVarCaching($id, $refresh = false)
    {
        if (self::$startedCache != null)
        {
            throw new RuntimeException("A cache is already started: " . self::$startedCache);
        }

        self::$startedCache = $id;
        self::$cacheContent = null;
        self::$hasCacheContent = false;

        $cacheFile = self::getCacheDir() . "/" . self::getFilename($id);

        // Check if the cached file is older then the configured time
        if(!$refresh && file_exists($cacheFile) &&
                (time() - filemtime($cacheFile)) < self::$maxCacheTime)
        {
            $raw = file_get_contents($cacheFile);
            if ($raw !== false) {
                [$isValid, $decoded] = self::decodeVarCachePayload($raw);
                if ($isValid) {
                    self::$cacheContent = $decoded;
                    self::$hasCacheContent = true;

                    return false;
                }

                // Invalid or unsupported payload (for example legacy object cache):
                // remove it and treat as cache miss so callers can regenerate safely.
                @unlink($cacheFile);
            }

            return true;
        }

        return true;
    }

    /**
     * Set the variable caching data.
     *
     * @param string $id
     *               The cache identifier.
     * @param mixed $data
     *               The data to cache.
     * @throws RuntimeException
     */
    public static function setVarCaching($id, $data)
    {
        if (self::$startedCache != $id)
        {
            throw new RuntimeException("Cache is not started");
        }

        $cacheFile = self::getCacheDir() . "/" . self::getFilename($id);

        if (self::containsObject($data)) {
            throw new RuntimeException("Caching PHP objects is not supported in JSON variable cache");
        }

        self::$cacheContent = $data;
        self::$hasCacheContent = true;
        self::writeVarCachePayload($cacheFile, $data);
    }


    /**
     * Stops the variable caching and returned the cached content.
     *
     * @param string $id
               The cache identifier.
     * @throws RuntimeException
     *               When the cache is not started
     */
    public static function finishVarCaching($id)
    {
        if (self::$startedCache != $id)
        {
            throw new RuntimeException("Cache isn't started");
        }

        $content = self::$cacheContent;
        self::$startedCache = null;
        self::$cacheContent = null;
        self::$hasCacheContent = false;

        return $content;
    }


    /**
     * Clear the complete cache or only for the given id or the given idPrefix.
     *
     * @param string $id
            The cache identifier.
     * @param string $idPrefix
            The cache identifier prefix.
     */
    public static function clearCache($id = null, $idPrefix = null) {
        if ($id) {
            $pattern = self::getFilename($id);
        } else if ($idPrefix) {
            $pattern = self::sanitizeIdPrefix($idPrefix) . "*.cache";
        } else {
            $pattern = "*.cache";
        }
        $cacheDir = self::getCacheDir();
        $scanResult = glob($cacheDir . "/" . $pattern) ?: [];
        foreach ($scanResult as $fileName) {
            unlink($fileName);
        }
    }

    /**
     * Return the count of all cache files or for the given prefix.
     *
     * @param string $idPrefix
     *            The cache identifier prefix
     * @return int
     *            The cache file count.
     */
    public static function getCacheCount($idPrefix = "") {
        $pattern = self::sanitizeIdPrefix($idPrefix) . "*.cache";
        return count(glob(self::getCacheDir() . "/" . $pattern) ?: []);
    }

    /**
     * Strip glob-unsafe characters from a cache ID prefix.
     * @param string $idPrefix
     * @return string
     */
    private static function sanitizeIdPrefix($idPrefix)
    {
        return preg_replace('/[^a-zA-Z0-9_\-.]/', '', (string) $idPrefix);
    }

    /**
     * Returns the cache id.
     *
     * @param string $id
     *            The cache id
     * @return string
     *               The cache file name.
     */
    private static function getFilename($id)
    {
        return urlencode(self::fixPath($id)) . "-" . md5($id) . ".cache";
    }

    /**
     * Decode cache payload from the JSON cache format.
     *
     * @param string $raw
     * @return array{0: bool, 1: mixed} Whether the payload is valid and its value.
     */
    private static function decodeVarCachePayload($raw)
    {
        if (!str_starts_with($raw, self::VAR_CACHE_PREFIX)) {
            return [false, null];
        }

        $json = substr($raw, strlen(self::VAR_CACHE_PREFIX));

        try {
            return [true, json_decode($json, true, 512, JSON_THROW_ON_ERROR)];
        } catch (\JsonException $e) {
            return [false, null];
        }
    }

    /**
     * Encode and persist variable cache payload in the JSON format.
     *
     * @param string $cacheFile
     * @param mixed $data
     * @throws RuntimeException
     */
    private static function writeVarCachePayload($cacheFile, $data)
    {
        try {
            $payload = self::VAR_CACHE_PREFIX . json_encode($data, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new RuntimeException("Error encoding variable cache payload: " . $e->getMessage(), 0, $e);
        }

        if (file_put_contents($cacheFile, $payload, LOCK_EX) === false) {
            throw new RuntimeException("Error writing cache: '$cacheFile'");
        }
    }

    /**
     * Detect unsupported object values recursively.
     *
     * @param mixed $value
     * @return boolean
     */
    private static function containsObject($value)
    {
        if (is_object($value)) {
            return true;
        }

        if (is_array($value)) {
            foreach ($value as $item) {
                if (self::containsObject($item)) {
                    return true;
                }
            }
        }

        return false;
    }


    /**
     * Fix the path (under windows).
     *
     * @param string $path
     *               The dir path to fix.
     * @return string The fixed path.
     */
    private static function fixPath($path)
    {
        return str_replace("\\", "/", $path);
    }


    /**
     * Returns the cache directory path. Create the cache dir if not exists.
     *
     * @return string The cache directory.
     * @throws RuntimeException
     *            When the cache directory creation failed.
     */
    private static function getCacheDir()
    {
       if (self::$cacheBaseDir == null) {
            self::$cacheBaseDir = sys_get_temp_dir();
        }
        $dir = self::fixPath(self::$cacheBaseDir) . "/.simplePhpCache";
        if (!is_dir($dir) && !mkdir($dir, 0770, true)) {
            throw new RuntimeException("Can not create cache directory: '$dir'");
        }
        return $dir;
    }

}

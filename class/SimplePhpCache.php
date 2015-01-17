<?php
/*
 * Copyright (C) 2014 Thorsten Schüller
 * http://schueller.me/projects/
 * Licensed under the MIT license.
 */

use \RuntimeException;

class SimplePhpCache
{
	 /** Cache id from the current started cache. */
	private static $startedCache = null;

	/** The cached content. */
	private static $cacheContent = null;

	/** The cache base directory. */
	public static $cacheBaseDir = null;

	/** The max cache time. */
	public static $maxCacheTime = 86400;

	/**
	 * Constructor
	 */
	private function __construct()
	{
	    SimplePhpCache::$cacheBaseDir = sys_get_temp_dir();
	}

    /**
     * Start the HTML output caching.
     *
     * @param string $id
     * 			  The cache identifyer.
     * @param boolean $refresh
     * 			  Refresh the cache. Optional, default is false
     * @return boolean Returned true if no cache is available otherwise false
     * @throws RuntimeException
     * 			  When the cache is already started
     */
    public static function initHTMLCaching($id, $refresh = false)
    {
    	if (self::$startedCache != null)
    	{
    		throw new RuntimeException("Cache is already started");
    	}

    	self::$startedCache = $id;
    	self::$cacheContent = null;

		$cacheFile = self::getCacheDir() . "/" . self::getFilename($id);

		// Check if the cached file is older then the configured time
		if(!$refresh && file_exists($cacheFile) &&
				(time() - filemtime($cacheFile)) < self::$maxCacheTime)
		{
			self::$cacheContent = file_get_contents($cacheFile);
			return false;
		}

		ob_start();
		return true;
    }


    /**
     * Stops the HTML output caching and returned the cached content.
     *
     * @param string $id
     * 			  The cache identifyer.
     * @throws RuntimeException
     * 			  When the cache is not started
     */
    public static function finishHTMLCaching($id)
    {
    	if (self::$startedCache != $id)
    	{
    		throw new RuntimeException("Cache isn't started");
    	}

    	if (self::$cacheContent != null)
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

    	return $content;
    }



    /**
     * Start the variable caching.
     *
     * @param string $id
     * 			  The cache identifyer.
     * @param boolean $refresh
     * 			  Refresh the cache. Option, default is false
     * @return boolean Returned true if no cache is available otherwise false
     * @throws RuntimeException
     * 			  When the cache is already started
     */
    public static function initVarCaching($id, $refresh = false)
    {
    	if (self::$startedCache != null)
    	{
    		throw new RuntimeException("Cache is already started");
    	}

    	self::$startedCache = $id;

    	$cacheFile = self::getCacheDir() . "/" . self::getFilename($id);

    	// Check if the cached file is older then the configured time
    	if(!$refresh && file_exists($cacheFile) &&
    			(time() - filemtime($cacheFile)) < self::$maxCacheTime)
    	{
    		self::$cacheContent = unserialize(file_get_contents($cacheFile));
    		return false;
    	}

    	return true;
    }

    /**
     * Set the variable caching data.
     *
     * @param string $id
     * 			  The cache identifyer.
     * @param mixed $data
     * 			  The data to cache.
     * @throws RuntimeException
     */
    public static function setVarCaching($id, $data)
    {
    	if (self::$startedCache != $id)
    	{
    		throw new RuntimeException("Cache is not started");
    	}

    	$cacheFile = self::getCacheDir() . "/" . self::getFilename($id);

    	self::$cacheContent = $data;
    	if (file_put_contents($cacheFile, serialize($data), LOCK_EX) === false)
    		throw new RuntimeException("Error writing cache: '$cacheFile'");
    }


    /**
     * Stops the variable caching and returned the cached content.
     *
     * @param string $id
     * 			  The cache identifyer.
     * @throws RuntimeException
     * 			  When the cache is not started
     */
    public static function finishVarCaching($id)
    {
    	if (self::$startedCache != $id)
    	{
    		throw new RuntimeException("Cache isn't started");
    	}

    	self::$startedCache = null;

    	return self::$cacheContent;
    }


    /**
     * Clear the complete cache.
     */
    public static function clearCache()
    {
        $cacheDir = self::getCacheDir();
        $scanResult = scandir($cacheDir);
        foreach ($scanResult as $fileName)
        {
            if (preg_match("/^.*\.(cache)$/i", $fileName))
            {
               unlink("$cacheDir/$fileName");
            }
        }
    }


    /**
     * Returns the cache id.
     *
     * @param string $id
     *            The cache id
     * @return string
     * 			  The cache file name.
     */
    private static function getFilename($id)
    {
        return urlencode(self::fixPath($id)) . "-" . md5($id) . ".cache";
    }


    /**
     * Fix the path (under windows).
     *
     * @param string $path
     * 			  The dir path to fix.
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
     *            When the cache directory creation falied.
     */
    private static function getCacheDir()
    {

        $dir = self::fixPath(self::$cacheBaseDir) . "/.simplePhpCache";
        if (!is_dir($dir) && !mkdir($dir, 0770, true))
        {
            throw new RuntimeException("Can not create cache directory: '$dir'");
        }
        return $dir;
    }

}

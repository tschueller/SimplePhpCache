<?php

include "../class/SimplePhpCache.php";

// Set the cacheBaseDir (if needed). Default is the system temp directory.
//SimplePhpCache::$cacheBaseDir = "./";

// Set the max cache time (if needed). Default is 86400;
//SimplePhpCache::$maxCacheTime = 86400;

// Clear the cache if requested
if (isset($_REQUEST["clearCache"])) 
{    
    SimplePhpCache::clearCache();
    header("Location: //". $_SERVER["HTTP_HOST"] . $_SERVER["PHP_SELF"]);
    exit();
}

?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8" />
    <title>SimplePhpCache Demo</title>
  </head>
  <body>

    <h1>SimplePhpCache Demo</h1>
    <button onclick="window.location.href=window.location.href+'?clearCache=true'">Clear Cache</button>  
    <hr/>
    
<?
  
    /*** Start the html output cache ***/
    $cacheName = "html_cache_1";
    if (SimplePhpCache::initHTMLCaching($cacheName))
    {
        $cacheDate = date("d.m.Y H:i:s");
?>
        <h2>HTML Output Cache</h2>
        <p>Name: <?=$cacheName?></p>
        <p><?=$cacheDate?></p>
<? 
    }
    echo SimplePhpCache::finishHTMLCaching($cacheName);
    
    
    /*** Start the variable cache ***/
    $cacheName = "var_cache_1";
    if (SimplePhpCache::initVarCaching($cacheName))
    {
        $dataToCache = array(
            "time" => date("d.m.Y H:i:s"), 
            "name" => $cacheName);
        SimplePhpCache::setVarCaching($cacheName, $dataToCache);
    }
    
    $cachedData = SimplePhpCache::finishVarCaching($cacheName);
?>
    <hr/>
    <h2>Var Output Cache</h2>
    <p>Name: <?=$cachedData["name"]?></p>
    <p><?=$cachedData["time"]?></p>
  
  </body>
</html>
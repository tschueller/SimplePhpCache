<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class SimplePhpCacheTest extends TestCase
{
    private string $testCacheDir;

    protected function setUp(): void
    {
        $this->testCacheDir = sys_get_temp_dir() . '/simplephpcache_test_' . uniqid();
        mkdir($this->testCacheDir, 0770, true);

        SimplePhpCache::$cacheBaseDir = $this->testCacheDir;
        SimplePhpCache::$maxCacheTime = 86400;

        $this->resetStaticState();
    }

    protected function tearDown(): void
    {
        SimplePhpCache::clearCache();

        $cacheSubDir = $this->testCacheDir . '/.simplePhpCache';
        foreach (glob($cacheSubDir . '/*') ?: [] as $file) {
            unlink($file);
        }
        if (is_dir($cacheSubDir)) {
            rmdir($cacheSubDir);
        }
        if (is_dir($this->testCacheDir)) {
            rmdir($this->testCacheDir);
        }

        SimplePhpCache::$cacheBaseDir = null;
    }

    private function resetStaticState(): void
    {
        $r = new ReflectionClass(SimplePhpCache::class);
        $r->getProperty('startedCache')->setValue(null, null);
        $r->getProperty('cacheContent')->setValue(null, null);
    }

    // --- Variable caching ---

    public function testVarCachingMissOnFirstCall(): void
    {
        $miss = SimplePhpCache::initVarCaching('var_first');
        $this->assertTrue($miss);

        SimplePhpCache::setVarCaching('var_first', 'data');
        SimplePhpCache::finishVarCaching('var_first');
    }

    public function testVarCachingSetAndGet(): void
    {
        $id = 'var_set_get';
        $data = ['key' => 'value', 'number' => 42, 'nested' => [1, 2, 3]];

        SimplePhpCache::initVarCaching($id);
        SimplePhpCache::setVarCaching($id, $data);
        $fetched = SimplePhpCache::finishVarCaching($id);

        $this->assertEquals($data, $fetched);
    }

    public function testVarCachingHitOnSecondCall(): void
    {
        $id = 'var_hit';
        $data = ['cached' => true];

        SimplePhpCache::initVarCaching($id);
        SimplePhpCache::setVarCaching($id, $data);
        SimplePhpCache::finishVarCaching($id);
        $this->resetStaticState();

        $miss = SimplePhpCache::initVarCaching($id);
        $this->assertFalse($miss, 'Second call should be a cache hit');

        $result = SimplePhpCache::finishVarCaching($id);
        $this->assertEquals($data, $result);
    }

    public function testVarCachingExpiredEntryTreatedAsMiss(): void
    {
        $id = 'var_expire';
        SimplePhpCache::$maxCacheTime = 0;

        SimplePhpCache::initVarCaching($id);
        SimplePhpCache::setVarCaching($id, 'expires');
        SimplePhpCache::finishVarCaching($id);
        $this->resetStaticState();

        // maxCacheTime=0 means (time() - mtime) < 0 is always false → cache miss
        $miss = SimplePhpCache::initVarCaching($id);
        $this->assertTrue($miss, 'Expired entry should be a cache miss');

        SimplePhpCache::setVarCaching($id, 'renewed');
        SimplePhpCache::finishVarCaching($id);
    }

    public function testVarCachingForceRefresh(): void
    {
        $id = 'var_refresh';

        SimplePhpCache::initVarCaching($id);
        SimplePhpCache::setVarCaching($id, 'original');
        SimplePhpCache::finishVarCaching($id);
        $this->resetStaticState();

        $miss = SimplePhpCache::initVarCaching($id, true);
        $this->assertTrue($miss, 'Force refresh must bypass the cache');

        SimplePhpCache::setVarCaching($id, 'updated');
        $result = SimplePhpCache::finishVarCaching($id);
        $this->assertEquals('updated', $result);
    }

    public function testInitVarThrowsWhenAlreadyStarted(): void
    {
        $this->expectException(RuntimeException::class);

        SimplePhpCache::initVarCaching('first');
        SimplePhpCache::initVarCaching('second');
    }

    // --- HTML caching ---

    public function testHtmlCachingCapturesOutput(): void
    {
        $id = 'html_capture';

        if (SimplePhpCache::initHTMLCaching($id)) {
            echo 'cached html output';
        }
        $output = SimplePhpCache::finishHTMLCaching($id);

        $this->assertEquals('cached html output', $output);
    }

    public function testHtmlCachingReturnsCachedContent(): void
    {
        $id = 'html_hit';

        if (SimplePhpCache::initHTMLCaching($id)) {
            echo 'stored content';
        }
        SimplePhpCache::finishHTMLCaching($id);
        $this->resetStaticState();

        $miss = SimplePhpCache::initHTMLCaching($id);
        $this->assertFalse($miss, 'Second call should be a cache hit');

        $output = SimplePhpCache::finishHTMLCaching($id);
        $this->assertEquals('stored content', $output);
    }

    // --- clear / count ---

    public function testClearCacheById(): void
    {
        $id = 'clear_by_id';

        SimplePhpCache::initVarCaching($id);
        SimplePhpCache::setVarCaching($id, 'x');
        SimplePhpCache::finishVarCaching($id);

        $this->assertEquals(1, SimplePhpCache::getCacheCount());

        SimplePhpCache::clearCache($id);

        $this->assertEquals(0, SimplePhpCache::getCacheCount());
    }

    public function testClearAllCache(): void
    {
        foreach (['a', 'b', 'c'] as $id) {
            SimplePhpCache::initVarCaching($id);
            SimplePhpCache::setVarCaching($id, $id);
            SimplePhpCache::finishVarCaching($id);
            $this->resetStaticState();
        }

        $this->assertEquals(3, SimplePhpCache::getCacheCount());

        SimplePhpCache::clearCache();

        $this->assertEquals(0, SimplePhpCache::getCacheCount());
    }

    public function testClearCacheByPrefix(): void
    {
        foreach (['prefix_one', 'prefix_two', 'other'] as $id) {
            SimplePhpCache::initVarCaching($id);
            SimplePhpCache::setVarCaching($id, $id);
            SimplePhpCache::finishVarCaching($id);
            $this->resetStaticState();
        }

        SimplePhpCache::clearCache(null, 'prefix_');

        $this->assertEquals(1, SimplePhpCache::getCacheCount(), 'Only non-prefixed entry should remain');
    }

    public function testGetCacheCountReturnsZeroOnEmptyCache(): void
    {
        $this->assertEquals(0, SimplePhpCache::getCacheCount());
    }
}

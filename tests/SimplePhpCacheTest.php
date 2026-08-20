<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Tschueller\SimplePhpCache\SimplePhpCache;

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
        $r->getProperty('hasCacheContent')->setValue(null, false);
    }

    private function getCacheFilePathForId(string $id): string
    {
        $cacheSubDir = $this->testCacheDir . '/.simplePhpCache';
        return $cacheSubDir . '/' . urlencode(str_replace('\\', '/', $id)) . '-' . md5($id) . '.cache';
    }

    public function testImplicitDefaultCacheDirectoryTriggersDeprecation(): void
    {
        SimplePhpCache::$cacheBaseDir = null;
        $reportedErrors = [];

        set_error_handler(
            static function (int $severity, string $message) use (&$reportedErrors): bool {
                $reportedErrors[] = [$severity, $message];
                return true;
            }
        );

        try {
            SimplePhpCache::getCacheCount();
            SimplePhpCache::getCacheCount();
        } finally {
            restore_error_handler();
            SimplePhpCache::$cacheBaseDir = $this->testCacheDir;
        }

        $this->assertSame(
            [[
                E_USER_DEPRECATED,
                'Using the system temporary directory as the SimplePhpCache cache base directory is deprecated. '
                . 'Set SimplePhpCache::$cacheBaseDir to an application-owned directory; the implicit default will be removed in 2.0.',
            ]],
            $reportedErrors
        );
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

    public function testVarCachingNullRoundTrip(): void
    {
        $id = 'var_null';

        SimplePhpCache::initVarCaching($id);
        SimplePhpCache::setVarCaching($id, null);
        SimplePhpCache::finishVarCaching($id);
        $this->resetStaticState();

        $miss = SimplePhpCache::initVarCaching($id);
        $this->assertFalse($miss);
        $this->assertNull(SimplePhpCache::finishVarCaching($id));
    }

    public function testVarCachingMissDoesNotReturnContentFromPreviousSession(): void
    {
        SimplePhpCache::initVarCaching('first');
        SimplePhpCache::setVarCaching('first', 'previous value');
        SimplePhpCache::finishVarCaching('first');

        $miss = SimplePhpCache::initVarCaching('second');
        $this->assertTrue($miss);
        $this->assertNull(SimplePhpCache::finishVarCaching('second'));
    }

    public function testVarCachingLargePayloadAndMultilineTextRoundTrip(): void
    {
        $id = 'var_large_multiline';
        $largeText = str_repeat("Line one\nLine two with umlaut äöü\r\n", 2000);
        $largeList = range(1, 3000);
        $data = [
            'title' => 'Large payload',
            'body' => $largeText,
            'nested' => [
                'rows' => array_map(
                    static fn (int $n): array => [
                        'id' => $n,
                        'text' => "entry-{$n}",
                        'active' => $n % 2 === 0,
                    ],
                    range(1, 500)
                ),
                'numbers' => $largeList,
            ],
        ];

        SimplePhpCache::initVarCaching($id);
        SimplePhpCache::setVarCaching($id, $data);
        SimplePhpCache::finishVarCaching($id);
        $this->resetStaticState();

        $miss = SimplePhpCache::initVarCaching($id);
        $this->assertFalse($miss);
        $result = SimplePhpCache::finishVarCaching($id);

        $this->assertSame($data, $result);
    }

    public function testLegacySerializedPayloadIsDroppedAndTreatedAsMiss(): void
    {
        $id = 'var_legacy_payload';
        $cacheFile = $this->getCacheFilePathForId($id);

        SimplePhpCache::initVarCaching($id);
        SimplePhpCache::setVarCaching($id, 'seed');
        SimplePhpCache::finishVarCaching($id);

        file_put_contents($cacheFile, serialize(['legacy' => true, 'value' => 123]), LOCK_EX);
        $this->resetStaticState();

        $miss = SimplePhpCache::initVarCaching($id);
        $this->assertTrue($miss, 'Legacy serialized payload is unsupported and should be treated as miss');
        $this->assertFileDoesNotExist($cacheFile);

        SimplePhpCache::setVarCaching($id, ['fresh' => true]);
        $result = SimplePhpCache::finishVarCaching($id);
        $this->assertSame(['fresh' => true], $result);
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

    public function testVarCachingPerCallMaxCacheTimeOverridesGlobalSetting(): void
    {
        $id = 'var_per_call_ttl';

        SimplePhpCache::initVarCaching($id);
        SimplePhpCache::setVarCaching($id, 'expires');
        SimplePhpCache::finishVarCaching($id);
        $this->resetStaticState();

        $this->assertTrue(SimplePhpCache::initVarCaching($id, false, 0));
        SimplePhpCache::setVarCaching($id, 'renewed');
        SimplePhpCache::finishVarCaching($id);
        $this->assertSame(86400, SimplePhpCache::$maxCacheTime);
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

    public function testCacheRefreshAtomicallyReplacesExistingFile(): void
    {
        $id = 'atomic_refresh';

        SimplePhpCache::initVarCaching($id);
        SimplePhpCache::setVarCaching($id, 'original');
        SimplePhpCache::finishVarCaching($id);
        $this->resetStaticState();

        SimplePhpCache::initVarCaching($id, true);
        SimplePhpCache::setVarCaching($id, 'replacement');
        SimplePhpCache::finishVarCaching($id);
        $this->resetStaticState();

        $this->assertFalse(SimplePhpCache::initVarCaching($id));
        $this->assertSame('replacement', SimplePhpCache::finishVarCaching($id));
        $this->assertSame(
            [],
            glob($this->testCacheDir . '/.simplePhpCache/.simplephpcache-*') ?: [],
            'Successful cache writes must not leave temporary files behind.'
        );
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

    public function testHtmlCachingReturnsEmptyCachedContent(): void
    {
        $id = 'html_empty';

        SimplePhpCache::initHTMLCaching($id);
        SimplePhpCache::finishHTMLCaching($id);
        $this->resetStaticState();

        $this->assertFalse(SimplePhpCache::initHTMLCaching($id));
        $this->assertSame('', SimplePhpCache::finishHTMLCaching($id));
    }

    public function testHtmlCachingPerCallMaxCacheTimeOverridesGlobalSetting(): void
    {
        $id = 'html_per_call_ttl';

        if (SimplePhpCache::initHTMLCaching($id)) {
            echo 'expires';
        }
        SimplePhpCache::finishHTMLCaching($id);
        $this->resetStaticState();

        $this->assertTrue(SimplePhpCache::initHTMLCaching($id, false, 0));
        echo 'renewed';
        $this->assertSame('renewed', SimplePhpCache::finishHTMLCaching($id));
        $this->assertSame(86400, SimplePhpCache::$maxCacheTime);
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

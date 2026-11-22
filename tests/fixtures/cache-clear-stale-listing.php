<?php

declare(strict_types=1);

namespace Tschueller\SimplePhpCache;

/**
 * Simulate another request deleting the first cache entry after glob lists it.
 * Loaded only inside a process-isolated regression test.
 *
 * @return list<string>|false
 */
function glob(string $pattern): array|false
{
    $files = \glob($pattern);
    if ($files !== false && isset($files[0]) && !\unlink($files[0])) {
        throw new \RuntimeException('Unable to prepare the stale cache listing.');
    }
    return $files;
}

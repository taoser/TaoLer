<?php

/**
 * Shared coverage filter helper.
 *
 * The installed phpunit/php-code-coverage Filter only supports file-level
 * inclusion (includeFiles / includeFile), not includeDirectory. Both the
 * subprocess runner (tests/Support/run-in-worker.php) and the parent merge
 * script (bin/merge-coverage.php) need to instrument exactly the project src/
 * tree, so they share this helper to enumerate every .php file under src/.
 *
 * @return list<string> absolute paths of every .php file under <project>/src
 */

declare(strict_types=1);

if (!function_exists('workerman_redis_src_files')) {
    /**
     * @return list<string>
     */
    function workerman_redis_src_files(): array
    {
        $srcDir = dirname(__DIR__, 2) . '/src';
        if (!is_dir($srcDir)) {
            return [];
        }

        $files = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($srcDir, FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $fileInfo) {
            /** @var SplFileInfo $fileInfo */
            if ($fileInfo->isFile() && $fileInfo->getExtension() === 'php') {
                $real = $fileInfo->getRealPath();
                if ($real !== false) {
                    $files[] = $real;
                }
            }
        }

        sort($files);

        return $files;
    }
}

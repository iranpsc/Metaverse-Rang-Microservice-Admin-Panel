<?php

declare(strict_types=1);

/**
 * Print a GitHub Actions job summary for tests (controllers vs services).
 *
 * Usage: php scripts/summarize-tests.php [junit.xml] [clover.xml]
 */
require __DIR__.DIRECTORY_SEPARATOR.'TestResultsSummarizer.php';

$junitPath = $argv[1] ?? 'coverage/junit.xml';
$cloverPath = $argv[2] ?? 'coverage/clover.xml';

try {
    $summarizer = new TestResultsSummarizer;
    $lines = $summarizer->summarize($junitPath, $cloverPath);
    echo implode("\n", $lines);
    exit(0);
} catch (Throwable $exception) {
    fwrite(STDERR, $exception->getMessage()."\n");
    exit(1);
}

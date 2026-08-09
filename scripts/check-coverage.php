<?php

declare(strict_types=1);

/**
 * Fail if clover statement/line coverage is below the required threshold.
 *
 * Usage: php scripts/check-coverage.php [clover.xml] [threshold]
 */
$cloverPath = $argv[1] ?? 'coverage/clover.xml';
$threshold = isset($argv[2]) ? (float) $argv[2] : 98.0;

if (! is_file($cloverPath)) {
    fwrite(STDERR, "Coverage report not found: {$cloverPath}\n");
    exit(1);
}

$xml = @simplexml_load_file($cloverPath);
if ($xml === false) {
    fwrite(STDERR, "Unable to parse clover report: {$cloverPath}\n");
    exit(1);
}

$metrics = $xml->project->metrics ?? $xml->metrics ?? null;
if ($metrics === null) {
    fwrite(STDERR, "Clover report is missing project metrics.\n");
    exit(1);
}

$statements = (int) ($metrics['statements'] ?? 0);
$coveredStatements = (int) ($metrics['coveredstatements'] ?? 0);
$statementCoverage = $statements > 0 ? ($coveredStatements / $statements) * 100 : 100.0;

echo sprintf(
    "Coverage: %.2f%% (%d/%d statements)\nThreshold: %.2f%%\n",
    $statementCoverage,
    $coveredStatements,
    $statements,
    $threshold
);

if ($statementCoverage + 0.0001 < $threshold) {
    fwrite(
        STDERR,
        sprintf(
            "FAIL: Coverage %.2f%% is below the required %.2f%%.\n",
            $statementCoverage,
            $threshold
        )
    );
    exit(1);
}

echo sprintf("PASS: Coverage %.2f%% meets the required %.2f%%.\n", $statementCoverage, $threshold);
exit(0);

<?php

declare(strict_types=1);

/**
 * Fail if clover line/statement coverage is below the required threshold.
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
$methods = (int) ($metrics['methods'] ?? 0);
$coveredMethods = (int) ($metrics['coveredmethods'] ?? 0);
$elements = (int) ($metrics['elements'] ?? 0);
$coveredElements = (int) ($metrics['coveredelements'] ?? 0);

$statementCoverage = $statements > 0 ? ($coveredStatements / $statements) * 100 : 100.0;
$elementCoverage = $elements > 0 ? ($coveredElements / $elements) * 100 : 100.0;
$methodCoverage = $methods > 0 ? ($coveredMethods / $methods) * 100 : 100.0;

$lines = [
    sprintf('Statements : %.2f%% (%d/%d)', $statementCoverage, $coveredStatements, $statements),
    sprintf('Methods    : %.2f%% (%d/%d)', $methodCoverage, $coveredMethods, $methods),
    sprintf('Elements   : %.2f%% (%d/%d)', $elementCoverage, $coveredElements, $elements),
    sprintf('Threshold  : %.2f%%', $threshold),
];

echo implode(PHP_EOL, $lines).PHP_EOL;

if ($statementCoverage + 0.0001 < $threshold) {
    fwrite(
        STDERR,
        sprintf(
            "FAIL: Statement coverage %.2f%% is below the required %.2f%%.\n",
            $statementCoverage,
            $threshold
        )
    );
    exit(1);
}

echo sprintf("PASS: Statement coverage %.2f%% meets the required %.2f%%.\n", $statementCoverage, $threshold);
exit(0);

<?php

declare(strict_types=1);

/**
 * Build GitHub Actions job-summary Markdown from PHPUnit JUnit + Clover reports.
 */
final class TestResultsSummarizer
{
    private const CONTROLLERS = 'Controllers';

    private const SERVICES = 'Services';

    private const OTHER_UNIT = 'Other unit';

    /**
     * @return list<string>
     */
    public function summarize(?string $junitPath, ?string $cloverPath): array
    {
        $lines = [];

        if ($junitPath !== null && $junitPath !== '' && is_file($junitPath)) {
            $lines = array_merge($lines, $this->summarizeJunit($junitPath));
        } else {
            $lines[] = '## Test results';
            $lines[] = '';
            $lines[] = '_JUnit report not found; test categories were not generated._';
            $lines[] = '';
        }

        if ($cloverPath !== null && $cloverPath !== '' && is_file($cloverPath)) {
            if ($lines !== []) {
                $lines[] = '';
            }
            $lines = array_merge($lines, $this->summarizeClover($cloverPath));
        }

        return $lines;
    }

    /**
     * @return list<string>
     */
    public function summarizeJunit(string $junitPath): array
    {
        $xml = $this->loadXml($junitPath);
        $cases = $this->collectTestCases($xml);

        $categories = [
            self::CONTROLLERS => [],
            self::SERVICES => [],
            self::OTHER_UNIT => [],
        ];
        $failures = [];

        foreach ($cases as $case) {
            $category = $this->classify($case);
            $group = $this->groupName($case, $category);
            if (! isset($categories[$category][$group])) {
                $categories[$category][$group] = $this->emptyGroup();
            }

            $categories[$category][$group]['tests']++;
            $categories[$category][$group]['time'] += $case['time'];

            if ($case['status'] === 'failed' || $case['status'] === 'error') {
                $categories[$category][$group]['failed']++;
                $failures[] = $case;
            } elseif ($case['status'] === 'skipped') {
                $categories[$category][$group]['skipped']++;
            } else {
                $categories[$category][$group]['passed']++;
            }
        }

        $lines = [];
        $lines[] = '## Test results';
        $lines[] = '';
        $lines[] = '| Category | Tests | Passed | Failed | Skipped | Time |';
        $lines[] = '| --- | ---: | ---: | ---: | ---: | ---: |';

        foreach ([self::CONTROLLERS, self::SERVICES, self::OTHER_UNIT] as $category) {
            $totals = $this->totals($categories[$category]);
            $lines[] = sprintf(
                '| %s | %d | %d | %d | %d | %s |',
                $category,
                $totals['tests'],
                $totals['passed'],
                $totals['failed'],
                $totals['skipped'],
                $this->formatTime($totals['time'])
            );
        }

        $lines[] = '';
        $lines = array_merge($lines, $this->groupTable('### Controllers', $categories[self::CONTROLLERS], 'Domain'));
        $lines[] = '';
        $lines = array_merge($lines, $this->groupTable('### Services', $categories[self::SERVICES], 'Service'));
        $lines[] = '';
        $lines = array_merge($lines, $this->groupTable('### Other unit', $categories[self::OTHER_UNIT], 'Area'));

        if ($failures !== []) {
            $lines[] = '';
            $lines[] = '### Failures';
            $lines[] = '';
            foreach ($failures as $failure) {
                $label = $this->cell($failure['class'].'::'.$failure['name']);
                $message = $this->cell($this->firstLine($failure['message']));
                $lines[] = sprintf('- **%s** (%s): %s', $label, $failure['status'], $message);
            }
        }

        $lines[] = '';

        return $lines;
    }

    /**
     * @return list<string>
     */
    public function summarizeClover(string $cloverPath): array
    {
        $xml = $this->loadXml($cloverPath);
        $controllers = [];
        $services = [];

        foreach ($xml->xpath('//file') ?: [] as $file) {
            $originalPath = str_replace('\\', '/', (string) ($file['name'] ?? $file['path'] ?? ''));
            if ($originalPath === '') {
                continue;
            }

            $metrics = $file->metrics ?? null;
            if ($metrics === null) {
                continue;
            }

            $statements = (int) ($metrics['statements'] ?? 0);
            $covered = (int) ($metrics['coveredstatements'] ?? 0);
            $path = strtolower($originalPath);

            if (str_contains($path, '/app/http/controllers/')) {
                $controllers[$this->coverageLabel($originalPath, '/app/Http/Controllers/')] = [
                    'statements' => $statements,
                    'covered' => $covered,
                ];
            } elseif (str_contains($path, '/app/services/')) {
                $services[$this->coverageLabel($originalPath, '/app/Services/')] = [
                    'statements' => $statements,
                    'covered' => $covered,
                ];
            }
        }

        $lines = [];
        $lines[] = '## Coverage by layer';
        $lines[] = '';
        $lines = array_merge($lines, $this->coverageTable('### Controllers', $controllers));
        $lines[] = '';
        $lines = array_merge($lines, $this->coverageTable('### Services', $services));
        $lines[] = '';

        return $lines;
    }

    private function loadXml(string $path): SimpleXMLElement
    {
        $xml = @simplexml_load_file($path);
        if ($xml === false) {
            throw new RuntimeException('Unable to parse XML report: '.$path);
        }

        return $xml;
    }

    /**
     * @return list<array{name: string, class: string, file: string, time: float, status: string, message: string}>
     */
    private function collectTestCases(SimpleXMLElement $xml): array
    {
        $cases = [];

        foreach ($xml->xpath('//testcase') ?: [] as $node) {
            $status = 'passed';
            $message = '';

            if (isset($node->failure)) {
                $status = 'failed';
                $message = trim((string) $node->failure);
            } elseif (isset($node->error)) {
                $status = 'error';
                $message = trim((string) $node->error);
            } elseif (isset($node->skipped)) {
                $status = 'skipped';
                $message = trim((string) $node->skipped);
            }

            $class = (string) ($node['classname'] ?? $node['class'] ?? '');
            $file = (string) ($node['file'] ?? '');

            $cases[] = [
                'name' => (string) ($node['name'] ?? ''),
                'class' => $class,
                'file' => $file,
                'time' => (float) ($node['time'] ?? 0),
                'status' => $status,
                'message' => $message,
            ];
        }

        return $cases;
    }

    /**
     * @param  array{name: string, class: string, file: string, time: float, status: string, message: string}  $case
     */
    private function classify(array $case): string
    {
        $haystack = $case['class'].' '.$this->normalizePath($case['file']);

        if (str_contains($haystack, 'Tests\\Feature\\') || str_contains($haystack, '/tests/feature/')) {
            return self::CONTROLLERS;
        }

        if (str_ends_with($case['class'], 'ServiceTest') || str_ends_with($case['file'], 'ServiceTest.php')) {
            return self::SERVICES;
        }

        return self::OTHER_UNIT;
    }

    /**
     * @param  array{name: string, class: string, file: string, time: float, status: string, message: string}  $case
     */
    private function groupName(array $case, string $category): string
    {
        $class = $case['class'];
        $segments = $class !== '' ? explode('\\', $class) : [];

        if ($category === self::SERVICES) {
            $short = $segments !== [] ? (string) end($segments) : basename($case['file'], '.php');

            return preg_replace('/Test$/', '', $short) ?: $short;
        }

        if (count($segments) >= 3 && ($segments[1] === 'Feature' || $segments[1] === 'Unit')) {
            return $segments[2];
        }

        $path = $this->normalizePath($case['file']);
        if (preg_match('#/tests/(?:feature|unit)/([^/]+)/#i', $path, $matches) === 1) {
            return $matches[1];
        }

        return $class !== '' ? $class : 'Unknown';
    }

    /**
     * @return array{tests: int, passed: int, failed: int, skipped: int, time: float}
     */
    private function emptyGroup(): array
    {
        return [
            'tests' => 0,
            'passed' => 0,
            'failed' => 0,
            'skipped' => 0,
            'time' => 0.0,
        ];
    }

    /**
     * @param  array<string, array{tests: int, passed: int, failed: int, skipped: int, time: float}>  $groups
     * @return array{tests: int, passed: int, failed: int, skipped: int, time: float}
     */
    private function totals(array $groups): array
    {
        $totals = $this->emptyGroup();
        foreach ($groups as $group) {
            $totals['tests'] += $group['tests'];
            $totals['passed'] += $group['passed'];
            $totals['failed'] += $group['failed'];
            $totals['skipped'] += $group['skipped'];
            $totals['time'] += $group['time'];
        }

        return $totals;
    }

    /**
     * @param  array<string, array{tests: int, passed: int, failed: int, skipped: int, time: float}>  $groups
     * @return list<string>
     */
    private function groupTable(string $heading, array $groups, string $label): array
    {
        $lines = [$heading, ''];

        if ($groups === []) {
            $lines[] = '_No tests in this category._';

            return $lines;
        }

        $sorted = $groups;
        uksort($sorted, function (string $left, string $right) use ($groups): int {
            $failedCmp = $groups[$right]['failed'] <=> $groups[$left]['failed'];
            if ($failedCmp !== 0) {
                return $failedCmp;
            }

            return strcasecmp($left, $right);
        });

        $lines[] = sprintf('| %s | Tests | Passed | Failed | Skipped | Time |', $label);
        $lines[] = '| --- | ---: | ---: | ---: | ---: | ---: |';

        foreach ($sorted as $name => $stats) {
            $lines[] = sprintf(
                '| %s | %d | %d | %d | %d | %s |',
                $this->cell($name),
                $stats['tests'],
                $stats['passed'],
                $stats['failed'],
                $stats['skipped'],
                $this->formatTime($stats['time'])
            );
        }

        return $lines;
    }

    /**
     * @param  array<string, array{statements: int, covered: int}>  $files
     * @return list<string>
     */
    private function coverageTable(string $heading, array $files): array
    {
        $lines = [$heading, ''];

        if ($files === []) {
            $lines[] = '_No files in this layer._';

            return $lines;
        }

        $totalStatements = 0;
        $totalCovered = 0;
        foreach ($files as $stats) {
            $totalStatements += $stats['statements'];
            $totalCovered += $stats['covered'];
        }

        uksort($files, function (string $left, string $right) use ($files): int {
            $leftPct = $this->percent($files[$left]['covered'], $files[$left]['statements']);
            $rightPct = $this->percent($files[$right]['covered'], $files[$right]['statements']);
            $pctCmp = $leftPct <=> $rightPct;
            if ($pctCmp !== 0) {
                return $pctCmp;
            }

            return strcasecmp($left, $right);
        });

        $lines[] = sprintf(
            '**Total: %s** (%d/%d statements)',
            $this->formatPercent($this->percent($totalCovered, $totalStatements)),
            $totalCovered,
            $totalStatements
        );
        $lines[] = '';
        $lines[] = '| File | Covered | Statements | Coverage |';
        $lines[] = '| --- | ---: | ---: | ---: |';

        foreach ($files as $name => $stats) {
            $lines[] = sprintf(
                '| %s | %d | %d | %s |',
                $this->cell($name),
                $stats['covered'],
                $stats['statements'],
                $this->formatPercent($this->percent($stats['covered'], $stats['statements']))
            );
        }

        return $lines;
    }

    private function coverageLabel(string $path, string $needle): string
    {
        $path = str_replace('\\', '/', $path);
        $offset = stripos($path, $needle);
        if ($offset === false) {
            return basename($path);
        }

        $relative = substr($path, $offset + strlen($needle));

        return preg_replace('/\.php$/i', '', $relative) ?: $relative;
    }

    private function normalizePath(string $path): string
    {
        return strtolower(str_replace('\\', '/', $path));
    }

    private function percent(int $covered, int $statements): float
    {
        if ($statements <= 0) {
            return 100.0;
        }

        return ($covered / $statements) * 100;
    }

    private function formatPercent(float $percent): string
    {
        return sprintf('%.1f%%', $percent);
    }

    private function formatTime(float $seconds): string
    {
        return sprintf('%.1fs', $seconds);
    }

    private function firstLine(string $message): string
    {
        $message = trim($message);
        if ($message === '') {
            return 'see JUnit report';
        }

        $line = explode("\n", $message)[0];
        $line = trim($line);
        if (strlen($line) > 180) {
            return substr($line, 0, 177).'...';
        }

        return $line;
    }

    private function cell(string $value): string
    {
        $value = str_replace(['|', "\r", "\n"], ['\\|', ' ', ' '], $value);

        return trim($value);
    }
}

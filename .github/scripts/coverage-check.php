<?php

declare(strict_types=1);

// Fails the build when line coverage in a Clover report is below a threshold.
// Usage: php coverage-check.php <clover.xml> <min-percent>
// Exit codes: 0 = threshold met, 1 = below threshold, 2 = unusable input.

function fail(string $message): never
{
    fwrite(STDERR, $message . "\n");
    exit(2);
}

$file = $argv[1] ?? '';
$minimum = $argv[2] ?? '';

if (!is_numeric($minimum)) {
    fail('Usage: php coverage-check.php <clover.xml> <min-percent>');
}

$report = is_file($file) ? @simplexml_load_file($file) : false;
$metrics = $report === false ? [] : $report->xpath('/coverage/project/metrics');
if (!isset($metrics[0]['statements'], $metrics[0]['coveredstatements'])) {
    fail("Not a Clover coverage report: {$file}");
}

$statements = (int) $metrics[0]['statements'];
$covered = (int) $metrics[0]['coveredstatements'];
if ($statements === 0) {
    fail('The coverage report contains no executable statements.');
}

$percent = $covered / $statements * 100;
printf("Line coverage: %.2f%% (%d/%d), required: %.2f%%\n", $percent, $covered, $statements, (float) $minimum);

exit($percent + 1e-9 >= (float) $minimum ? 0 : 1);

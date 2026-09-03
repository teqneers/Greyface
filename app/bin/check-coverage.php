#!/usr/bin/env php
<?php

/**
 * Fails when line coverage in a Clover report drops below a threshold.
 *
 * PHPUnit has no built-in minimum-coverage gate, so CI uses this. The threshold
 * is a ratchet: raise it as coverage improves, never lower it to make a build
 * pass.
 *
 * Usage: php bin/check-coverage.php <clover.xml> <minimum percentage>
 */

$cloverPath = $argv[1] ?? null;
$minimum = isset($argv[2]) ? (float)$argv[2] : null;

if ($cloverPath === null || $minimum === null) {
    fwrite(STDERR, "Usage: php bin/check-coverage.php <clover.xml> <minimum percentage>\n");
    exit(2);
}

if (!is_file($cloverPath)) {
    fwrite(STDERR, sprintf("Coverage report not found: %s\n", $cloverPath));
    exit(2);
}

$xml = simplexml_load_file($cloverPath);
if ($xml === false) {
    fwrite(STDERR, sprintf("Could not parse coverage report: %s\n", $cloverPath));
    exit(2);
}

$metrics = $xml->project->metrics ?? null;
if ($metrics === null) {
    fwrite(STDERR, "Coverage report contains no project metrics.\n");
    exit(2);
}

$statements = (int)$metrics['statements'];
$covered = (int)$metrics['coveredstatements'];

if ($statements === 0) {
    fwrite(STDERR, "Coverage report contains no statements — did the test run fail?\n");
    exit(2);
}

$percentage = $covered / $statements * 100;

printf("Line coverage: %.2f%% (%d/%d), minimum %.2f%%\n", $percentage, $covered, $statements, $minimum);

if ($percentage + 0.005 < $minimum) {
    fwrite(STDERR, sprintf(
        "Coverage %.2f%% is below the required %.2f%%.\n",
        $percentage,
        $minimum
    ));
    exit(1);
}

exit(0);

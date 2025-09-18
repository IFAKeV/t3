#!/usr/bin/env php
<?php
$logFile = __DIR__ . '/mail_output.log';
$entry = "=== MAIL ===\n";
$entry .= 'ARGS: ' . json_encode(array_slice($argv, 1)) . "\n";
$entry .= "DATA:\n" . stream_get_contents(STDIN) . "\n";
$entry .= "=== END ===\n";
file_put_contents($logFile, $entry, FILE_APPEND);

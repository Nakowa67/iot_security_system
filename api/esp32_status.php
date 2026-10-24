<?php
// Receive ESP32 connection status without saving it to MySQL.

header('Content-Type: text/plain; charset=utf-8');
header('Cache-Control: no-store');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('POST method required');
}

// Store only the latest connection time in a temporary file.
$statusFile = __DIR__ . '/esp32_status.txt';

$written = file_put_contents(
    $statusFile,
    (string) time(),
    LOCK_EX
);

if ($written === false) {
    http_response_code(500);
    exit('Could not update ESP32 status');
}

http_response_code(200);
echo 'ESP32 status updated';
?>

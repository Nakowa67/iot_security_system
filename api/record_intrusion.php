
<?php

header('Content-Type: application/json; charset=utf-8');

require_once('../config_setup/database.php');

// Accept POST requests only
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);

    echo json_encode([
        'success' => false,
        'message' => 'POST requests only.'
    ]);

    exit;
}

// Read the JSON data sent by the ESP32
$rawData = file_get_contents('php://input');
$data = json_decode($rawData, true);

// Validate the request
if (!is_array($data) || !isset($data['event_type'])) {
    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Invalid request data.'
    ]);

    exit;
}

$eventType = $data['event_type'];

// Accept only supported event types
$allowedEvents = [
    'Movement Detected',
    'ESP32 Heartbeat'
];

if (!in_array($eventType, $allowedEvents, true)) {
    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Unsupported event type.'
    ]);

    exit;
}

// Generate the date and time using the server clock
date_default_timezone_set('Africa/Lagos');

$eventDate = date('Y-m-d');
$eventTime = date('H:i:s');

// Set the event status
$eventStatus = 'New';

// Save the event using a prepared statement
$sql = "INSERT INTO intrusion_events
        (event_type, event_date, event_time, event_status)
        VALUES (?, ?, ?, ?)";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Unable to prepare database query.'
    ]);

    exit;
}

mysqli_stmt_bind_param(
    $stmt,
    'ssss',
    $eventType,
    $eventDate,
    $eventTime,
    $eventStatus
);

if (mysqli_stmt_execute($stmt)) {

    http_response_code(201);

    echo json_encode([
        'success' => true,
        'message' => 'Event recorded successfully.',
        'event_id' => mysqli_insert_id($conn)
    ]);

} else {

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Unable to record event.'
    ]);
}

mysqli_stmt_close($stmt);
mysqli_close($conn);

?>

<?php
// Configure through process environment; credentials never belong in source.
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
try {
    $username = getenv('DB_USER');
    if ($username === false || $username === '') {
        throw new RuntimeException('DB_USER is required');
    }
    $conn = new mysqli(getenv('DB_HOST') ?: 'localhost', $username,
        getenv('DB_PASSWORD') ?: '', getenv('DB_NAME') ?: 'friendzone_db');
    $conn->set_charset('utf8mb4');
} catch (Throwable $error) {
    error_log('Database connection unavailable');
    http_response_code(503);
    exit('Service temporarily unavailable.');
}

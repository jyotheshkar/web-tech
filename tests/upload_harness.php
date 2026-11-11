<?php
// A test-only endpoint is disabled unless the dedicated CI server explicitly enables it.
if (getenv('FRIENDZONE_TEST_MODE') !== '1') { http_response_code(404); exit; }
require dirname(__DIR__) . '/security.php';
require dirname(__DIR__) . '/uploads.php';
header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $_SESSION['user_data'] = ['user_id' => 7];
    echo json_encode(['token' => csrf_token()]);
    exit;
}
require_post_request();
try { echo json_encode(['image' => store_uploaded_image('fixture')]); }
catch (InvalidArgumentException $error) { reject_request($error->getMessage(), 400); }

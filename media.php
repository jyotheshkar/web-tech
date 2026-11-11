<?php
require_once __DIR__ . '/security.php';
require_once __DIR__ . '/uploads.php';
if (!isset($_SESSION['user_data']['user_id'])) reject_request('Authentication required.', 401);
$filename = $_GET['file'] ?? '';
if (!is_string($filename) || !preg_match('/^[a-f0-9]{32}\.png$/D', $filename)) {
    reject_request('Image not found.', 404);
}
try { $directory = upload_directory(false); }
catch (Throwable $error) { reject_request('Image not found.', 404); }
$path = $directory . DIRECTORY_SEPARATOR . $filename;
if (!is_file($path) || is_link($path)) reject_request('Image not found.', 404);
header('Content-Type: image/png');
header('Content-Disposition: inline; filename="' . $filename . '"');
header('Content-Length: ' . filesize($path));
readfile($path);

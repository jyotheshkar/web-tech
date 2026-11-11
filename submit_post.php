<?php
require_once __DIR__ . '/security.php';
require_post_request();
if (!isset($_SESSION['user_data']['user_id'])) reject_request('Authentication required.', 401);
$content = $_POST['postContent'] ?? null;
if (!is_string($content) || trim($content) === '' || strlen($content) > 10000) {
    reject_request('Post content must contain between 1 and 10000 bytes.', 400);
}
require __DIR__ . '/db_connect.php';
require_once __DIR__ . '/uploads.php';
try { $imagePath = store_uploaded_image('postImage'); }
catch (InvalidArgumentException $error) { reject_request($error->getMessage(), 400); }
catch (Throwable $error) { error_log('Image storage unavailable'); reject_request('Image storage unavailable.', 503); }
try {
    $userId = $_SESSION['user_data']['user_id'];
    $stmt = $conn->prepare('INSERT INTO posts (user_id, content, image) VALUES (?, ?, ?)');
    $stmt->bind_param('iss', $userId, $content, $imagePath);
    $stmt->execute();
    $stmt->close();
    header('Location: posts.php', true, 303);
} catch (Throwable $error) {
    discard_new_uploaded_image($imagePath);
    error_log('Post insert failed');
    reject_request('Unable to create post.', 500);
} finally { $conn->close(); }

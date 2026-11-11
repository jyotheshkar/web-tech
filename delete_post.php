<?php
require_once __DIR__ . '/security.php';
require_post_request();
header('Content-Type: application/json');
if (!isset($_SESSION['user_data']['user_id'])) reject_request('Authentication required.', 401);
$input = json_decode(file_get_contents('php://input'), true);
$postId = is_array($input) ? filter_var($input['post_id'] ?? null, FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1]]) : false;
if ($postId === false || $postId === null) reject_request('Valid post ID required.', 400);
require __DIR__ . '/db_connect.php';
try {
    $userId = $_SESSION['user_data']['user_id'];
    $conn->begin_transaction();
    $stmt = $conn->prepare('SELECT post_id FROM posts WHERE post_id = ? AND user_id = ? FOR UPDATE');
    $stmt->bind_param('ii', $postId, $userId);
    $stmt->execute();
    $owned = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$owned) { $conn->rollback(); reject_request('Post not found.', 404); }
    foreach (['comments', 'likes'] as $table) {
        $stmt = $conn->prepare("DELETE FROM $table WHERE post_id = ?");
        $stmt->bind_param('i', $postId);
        $stmt->execute();
        $stmt->close();
    }
    $stmt = $conn->prepare('DELETE FROM posts WHERE post_id = ? AND user_id = ?');
    $stmt->bind_param('ii', $postId, $userId);
    $stmt->execute();
    $stmt->close();
    $conn->commit();
    echo json_encode(['success' => true]);
} catch (Throwable $error) {
    $conn->rollback();
    error_log('Post deletion transaction failed');
    reject_request('Unable to delete post.', 500);
} finally { $conn->close(); }

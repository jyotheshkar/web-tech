<!-- delete_post.php -->

<?php
header('Content-Type: application/json');
session_start();

// Start output buffering to prevent premature output
ob_start(); 

// Include the database connection file
require 'db_connect.php'; // Adjust this path as necessary.

// Read the JSON input from the request body
$input = json_decode(file_get_contents("php://input"), true);
file_put_contents("debug.txt", print_r($input, true)); // This will write the input data to debug.txt file for inspection.


// Ensure the user is logged in
if (!isset($_SESSION['user_data'])) {
    echo json_encode(['success' => false, 'error' => 'User not authenticated']);
    exit;
}

// Check if the post ID was provided in the JSON input
if (!isset($input['post_id'])) {
    echo json_encode(['success' => false, 'error' => 'Post ID not provided']);
    exit;
}

$post_id = $input['post_id'];
$user_id = $_SESSION['user_data']['user_id'];

// Prepare SQL to delete the post only if it belongs to the user
$sql = "DELETE FROM posts WHERE post_id = ? AND user_id = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode(['success' => false, 'error' => 'Prepare failed: ' . $conn->error]);
    exit;
}

// Bind the post ID and user ID to the prepared statement
$stmt->bind_param("ii", $post_id, $user_id);

// Execute the deletion
if (!$stmt->execute()) {
    echo json_encode(['success' => false, 'error' => 'Execute failed: ' . $stmt->error]);
    exit;
}

// Check if any rows were affected
if ($stmt->affected_rows > 0) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'No rows affected. Post ID may not exist or user may not have permission to delete this post.']);
}

// Close the statement and the database connection
$stmt->close();
$conn->close();

// Send output buffer and turn off output buffering
ob_end_flush(); 
?>
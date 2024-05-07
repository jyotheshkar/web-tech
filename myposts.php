<!-- myposts.php -->
<?php
session_start();
require 'db_connect.php'; // Ensure your db_connect.php correctly sets up the database connection.

if (!isset($_SESSION['user_data'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_data']['user_id'];

$sql = "SELECT post_id, content, created_at, image FROM posts WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

echo '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Posts</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-200">
<nav class="sticky top-0 bg-gray-900 p-4 text-white flex justify-between items-center z-50">
    <div class="flex items-center">
        <img class="w-auto px-2 bg-white py-2 h-10 sm:h-10 rounded-full mr-2" src="images/ficon.png" alt="">
        <div class="text-lg font-semibold">My Posts</div>
    </div>
    <a href="body.php" class="hover:bg-gray-700 bg-gray-100 text-gray-900 hover:text-gray-100 px-3 py-2 rounded text-sm transition-colors duration-200">Back</a>
</nav>
    <div class="container mx-auto px-4 mt-8">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">';

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $imagePath = $row['image'] ? 'uploads/' . $row['image'] : 'images/default.png';
        $imagePath = str_replace('uploads/uploads/', 'uploads/', $imagePath);

        echo '<div class="bg-gray-100 rounded-lg shadow-xl overflow-hidden">
                <img class="w-full h-64 object-cover" src="' . htmlspecialchars($imagePath) . '" alt="Post Image">
                <div class="p-4 bg-gray-900">
                    <div class="uppercase tracking-wide text-sm text-gray-100 font-semibold">Posted on ' . date("F j, Y, g:i a", strtotime($row['created_at'])) . '</div>
                    <p class="mt-1 text-lg leading-tight font-medium text-gray-100">' . htmlspecialchars($row['content']) . '</p>
                </div>
            </div>';
    }
} else {
    echo '<p class="text-center text-xl text-gray-500 mt-10 col-span-3">You have no posts yet.</p>';
}

echo '        </div>
    </div>
</body>
</html>';

$stmt->close();
$conn->close();
?>

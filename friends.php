
<!-- friends.php -->
<?php
// Start the session
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_data'])) {
    // If not logged in, redirect to the login page
    header("Location: login.php");
    exit;
}

$servername = "localhost";
$username = "root";
$password = "";  // Assuming no password for localhost
$database = "friendzone_db";

$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize search query
$search = $_GET['search'] ?? '';

// Prepare SQL query based on search input
$sql = "SELECT * FROM users WHERE username LIKE ?";
$stmt = $conn->prepare($sql);
$searchTerm = "%$search%";
$stmt->bind_param("s", $searchTerm);
$stmt->execute();
$result = $stmt->get_result();

// Store the fetched users in an array
$users = array();
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Skip the currently logged-in user
        if ($row['email'] === $_SESSION['user_data']['email']) {
            continue;
        }
        $users[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Friendzone</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-200">
<nav class="sticky top-0 bg-gray-900 p-4 text-white flex justify-between items-center z-50">
    <div class="flex items-center">
        <img class="w-auto px-2 bg-white py-2 h-10 sm:h-10 rounded-full mr-2" src="images/ficon.png" alt="">
        <div class="text-lg font-semibold">Friends</div>
    </div>
    <a href="body.php" class="hover:bg-gray-700 bg-gray-100 text-gray-900 hover:text-gray-100 px-3 py-2 rounded text-sm transition-colors duration-200">Back</a>
</nav>
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold mb-6">Explore Your Friends</h1>
        
        <!-- Search Form -->
        <form method="GET" action="friends.php" class="flex items-center space-x-2" >
            <input type="text" name="search" placeholder="Search by username" required class="flex-1 p-2 mb-8 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:outline-none transition duration-150 ease-in-out">
            <button type="submit" class="bg-gray-900 mb-8 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded transition duration-150 ease-in-out">
                Search
            </button>
        </form>

        <?php if (!empty($search)) : ?>
            <!-- Back Button appears only after search -->
            <a href="friends.php" class="bg-gray-900 hover:bg-gray-100 hover:text-gray-900 text-gray-100 px-5 py-3 mb-4 rounded text-md transition-colors duration-200">Go Back</a>
        <?php endif; ?>

        <!-- Users List -->
        <div id="users-list" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mt-8">
            <?php if (!empty($users)) {
                foreach ($users as $user) {
                    echo "<div class='bg-white shadow-md rounded-lg p-6 user-card'>";
                    echo "<div class='user-details'>";
                    echo "<img src='" . $user['profile_pic'] . "' alt='" . $user['username'] . "' class='rounded-full mx-auto mb-4' style='width: 100px; height: 100px;'>";
                    echo "<p class='text-xl font-semibold mb-2'>" . $user['username'] . "</p>";
                    echo "<p class='text-gray-600 email'>" . $user['email'] . "</p>";
                    echo "</div>";
                    echo "</div>";
                }
            } else {
                echo "<p>No users found.</p>";
            } ?>
        </div>
    </div>




</body>
</html>

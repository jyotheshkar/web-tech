<!-- db_connet.php -->
<?php
$servername = "localhost";
$username = "root";
$password = "";  // Assuming no password for localhost
$database = "friendzone_db"; // Use your actual database name

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
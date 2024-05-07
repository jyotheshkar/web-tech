<!-- submit_post.php -->
<?php
session_start(); // Start the session at the beginning
include 'db_connect.php'; // Include your database connection script

// Check if the form has been submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['postImage']) && isset($_POST['postContent'])) {
    // Ensure the user is logged in by checking the session
    if (!isset($_SESSION['user_data']['user_id'])) {
        die("Error: User is not logged in.");
    }

    // Retrieve user ID from session
    $userId = $_SESSION['user_data']['user_id'];
    $textContent = $_POST['postContent'];
    $imagePath = 'images/default.png'; // Default image path

    // Handle the file upload
    if ($_FILES['postImage']['error'] == 0) {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($_FILES["postImage"]["name"]);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];

        // Check if file type is allowed
        if (in_array($imageFileType, $allowedTypes)) {
            if (move_uploaded_file($_FILES["postImage"]["tmp_name"], $target_file)) {
                $imagePath = $target_file;
            } else {
                echo "Sorry, there was an error uploading your file.";
            }
        } else {
            echo "Sorry, only JPG, JPEG, PNG, and GIF files are allowed.";
        }
    }

    // Prepare SQL statement to insert the post into the database
    $sql = "INSERT INTO posts (user_id, content, image) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die('MySQL prepare error: ' . $conn->error);
    }

    $stmt->bind_param("iss", $userId, $textContent, $imagePath);
    if (!$stmt->execute()) {
        die('Execute error: ' . $stmt->error);
    }

    $stmt->close();
    $conn->close();

    // Redirect back to posts page or a success page
    header("Location: posts.php");
    exit();
} else {
    // If the correct POST requests aren't set
    echo "Invalid request.";
}
?>

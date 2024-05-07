<!-- signupcode.php -->
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
$servername = "localhost";
$username = "root";
$password = "";  // Assuming no password for localhost
$database = "friendzone_db";

$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['signup'])) {
    $firstName = $_POST['firstName'];
    $lastName = $_POST['lastName'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $birthday = $_POST['birthday'];
    $gender = $_POST['gender'];
    $username = $_POST['username'];

    // Check password length
    if (strlen($password) < 8) {
        echo "Password must be at least 8 characters long.";
        exit;
    }

    // Hashing the password
    $password = password_hash($password, PASSWORD_DEFAULT);

    // Check if username already exists in the database using prepared statement
    $stmt = $conn->prepare("SELECT username FROM Users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "Username already in use. Please choose a different username.";
        exit;
    }

    // Process file upload and get file path
    $target_file = processFileUpload();

    // Insert user data into database using prepared statement
    $stmt = $conn->prepare("INSERT INTO Users (FirstName, LastName, Email, Password, Birthday, Gender, Username, Profile_Pic) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssss", $firstName, $lastName, $email, $password, $birthday, $gender, $username, $target_file);
    if ($stmt->execute()) {
        header("Location: login.php"); // Redirect to login page after successful registration
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}

// Close connection
$conn->close();

function processFileUpload() {
    $defaultImagePath = 'images/default.png';  // Default path if no image is uploaded

    if (isset($_FILES['profilePic']) && $_FILES['profilePic']['error'] === 0) {
        $target_dir = "uploads/";
        $file_ext = strtolower(pathinfo($_FILES["profilePic"]["name"], PATHINFO_EXTENSION));
        $allowed_types = array('jpg', 'png', 'jpeg', 'gif');

        if (in_array($file_ext, $allowed_types)) {
            $new_filename = uniqid('', true) . '.' . $file_ext; // Generate unique file name
            $target_file = $target_dir . $new_filename;

            if (!file_exists($target_dir)) {
                if (!mkdir($target_dir, 0755, true)) {
                    error_log("Failed to create directory: $target_dir");
                    echo "Failed to create directory for uploads.";
                    return $defaultImagePath;
                }
            }

            if (move_uploaded_file($_FILES["profilePic"]["tmp_name"], $target_file)) {
                echo "The file has been uploaded successfully.";
                return $target_file;
            } else {
                error_log("Error moving uploaded file: $target_file");
                echo "Sorry, there was an error uploading your file.";
                return $defaultImagePath;
            }
        } else {
            echo "Invalid file type.";
            return $defaultImagePath;
        }
    } else {
        echo "No file was uploaded.";
        return $defaultImagePath;
    }
}
?>
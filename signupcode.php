<?php
require_once __DIR__ . '/security.php';
require_post_request();
// Server errors belong in logs, not in HTTP responses.

// Database connection
require __DIR__ . '/uploads.php';
require __DIR__ . '/db_connect.php';

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
    try { $target_file = store_uploaded_image('profilePic'); }
    catch (InvalidArgumentException $error) { reject_request($error->getMessage(), 400); }
    catch (Throwable $error) { error_log('Image storage unavailable'); reject_request('Image storage unavailable.', 503); }

    // Insert user data into database using prepared statement
    $stmt = $conn->prepare("INSERT INTO Users (FirstName, LastName, Email, Password, Birthday, Gender, Username, Profile_Pic) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssss", $firstName, $lastName, $email, $password, $birthday, $gender, $username, $target_file);
    if ($stmt->execute()) {
        header("Location: login.php"); // Redirect to login page after successful registration
        exit();
    } else {
        error_log('Registration insert failed');
        reject_request('Unable to create account.', 500);
    }
    $stmt->close();
}

// Close connection
$conn->close();


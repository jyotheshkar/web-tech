<?php
require_once __DIR__ . '/security.php';
require_post_request();
$fields = ['firstName', 'lastName', 'email', 'password', 'birthday', 'gender', 'username'];
foreach ($fields as $field) {
    if (!isset($_POST[$field]) || !is_string($_POST[$field]) || trim($_POST[$field]) === '') {
        reject_request('Required registration fields are missing.', 400);
    }
}
$firstName = trim($_POST['firstName']);
$lastName = trim($_POST['lastName']);
$email = trim($_POST['email']);
$password = $_POST['password'];
$birthday = $_POST['birthday'];
$gender = $_POST['gender'];
$username = trim($_POST['username']);
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 8 || strlen($password) > 72) {
    reject_request('Use a valid email and a password of 8 to 72 bytes.', 400);
}
$date = DateTime::createFromFormat('!Y-m-d', $birthday);
if (!$date || $date->format('Y-m-d') !== $birthday) reject_request('Invalid birthday.', 400);
require __DIR__ . '/db_connect.php';
require_once __DIR__ . '/uploads.php';
$imagePath = 'images/default.png';
try {
    $stmt = $conn->prepare('SELECT username FROM users WHERE username = ?');
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $exists = $stmt->get_result()->num_rows > 0;
    $stmt->close();
    if ($exists) reject_request('Username already in use.', 409);
    $imagePath = store_uploaded_image('profilePic');
    $password = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare('INSERT INTO users (FirstName, LastName, Email, Password, Birthday, Gender, Username, Profile_Pic) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->bind_param('ssssssss', $firstName, $lastName, $email, $password, $birthday, $gender, $username, $imagePath);
    $stmt->execute();
    $stmt->close();
    header('Location: login.php', true, 303);
} catch (InvalidArgumentException $error) {
    discard_new_uploaded_image($imagePath);
    reject_request($error->getMessage(), 400);
} catch (Throwable $error) {
    discard_new_uploaded_image($imagePath);
    error_log('Registration failed');
    reject_request('Unable to create account.', 500);
} finally { $conn->close(); }

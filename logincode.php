<!-- logincode.php -->
<?php
session_start();

// Redirect to body.php if user is already logged in
if (isset($_SESSION['user_data'])) {
    header("Location: body.php");
    exit;
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    // Ensure email and password are provided
    if (!empty($_POST['email']) && !empty($_POST['password'])) {
        // Database connection parameters
        $servername = "localhost";
        $username = "root";
        $password = "";  // Assuming no password for localhost
        $database = "friendzone_db";

        // Create connection
        $conn = new mysqli($servername, $username, $password, $database);

        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Escape user inputs for security
        $email = $conn->real_escape_string($_POST['email']);
        $userInputPassword = $_POST['password']; // Use another variable to hold user input

        // Query to check if email exists in the database
        $sql = "SELECT user_id, firstName, lastName, email, password FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        // Check if email exists
        if ($result->num_rows == 1) {
            // Fetch user data
            $user_data = $result->fetch_assoc();
        
            // Verify the password
            if (password_verify($userInputPassword, $user_data['password'])) {
                // Remove password from session data for security
                unset($user_data['password']);
        
                // Fetch 'username' and 'profile_pic' from the database
                $sql = "SELECT username, profile_pic, email FROM users WHERE user_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $user_data['user_id']);
                $stmt->execute();
                $additional_data = $stmt->get_result()->fetch_assoc();
        

                // Add 'username' and 'profile_pic' to session
$_SESSION['user_data'] = [
    'user_id' => $user_data['user_id'],  // Corrected syntax here
    'username' => $additional_data['username'],
    'profile_pic' => $additional_data['profile_pic'],
    'email' => $additional_data['email']
];
        
                // Regenerate session ID for security
                session_regenerate_id();
        
                // Redirect to body.php after successful login
                header("Location: body.php");
                exit();
            } else {
                // Password is not correct
                $_SESSION['login_error'] = "The email address or password you entered doesn't match our records. Please double-check and try again.";
                header("Location: login.php");
                exit();
            }
        } else {
            // No user found with that email address
            $_SESSION['login_error'] = "No user found with that email address.";
            header("Location: login.php");
            exit();
        }

        // Close database connection
        $conn->close();
    } else {
        // Handle the case where email or password is empty
        $_SESSION['login_error'] = "Please enter both email and password.";
        header("Location: login.php");
        exit();
    }
}
?>
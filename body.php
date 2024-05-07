<!-- body.php -->
<?php
session_start();

// Prevent page caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Check if user is not logged in, redirect to login page
if (!isset($_SESSION['user_data'])) {
    header("Location: login.php");
    exit;
}

if (isset($_SESSION['user_data'])) {
    // Extract user data from the session
    $user = $_SESSION['user_data'];
    
    // Check if 'username' and 'profile_pic' are set in the session and use them
    $username = isset($user['username']) ? htmlspecialchars($user['username']) : 'No Username';
    $profilePic = isset($user['profile_pic']) && !empty($user['profile_pic']) ? $user['profile_pic'] : 'images/default.png';
    $email = isset($user['email']) ? htmlspecialchars($user['email']) : 'No Email';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Friendzone</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@100;200;300;400;500;600;700;800;900&family=Raleway:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">

 

</head>
<body class="bg-gradient-to-r from-gray-300 via-gray-200 to-gray-300 h-screen">
    <!-- main  -->
    <div class="flex flex-col justify-between  px-4 py-12 overflow-y-auto bg-gray-900 rounded-b-full rtl:border-r-0 rtl:border-l dark:bg-gray-900 dark:border-gray-700">
        <a href="#" class="mx-auto flex items-center justify-evenly">
            <img class="w-auto px-2 bg-white py-2 h-14 sm:h-14 rounded-full mr-2" src="images/ficon.png" alt="">
            <h2 class="text-2xl text-white">Friendzone</h2>
        </a>

        <div class="flex flex-col items-center justify-center mt-8">
            <!-- Display profile picture, username, and email -->
            <img class="object-cover border border-gray-900 w-36 h-36 rounded-full" src="<?php echo $profilePic; ?>" alt="Profile Picture">
            <h4 class="mt-2 font-medium text-gray-100 dark:text-gray-200"><?php echo $username; ?></h4>
            <p class="mt-1 text-sm font-medium text-gray-100 dark:text-gray-400"><?php echo $email; ?></p>
        </div>

        <div class="flex justify-center mt-3">
        <nav class="flex justify-center my-2">
        

        <!-- Posts Button Form -->
<form action="posts.php" method="post" class="flex justify-center my-8">
    <button type="submit" name="posts" class="flex mr-2 justify-center items-center px-4 py-2 text-gray-700 hover:text-gray-100 transition-colors duration-300 transform rounded-lg bg-gray-100 hover:bg-gray-800">
        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M19 11H5M19 11C20.1046 11 21 11.8954 21 13V19C21 20.1046 20.1046 21 19 21H5C3.89543 21 3 20.1046 3 19V13C3 11.8954 3.89543 11 5 11M19 11V9C19 7.89543 18.1046 7 17 7M5 11V9C5 7.89543 5.89543 7 7 7M7 7V5C7 3.89543 7.89543 3 9 3H15C16.1046 3 17 3.89543 17 5V7M7 7H17" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        <span class="mx-4 font-medium">Posts</span>
    </button>
</form>

<!-- Friends Button Form -->
<form action="friends.php" method="post" class="flex justify-center my-8">
    <button type="submit" name="friends" class="flex ml-2 justify-center items-center px-4 py-2 text-gray-700 hover:text-gray-100 transition-colors duration-300 transform rounded-lg bg-gray-100 hover:bg-gray-800">
        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M16 7C16 9.20914 14.2091 11 12 11C9.79086 11 8 9.20914 8 7C8 4.79086 9.79086 3 12 3C14.2091 3 16 4.79086 16 7Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
            <path d="M12 14C8.13401 14 5 17.134 5 21H19C19 17.134 15.866 14 12 14Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
        <span class="mx-4 font-medium">Friends</span>
    </button>
</form>
</nav>
            
        </div>
        <form action="myposts.php" method="post" class="flex justify-center ">
    <button type="submit" name="myposts" class="flex justify-center items-center px-4 py-2 text-gray-700 hover:text-gray-100 transition-colors duration-300 transform rounded-lg bg-gray-100 hover:bg-gray-800 ">
        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path fill-rule="evenodd" clip-rule="evenodd" d="M3 4C3 3.44772 3.44772 3 4 3H10C10.5523 3 11 3.44772 11 4V10C11 10.5523 10.5523 11 10 11H4C3.44772 11 3 10.5523 3 10V4ZM13 3C13 3.55228 13.4477 4 14 4H20C20.5523 4 21 3.55228 21 3V9C21 9.55228 20.5523 10 20 10H14C13.4477 10 13 9.55228 13 9V3ZM3 13C3 12.4477 3.44772 12 4 12H10C10.5523 12 11 12.4477 11 13V19C11 19.5523 10.5523 20 10 20H4C3.44772 20 3 19.5523 3 19V13ZM14 13C14 12.4477 14.4477 12 15 12H21C21.5523 12 22 12.4477 22 13V19C22 19.5523 21.5523 20 21 20H15C14.4477 20 14 19.5523 14 19V13Z" fill="currentColor"/>
        </svg>
        <span class="mx-2 font-medium flex justify-center">My Posts</span>
    </button>
</form>
        <!-- Logout button -->
        <form action="logout.php" method="post" class="flex justify-center mt-12">
            <button type="submit" name="logout" class="flex justify-center items-center px-4 py-2 text-gray-100 bg-gray-900 transition-colors duration-300 transform rounded-lg dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 dark:hover:text-gray-200 hover:text-gray-700">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span class="mx-2 font-medium flex justify-center">Logout</span>
            </button>
        </form>
    </div>

    <!-- top div -->
    <div id="content-div" style="padding: 20px; flex-grow: 1;"></div>


</body>
</html>

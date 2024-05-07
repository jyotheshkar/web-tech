<!-- login.php -->


<?php
session_start();

if (isset($_SESSION['user_data'])) {
    header("Location: body.php");
    exit;
}

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");


?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Friendzone</title>
  <!-- Include Tailwind CSS -->
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">

  <style>
  /* Add custom styles for signup modal */
  #signupModal {
    z-index: 9999;
  }
  .blurred {
    filter: blur(10px);
  }

  /* Apply custom font family to all text elements */
  body {
    font-family: 'Barlow Condensed', sans-serif;
  }

  /* Optionally, specify different font family for headings */
  h1, h2, h3, h4, h5, h6 {
    font-family: 'Raleway', sans-serif;
  }
</style>

    <!-- Add font links -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@100;200;300;400;500;600;700;800;900&family=Raleway:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
</head>


<body class="h-screen bg-gray-200">


<!-- Navbar -->
<nav class="bg-gray-900 p-4 flex justify-between items-center">
<div class="flex items-center">
        <img class="w-auto px-2 bg-white py-2 h-10 sm:h-10 rounded-full mr-2" src="images/ficon.png" alt="">
        <div class="text-2xl text-gray-100 ">Friendzone</div>
    </div>
  <div>
  <a href="#" id="showSignupModal" class="text-black bg-gray-100 hover:text-white hover:bg-gray-900 hover:shadow-white hover:shadow-xl px-4 py-2 rounded-md text-md transition-colors duration-300">Sign Up</a>

  </div>
</nav>

<!-- Login Form Container -->
<div class="flex items-center justify-center mt-32"> <!-- Adjust margin-top here -->
  <div class="flex w-full max-w-sm mx-auto overflow-hidden bg-white rounded-lg shadow-xl shadow-blue-400  lg:max-w-4xl">
  <div class="hidden bg-cover bg-center lg:block lg:w-1/2" style="background-image: url('images/final.jpg');"></div>
    <div class="w-full px-6 py-8 md:px-8 lg:w-1/2">
      <div class="flex justify-center mx-auto">
        <img class="w-auto h-7 sm:h-8" src="images/ficon.png" alt="">
      </div>

      <p class="mt-3 text-xl text-center text-gray-600 dark:text-gray-200">
        Welcome to Friendzone!
      </p>

      <form method="post" method="post" action="logincode.php" class="mt-4">
        <div class="mt-4">
          <label class="block mb-2 text-sm font-medium text-gray-600 dark:text-gray-200" for="LoggingEmailAddress">Email Address</label>
          <input id="LoggingEmailAddress" name="email" class="block w-full px-4 py-2 text-gray-700 bg-white border rounded-lg dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 focus:border-blue-400 focus:ring-opacity-40 dark:focus:border-blue-300 focus:outline-none focus:ring focus:ring-blue-300" type="email" />
        </div>

        <div class="mt-4">
          <div class="flex justify-between">
            <label class="block mb-2 text-sm font-medium text-gray-600 dark:text-gray-200" for="loggingPassword">Password</label>

          </div>
          <input id="loggingPassword" name="password" class="block w-full px-4 py-2 text-gray-700 bg-white border rounded-lg dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 focus:border-blue-400 focus:ring-opacity-40 dark:focus:border-blue-300 focus:outline-none focus:ring focus:ring-blue-300" type="password" />
          <?php if (isset($_SESSION['login_error'])): ?>
<div style="color: red; margin: top 2px;">
    <?php
    echo htmlspecialchars($_SESSION['login_error']);
    // Unset the error message after displaying it so it doesn't show again on refresh.
    unset($_SESSION['login_error']);
    ?>
</div>
<?php endif; ?>
        </div>


        <div class="mt-6">
          <button type="submit" name="login" class="w-full px-6 py-3 text-sm font-medium tracking-wide text-white capitalize transition-colors duration-300 transform bg-gray-800 rounded-lg hover:bg-gray-700 focus:outline-none focus:ring focus:ring-gray-300 focus:ring-opacity-50">
            Sign In
          </button>
        </div>
      </form>

      <div class="flex items-center justify-between mt-4">
        <span class="w-1/5 border-b dark:border-gray-600 lg:w-1/4"></span>
        <a href="#" id="loginShowSignupModal" class="text-xs text-gray-500 uppercase dark:text-gray-400 hover:underline">or sign up</a>
        <span class="w-1/5 border-b dark:border-gray-600 lg:w-1/4"></span>
      </div>
    </div>
  </div>
</div>

  <!-- Signup Modal -->
  <div id="signupModal" class="fixed top-0 left-0 w-full h-full bg-black bg-opacity-50 flex items-center justify-center hidden">
    <div class="bg-white rounded-lg p-8 max-w-md w-full relative">
      <button class="absolute top-0 right-0 mr-4 mt-4 text-gray-600 hover:text-gray-800 focus:outline-none" onclick="closeSignupModal()">
        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
      </button>
      <h2 class="text-2xl font-semibold mb-4">Sign Up</h2>
      <form method="post" action="signupcode.php" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label for="username" class="block mb-2 text-sm font-medium text-gray-600 dark:text-gray-200">Username</label>
          <input id="username" name="username" type="text" class="block w-full px-4 py-2 text-gray-700 bg-white border rounded-lg focus:outline-none" required>
        </div>
        <div>
          <label for="profilePic" class="block mb-2 text-sm font-medium text-gray-600 dark:text-gray-200">Profile Picture</label>
          <input id="profilePic" name="profilePic" type="file" class="block w-full text-sm text-gray-500
            file:mr-4 file:py-2 file:px-4
            file:rounded-full file:border-0
            file:text-sm file:font-semibold
            file:bg-gray-50 file:text-gray-700
            hover:file:bg-gray-100
          ">
        </div>
    <div>
        <label class="block mb-2 text-sm font-medium text-gray-600 dark:text-gray-200" for="firstName">First Name</label>
        <input id="firstName" name="firstName" class="block w-full px-4 py-2 text-gray-700 bg-white border rounded-lg dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 focus:border-blue-400 focus:ring-opacity-40 dark:focus:border-blue-300 focus:outline-none focus:ring focus:ring-blue-300" type="text" />
      </div>
      <div>
        <label class="block mb-2 text-sm font-medium text-gray-600 dark:text-gray-200" for="lastName">Last Name</label>
        <input id="lastName" name="lastName" class="block w-full px-4 py-2 text-gray-700 bg-white border rounded-lg dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 focus:border-blue-400 focus:ring-opacity-40 dark:focus:border-blue-300 focus:outline-none focus:ring focus:ring-blue-300" type="text" />
      </div>
      <div>
        <label class="block mb-2 text-sm font-medium text-gray-600 dark:text-gray-200" for="emailAddress">Email address</label>
        <input id="emailAddress" name="email" class="block w-full px-4 py-2 text-gray-700 bg-white border rounded-lg dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 focus:border-blue-400 focus:ring-opacity-40 dark:focus:border-blue-300 focus:outline-none focus:ring focus:ring-blue-300" type="email" />
      </div>
      <div>
        <label class="block mb-2 text-sm font-medium text-gray-600 dark:text-gray-200" for="password">Password</label>
        <input id="password" name="password" class="block w-full px-4 py-2 text-gray-700 bg-white border rounded-lg dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 focus:border-blue-400 focus:ring-opacity-40 dark:focus:border-blue-300 focus:outline-none focus:ring focus:ring-blue-300" type="password" />
      </div>
      <div>
        <label class="block mb-2 text-sm font-medium text-gray-600 dark:text-gray-200" for="birthday">Birthday</label>
        <input id="birthday" name="birthday" class="block w-full px-4 py-2 text-gray-700 bg-white border rounded-lg dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 focus:border-blue-400 focus:ring-opacity-40 dark:focus:border-blue-300 focus:outline-none focus:ring focus:ring-blue-300" type="date" />
      </div>
      <div>
        <label class="block mb-2 text-sm font-medium text-gray-600 dark:text-gray-200" for="gender">Gender</label>
        <select id="gender" name="gender" class="block w-full px-4 py-2 text-gray-700 bg-white border rounded-lg dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 focus:border-blue-400 focus:ring-opacity-40 dark:focus:border-blue-300 focus:outline-none focus:ring focus:ring-blue-300">
          <option value="female">Female</option>
          <option value="male">Male</option>
          <option value="other">Other</option>
        </select>
      </div>
      <div class="mt-4">

      </div>
      <div class="mt-6">
        <button type="submit" name="signup" class="w-full px-6 py-3 text-sm font-medium tracking-wide text-white capitalize transition-colors duration-300 transform bg-gray-800 rounded-lg hover:bg-gray-700 focus:outline-none focus:ring focus:ring-gray-300 focus:ring-opacity-50">
          Sign Up
        </button>
      </div>
    </form>
  </div>
</div>

  <!-- JavaScript function to close the signup modal -->
  <!-- JavaScript to open/close modal and handle blur effect -->
  <script>
    function openSignupModal() {
      document.getElementById('signupModal').classList.remove('hidden');
      document.body.classList.add('body-with-modal-open');
    }

    function closeSignupModal() {
      document.getElementById('signupModal').classList.add('hidden');
      document.body.classList.remove('body-with-modal-open');
      location.reload(); // Reload the page
    }
  </script>

<!-- Forgot Password Modal -->
<div id="forgotPasswordModal" class="fixed inset-0 bg-black bg-opacity-50 h-full w-full flex justify-center items-center hidden">
  <div class="bg-white rounded-lg p-8 max-w-md w-full">
    <h2 class="text-2xl font-semibold mb-4">Reset Password</h2>
    <form id="forgotPasswordForm">
      <label class="block mb-2 text-sm font-medium text-gray-600" for="resetEmail">Email address</label>
      <input id="resetEmail" name="resetEmail" class="block w-full px-4 py-2 mb-4 text-gray-700 bg-white border rounded-lg focus:border-blue-400 focus:ring-opacity-40 focus:outline-none focus:ring focus:ring-blue-300" type="email" required />
      <button type="submit" class="w-full px-6 py-3 text-sm font-medium tracking-wide text-white capitalize transition-colors duration-300 transform bg-gray-800 rounded-lg hover:bg-gray-700 focus:outline-none focus:ring focus:ring-gray-300 focus:ring-opacity-50">
        Submit
      </button>
    </form>
  </div>
</div>

<!-- Success Message Modal -->
<div id="successModal" class="fixed inset-0 bg-black bg-opacity-50 h-full w-full flex justify-center items-center hidden">
  <div class="bg-white rounded-lg p-8 max-w-md w-full text-center">
    <svg class="mx-auto mb-4 w-10 h-10 text-green-600" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor">
      <path d="M5 13l4 4L19 7"></path>
    </svg>
    <h2 class="text-2xl font-semibold mb-4">Verification Link Sent Successfully!</h2>
    <p class="text-md text-gray-600">Please check your email to reset your password.</p>
  </div>
</div>


<script>
  // Function to show the signup modal and blur the background
  function showSignupModal() {
    // Blur the background elements
    document.querySelectorAll('body > :not(#signupModal)').forEach(function(element) {
      element.classList.add('blurred');
    });
    // Show the signup modal
    document.getElementById('signupModal').classList.remove('hidden');
  }

  // Function to hide the signup modal and remove blur from the background
  function hideSignupModal() {
    // Remove blur from the background elements
    document.querySelectorAll('body > :not(#signupModal)').forEach(function(element) {
      element.classList.remove('blurred');
    });
    // Hide the signup modal
    document.getElementById('signupModal').classList.add('hidden');
  }

  // Event listener for clicking on the signup button in the navbar
  document.getElementById('showSignupModal').addEventListener('click', function(event) {
    event.preventDefault();
    showSignupModal();
  });

  // Event listener for clicking on the or sign up button in the login form
  document.getElementById('loginShowSignupModal').addEventListener('click', function(event) {
    event.preventDefault();
    showSignupModal();
  });

  // Event listener for clicking on the close button or outside the modal to hide it
  document.getElementById('hideSignupModal').addEventListener('click', function(event) {
    event.preventDefault();
    hideSignupModal();
  });

  // Event listener for clicking outside the modal to hide it
  window.addEventListener('click', function(event) {
    if (event.target === document.getElementById('signupModal')) {
      hideSignupModal();
    }
  });


</script>


</body>
</html>
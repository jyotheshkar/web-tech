<!-- posts.php -->
<?php
session_start();
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

include 'db_connect.php';

if (!isset($_SESSION['user_data'])) {
    header("Location: login.php");
    exit;
}

if (isset($_SESSION['user_data'])) {
    $user = $_SESSION['user_data'];
    $username = isset($user['username']) ? htmlspecialchars($user['username']) : 'No Username';
    $profilePic = isset($user['profile_pic']) && !empty($user['profile_pic']) ? $user['profile_pic'] : 'images/default.png';
    $email = isset($user['email']) ? htmlspecialchars($user['email']) : 'No Email';
}



// Handle likes and unlikes
if (isset($_POST['like']) || isset($_POST['unlike'])) {
    $post_id = $_POST['post_id'];
    $user_id = $_SESSION['user_data']['user_id'];

    if (isset($_POST['like'])) {
        $sql = "INSERT INTO likes (user_id, post_id) SELECT ?, ? WHERE NOT EXISTS (SELECT 1 FROM likes WHERE user_id = ? AND post_id = ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiii", $user_id, $post_id, $user_id, $post_id);
    } else {
        $sql = "DELETE FROM likes WHERE user_id = ? AND post_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $user_id, $post_id);
    }

    if (!$stmt->execute()) {
        echo "Error updating like status: " . $stmt->error;
    }
    $stmt->close();
}

// Handle comments
if (isset($_POST['comment'])) {
    $post_id = $_POST['post_id'];
    $user_id = $_SESSION['user_data']['user_id'];
    $content = $_POST['comment_content'];
    $sql = "INSERT INTO comments (post_id, user_id, content) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iis", $post_id, $user_id, $content);
    $stmt->execute();
    $stmt->close();
}


if (isset($_POST['delete']) && $_POST['delete'] == 1) {
    $post_id = $_POST['post_id'];
    $user_id = $_SESSION['user_data']['user_id'];

    // Verify that the user attempting to delete the post is the owner
    $verify_sql = "SELECT user_id FROM posts WHERE post_id = ?";
    $verify_stmt = $conn->prepare($verify_sql);
    $verify_stmt->bind_param("i", $post_id);
    $verify_stmt->execute();
    $verify_result = $verify_stmt->get_result();
    if ($verify_data = $verify_result->fetch_assoc()) {
        if ($verify_data['user_id'] == $user_id) {
            // Begin transaction
            $conn->begin_transaction();
            try {
                // Delete comments associated with the post
                $delete_comments_sql = "DELETE FROM comments WHERE post_id = ?";
                $delete_comments_stmt = $conn->prepare($delete_comments_sql);
                $delete_comments_stmt->bind_param("i", $post_id);
                $delete_comments_stmt->execute();
                $delete_comments_stmt->close();

                // Delete likes associated with the post (if applicable)
                $delete_likes_sql = "DELETE FROM likes WHERE post_id = ?";
                $delete_likes_stmt = $conn->prepare($delete_likes_sql);
                $delete_likes_stmt->bind_param("i", $post_id);
                $delete_likes_stmt->execute();
                $delete_likes_stmt->close();

                // Finally, delete the post
                $delete_post_sql = "DELETE FROM posts WHERE post_id = ?";
                $delete_post_stmt = $conn->prepare($delete_post_sql);
                $delete_post_stmt->bind_param("i", $post_id);
                $delete_post_stmt->execute();
                $delete_post_stmt->close();

                // Commit transaction
                $conn->commit();

                echo "<script>alert('Post deleted successfully!'); window.location.href='posts.php';</script>";
            } catch (mysqli_sql_exception $exception) {
                // Rollback transaction if something goes wrong
                $conn->rollback();
                echo "Error deleting post: " . $exception->getMessage();
            }
        }
    }
    $verify_stmt->close();
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Posts</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">

    

</head>
<body class="bg-gray-200">

<nav class="sticky top-0 bg-gray-900 p-4 text-white flex justify-between items-center z-50">
    <div class="flex items-center">
        <img class="w-auto px-2 bg-white py-2 h-10 sm:h-10 rounded-full mr-2" src="images/ficon.png" alt="">
        <div class="text-lg font-semibold">Feed</div>
    </div>
    <a href="body.php" class="hover:bg-gray-700 bg-gray-100 text-gray-900 hover:text-gray-100 px-3 py-2 rounded text-sm transition-colors duration-200">Back</a>
</nav>

<div class="max-w-md mx-auto rounded-lg shadow-md mt-10">
    <!-- Red background section for header -->
    <div class="bg-gray-900 p-4 rounded-t-lg">
        <div class="flex items-center">
            <img class="w-10 h-10 rounded-full text-gray-100 border-white border-2 mr-4" src="<?php echo $profilePic; ?>" alt="Profile Picture">
            <h2 class="text-lg font-medium text-gray-100"><?php echo $username; ?></h2>
        </div>
    </div>

    <!-- White background section for the form -->
    <div class="bg-white p-6 rounded-b-lg">
        <form action="submit_post.php" method="post" enctype="multipart/form-data" class="space-y-4">
            <div class="flex items-center">
                <i class="fas fa-pencil-alt text-gray-900 mr-2"></i>
                <label for="postContent" class="block text-sm font-medium text-gray-700">What's on your mind?</label>
            </div>
            <input type="text" id="postContent" name="postContent" required class="mt-1 block w-full p-3 border border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50 placeholder-gray-500">
            
            <div class="flex items-center">
                <i class="fas fa-image text-gray-900 mr-2"></i>
                <label for="postImage" class="block text-sm font-medium text-gray-700">Upload Image:</label>
            </div>
            <input type="file" id="postImage" name="postImage" accept="image/*" class="mt-1 block w-full p-3 border border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50">
            
            <button type="submit" name="submitPost" class="w-full bg-gray-900 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline transition duration-300 ease-in-out">Post</button>
        </form>
    </div>
</div>

<?php
$sql = "SELECT p.content, p.image, p.post_id, p.user_id, u.username, u.profile_pic FROM posts p JOIN users u ON p.user_id = u.user_id ORDER BY p.created_at DESC";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Initialize variables
        $is_liked = false;
        $total_likes = 0;

        // Check if the current user has liked the post
        $like_check_sql = "SELECT COUNT(*) AS like_count FROM likes WHERE post_id = ? AND user_id = ?";
        $like_check_stmt = $conn->prepare($like_check_sql);
        $like_check_stmt->bind_param("ii", $row['post_id'], $_SESSION['user_data']['user_id']);
        $like_check_stmt->execute();
        $like_check_result = $like_check_stmt->get_result();
        if ($like_check_data = $like_check_result->fetch_assoc()) {
            $is_liked = $like_check_data['like_count'] > 0;
        }
        $like_check_stmt->close();

        // Fetch total likes for the post
        $total_likes_sql = "SELECT COUNT(*) AS total_likes FROM likes WHERE post_id = ?";
        $total_likes_stmt = $conn->prepare($total_likes_sql);
        $total_likes_stmt->bind_param("i", $row['post_id']);
        $total_likes_stmt->execute();
        $total_likes_result = $total_likes_stmt->get_result();
        if ($total_likes_data = $total_likes_result->fetch_assoc()) {
            $total_likes = $total_likes_data['total_likes'];
        }
        $total_likes_stmt->close();

        echo "<div class='max-w-md mx-auto bg-gray-50 p-4 rounded-lg shadow-lg mt-4 relative'>";

if ($row['user_id'] == $_SESSION['user_data']['user_id']) {
    echo "<form method='post' action='' class='delete-form absolute right-2 top-2'>";
    echo "<input type='hidden' name='delete' value='1'>";
    echo "<input type='hidden' name='post_id' value='" . $row['post_id'] . "'>";
    echo "<button type='submit' class='text-gray-600 hover:text-gray-800'>";
    echo "<svg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke-width='1.5' stroke='currentColor' class='w-6 h-6'>";
    echo "<path stroke-linecap='round' stroke-linejoin='round' d='M6 18L18 6M6 6l12 12' />";
    echo "</svg>";
    echo "</button>";
    echo "</form>";
}


        echo "<div class='flex items-center  space-x-4'>";
        echo "<img src='" . htmlspecialchars($row['profile_pic']) . "' alt='Profile Pic' class='w-12 h-12 rounded-full'>";
        echo "<h3 class='text-lg text-gray-900 font-bold'>" . htmlspecialchars($row['username']) . "</h3>";
        echo "</div>";
        if (!empty($row['image'])) {
            echo "<img src='" . htmlspecialchars($row['image']) . "' alt='Post Image' class='mt-3 rounded'>";
        }
        echo "<p class='mt-2 text-xl text-gray-600'>" . htmlspecialchars($row['content']) . "</p>";

// Like/Unlike and Comment form
echo "<div class='flex flex-col sm:flex-row justify-between  mt-4'>";
echo "<form method='post' action='' class='flex-grow mb-2 sm:mb-0' data-action='like' data-post-id='" . $row['post_id'] . "'>";

echo "<input type='hidden' name='post_id' value='" . $row['post_id'] . "'>";
if ($is_liked) {
    echo "<button type='submit' name='unlike' class='bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded mb-2 sm:mb-0 mr-2'>(" . $total_likes . ") <i class='fas fa-thumbs-up'></i></button>";
} else {
    echo "<button type='submit' name='like' class='bg-gray-900 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded mb-2 sm:mb-0 mr-2'>(" . $total_likes . ") <i class='far fa-thumbs-up'></i></button>";
}
echo "</form>";

echo "<form method='post' action='' class='flex-grow' data-action='comment' data-post-id='" . $row['post_id'] . "'>";
echo "<input type='hidden' name='post_id' value='" . $row['post_id'] . "'>";
echo "<input type='text' name='comment_content' id='commentContent' placeholder='Write a comment...' required oninvalid=\"this.setCustomValidity('Please write something in the comment!')\" oninput=\"this.setCustomValidity('')\" class='border border-gray-300 p-2 rounded flex-grow mb-2 sm:mb-0 mr-2'>";
echo "<button type='submit' name='comment' class='bg-gray-900 text-white font-bold py-2 px-4 rounded'><i class='far fa-comment'></i> Comment</button>";
echo "</form>";
echo "</div>"; // End of flex container



        // Display comments
        $commentSql = "SELECT c.content, u.username FROM comments c JOIN users u ON c.user_id = u.user_id WHERE c.post_id = ?";
        $commentStmt = $conn->prepare($commentSql);
        $commentStmt->bind_param("i", $row['post_id']);
        $commentStmt->execute();
        $commentResult = $commentStmt->get_result();
        if ($commentResult->num_rows > 0) {
            echo "<div class='comments-section mt-2 overflow-y-auto' style='max-height: 128px;'>";
            while ($comment = $commentResult->fetch_assoc()) {
                echo "<p><strong>" . htmlspecialchars($comment['username']) . ":</strong> " . htmlspecialchars($comment['content']) . "</p>";
            }
            echo "</div>";
        }

        echo "</div>"; // End of post div
    }
} else {
    echo "<p class='text-center text-gray-500 mt-10'>No posts yet!</p>";
}


if (isset($_POST['delete']) && $_POST['delete'] == 1) {
    $post_id = $_POST['post_id'];
    $user_id = $_SESSION['user_data']['user_id'];

    // Optional: Verify that the user attempting to delete the post is the owner
    $verify_sql = "SELECT user_id FROM posts WHERE post_id = ?";
    $verify_stmt = $conn->prepare($verify_sql);
    $verify_stmt->bind_param("i", $post_id);
    $verify_stmt->execute();
    $verify_result = $verify_stmt->get_result();
    if ($verify_data = $verify_result->fetch_assoc()) {
        if ($verify_data['user_id'] == $user_id) {
            // Start transaction
            $conn->begin_transaction();

            try {
                // Delete likes
                $delete_likes_sql = "DELETE FROM likes WHERE post_id = ?";
                $delete_likes_stmt = $conn->prepare($delete_likes_sql);
                $delete_likes_stmt->bind_param("i", $post_id);
                $delete_likes_stmt->execute();
                $delete_likes_stmt->close();

                // Delete comments
                $delete_comments_sql = "DELETE FROM comments WHERE post_id = ?";
                $delete_comments_stmt = $conn->prepare($delete_comments_sql);
                $delete_comments_stmt->bind_param("i", $post_id);
                $delete_comments_stmt->execute();
                $delete_comments_stmt->close();

                // Delete the post
                $delete_sql = "DELETE FROM posts WHERE post_id = ?";
                $delete_stmt = $conn->prepare($delete_sql);
                $delete_stmt->bind_param("i", $post_id);
                $delete_stmt->execute();
                $delete_stmt->close();

                // Commit transaction
                $conn->commit();

                // Redirect or output success message
                echo "<script>alert('Post deleted successfully!'); window.location.href='posts.php';</script>";
            } catch (mysqli_sql_exception $exception) {
                // Rollback transaction if something goes wrong
                $conn->rollback();
                echo "Error deleting post: " . $exception->getMessage();
            }
        }
    }
    $verify_stmt->close();
}



?>


<script>
document.addEventListener('DOMContentLoaded', function() {
    event.preventDefault();
    // Function to handle like/unlike and comment asynchronously
    function handleInteraction(action, postId) {
        event.preventDefault();
        // Find the form corresponding to the action
        const form = document.querySelector(`form[data-action="${action}"][data-post-id="${postId}"]`);
        
        // Serialize form data
        const formData = new FormData(form);

        // Send asynchronous request
        fetch(form.action, {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            // Update UI if needed
            console.log(data); // Log response for debugging
            // You can update UI here if necessary, like updating the like count or refreshing the comments
            // For now, let's refresh the page after an interaction to see the changes
            window.location.reload();
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }

    // Add event listener to all like and comment buttons
    document.querySelectorAll('.like-button, .comment-button').forEach(button => {
        event.preventDefault();
        button.addEventListener('click', function(event) {
            // Prevent default button click behavior
            event.preventDefault();

            // Get the action (like or comment) and post ID from the button's data attributes
            const action = this.dataset.action;
            const postId = this.dataset.postId;

            // Call the handleInteraction function with the action and post ID
            handleInteraction(action, postId);
        });
    });

    // Add event listener to all forms with the class 'delete-form'
    document.querySelectorAll('.delete-form').forEach(form => {
        form.addEventListener('submit', function(event) {
            // Show confirmation dialog
            var confirmDeletion = confirm("Are you sure you want to delete this post?");
            if (!confirmDeletion) {
                // If the user clicks 'Cancel', prevent the form from submitting
                event.preventDefault();
            }
        });
    });
});
</script>


<script>
document.addEventListener('DOMContentLoaded', function() {
    var commentForm = document.querySelector('form[data-action="comment"]');
    commentForm.addEventListener('submit', function(event) {
        var commentInput = document.getElementById('commentContent');
        if (commentInput.value.trim() === '') {
            event.preventDefault(); // Stop the form from submitting
            alert('Please type something in the comment!'); // Alert the user
            commentInput.focus(); // Optionally, put the focus back to the comment input
        }
    });
});
</script>




</body>
</html>
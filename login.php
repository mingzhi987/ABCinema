<?php
session_start();

require 'dbconnection.php';

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
echo "<script>console.log('connection success!')</script>";
// Close the connection when done
$conn->close();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="all_styles.css">
</head>
<body>
    <div class="header-content" style="text-align: center;">
        <h2>Sign In / Sign Up</h2>

        <!-- Insert logo here -->

        <!-- Insert navbar here -->
        <div class="navbar-header">
        </div>

        <!-- Insert sign in account here -->
    </div>

    <div class="body-content">
        <div class="header">
            <h2>Sign In / Sign Up</h2>
        </div>
        <div class="sign-up-in-form">
            <h3>Sign In</h3>
            <form method="post" action="">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required>
                <br>
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
                <br>
                <button type="submit" name="login">Login</button>
            </form>
        </div>
        <br/>
        <p>New here? <a href="sign_up.php">Sign up here</a></p>
        <p>Forgot your password? <a href="#" id="forget_password">Forget Password?</a></p>
    </div>
    
    <div class="footer-content">
        <p>&copy; 2021</p>
    </div>


    <?php
    // Only proceed with the login check if form is submitted
    if (isset($_POST['login'])) {
        // Create connection
        $conn = new mysqli($servername, $username, $password, $database);

        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Retrieve form data
        $user = $_POST['username'];
        $pass = $_POST['password'];

        // SQL query to check if the user exists
        $sql = "SELECT * FROM useraccount WHERE username = ? AND password = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $user, $pass);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // User found

            $row = $result->fetch_assoc();

            // Generate a secure login token
            $login_token = bin2hex(random_bytes(16));

            // Store the login token in the database
            $update_sql = "UPDATE useraccount SET login_token = ? WHERE UserID = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("si", $login_token, $row['UserID']);
            $update_stmt->execute();

            // Store the login token in the session
            $_SESSION['token_id'] = $login_token;

            // JavaScript alert and redirect
            echo "<script>
                alert('Login successful! Welcome, " . htmlspecialchars($user) . "');
                window.location.href = 'profile.php';
            </script>";
        } else {
            // User not found
            echo "<p>Invalid username or password. Please try again.</p>";
        }

        // Close the statement and connection
        $stmt->close();
        $conn->close();
    }
    ?>
    <script>
       document.getElementById('forget_password').addEventListener('click', function() {
            var email = prompt("Please enter your email:");
            if (email) {
                // Send AJAX request to render the email content
                var xhr = new XMLHttpRequest();
                xhr.open("POST", "send_reset_email.php", true);
                xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                
                xhr.onreadystatechange = function() {
                    if (xhr.readyState === 4) { // Request is complete
                        if (xhr.status === 200) { // HTTP status OK
                            var newWindow = window.open('', '_blank'); // Open a new window
                            if (newWindow) { // Check if the new window was opened successfully
                                newWindow.document.write(xhr.responseText); // Write the response to the new window
                                newWindow.document.close(); // Close the document to render the HTML
                            } else {
                                console.error("Failed to open a new window. Please check your popup settings.");
                            }
                        } else {
                            console.error("Error: " + xhr.status + " - " + xhr.statusText);
                        }
                    }
                };
                xhr.send("email=" + encodeURIComponent(email));
            }
        });
    </script>
</body>
</html>

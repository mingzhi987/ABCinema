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
    <link rel="stylesheet" href="abcmovies.css">
    <link rel="stylesheet" href="all_styles.css">
    <link rel="stylesheet" href="about_us.css">
    <script src="footerAdjuster.js"></script>
</head>
<body>
    <!-- Nav bar -->
    <div class="header">
        <div>
            <a href="movies.php">
                <img class=logo src="images/logo/logo.png">
            </a>
            <div class="header-left">
                <a href="movies.php" alt="Movies">Movies</a>
                <a href="about_us.html" alt="About us">About us</a>
            </div>
        </div>
        <div class="header-right">
            <a href="checkout.php"><img src="images/icons/basket.svg" alt="Checkout" /></a>
            <?php if (isset($_SESSION['token_id'])): ?>
                <a href="profile.php"><img src="images/icons/profile.svg" alt="Profile" /></a>
            <?php else: ?>
                <a href="login.php"><img src="images/icons/profile.svg" alt="Login" /></a>
            <?php endif; ?>
        </div>
    </div>
    

    <div class="body-content">
        <h1 style="margin-top: 30px; margin-bottom: 30px; text-transform: uppercase;">Sign In</h1>
        <div class="sign-up-in-form">
            <h2 style="margin-top: 30px;"><u>Sign In</u></h2>
            <form id="sign_in" method="post" action="login.php">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required>
                
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
                
                <button type="submit" name="login">Login</button>
            </form>
            <br/>
            <p>New here? <a href="sign_up.php">Sign up here</a></p>
            <p style="margin-bottom: 30px; margin-top: 20px;">Forgot your password? <a href="#" id="forget_password">Forget Password?</a></p>
        </div>
        <br/>
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
            echo "<script>alert('Invalid username or password. Please try again.')</script>";
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
                var form = document.createElement('form');
                form.method = 'post';
                form.action = 'send_reset_email.php';
                var input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'email';
                input.value = email;
                form.appendChild(input);
                document.body.appendChild(form);
                form.submit();
            }
        });
    </script>
   <!-- Footer -->
   <footer id="footer">
        <div class="footer-container">
            <div class="row">
                <div class="column-1"><img class="logo" src="images/logo/logo.png">
                    <div class="footer-summary">Welcome to ABCinema, a modern cinema delivering immersive experiences with top-notch visuals, sound, and cosy seating. Discover blockbusters, indie films, and local gems—all designed to captivate and inspire.
                    </div>
                </div>

                <div class="column-2">
                    <h2>Links</h2>
                        <p class="footer-links"><a href="http://demo.amytheme.com/movie/demo/elementor-single-cinema/" aria-current="page">Home</a></p>
                        <p class="footer-links"><a href="http://demo.amytheme.com/movie/demo/elementor-single-cinema/coming-soon/">Movies</a></p>
                        <p class="footer-links"><a href="http://demo.amytheme.com/movie/demo/elementor-single-cinema/top-rated/">About us</a></p>
                </div>

                <div class="column-2">
                    <h2>Contact Us</h2>
                    234 Film Street #01-01 <br> Singapore 690234
                    <p>contact@abcinema.com.sg
                    <p>+65 6349 8203
                </div>

                <div class="column-2">
                    <h2>Follow Us</h2>
                    <img id="footer-icons" src="images/icons/twitter-x.svg" alt="X (Twitter)" />
                    <img id="footer-icons" src="images/icons/facebook.svg" alt="Facebook" />
                    <img id="footer-icons" src="images/icons/instagram.svg" alt="Instagram" />
                </div>
            </div>
        </div>
        </div>
    </footer>
</body>
</html>

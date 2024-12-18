<?php
    require "dbconnection.php";
    // Process the signup form when submitted
    if (isset($_POST['signup'])) {

        // Create a connection
        $conn = new mysqli($servername, $username, $password, $database);

        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Retrieve and sanitize form data
        $new_user = $_POST['username'];
        //$new_pass = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash the password

         // Retrieve and sanitize form data
        $new_user = trim($_POST['username']);
        $new_email = trim($_POST['email']);
        $new_fullname = trim($_POST['fullname']);
        $new_dob = $_POST['dob'];
        $new_pass = trim($_POST['password']); // Hash the password

        // Input validation
        $errors = [];

        if (empty($new_user)) {
            $errors[] = "Username is required.";
        }
    
        if (empty($new_email) || !filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "A valid email is required.";
        }
    
        if (empty($new_fullname)) {
            $errors[] = "Full name is required.";
        }
    
        if (empty($new_dob)) {
            $errors[] = "Date of birth is required.";
        }
    
        if (empty($_POST['password'])) {
            $errors[] = "Password is required.";
        }
    
        if (count($errors) > 0) {
            foreach ($errors as $error) {
                echo "<script>alert('$error');</script>";
            }
        } else{
            // Check if username already exists
            $check_sql = "SELECT * FROM useraccount WHERE username = ?";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("s", $new_user);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();

            if ($check_result->num_rows > 0) {
                echo "<p>Username already taken. Please choose a different one.</p>";
            } else {
                // Insert new user into database
                $sql = "INSERT INTO useraccount (Username, Email, FullName, DateOfBirth, Password) VALUES (?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sssss", $new_user, $new_email, $new_fullname, $new_dob, $new_pass);

                if ($stmt->execute()) {
                    echo "<script>alert('Sign up successful! You will be redirected to the login page.'); window.location.href='login.php';</script>";
                } else {
                    echo "<script>alert('Error: " . $stmt->error . "');</script>";
                }

                // Close the statement
                $stmt->close();

            }
           
            $check_stmt->close();

        }
        $conn->close();
    }
    ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sign Up</title>
    <link rel="stylesheet" href="abcmovies.css">
    <link rel="stylesheet" href="all_styles.css">
    <link rel="stylesheet" href="about_us.css">
    <script src="footerAdjuster.js"></script>
</head>
<body>
    <!-- Nav bar -->
    <div class="header" style="padding-bottom: 5px;">
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
    <h1 style = "margin-top: 30px; margin-bottom: 30px; text-transform: uppercase;">Sign Up</h1>
        <div class="sign-up-in-form">
            <h2 style="margin-top: 30px; text-decoration: underline;">Sign Up</h2>
            <form id="sign_in" method="post" action="">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required>
            
                <label for="email">Email:</label>
                <input type="text" id="email" name="email" required>
               
                <label for="fullname">Full Name:</label>
                <input type="text" id="fullname" name="fullname" required>
              
                <label for="dob">Date of Birth:</label>
                <input type="date" id="dob" name="dob" required>
                
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
                
                <button type="submit" name="signup">Sign Up</button>
            </form>

            <p style="margin-bottom: 30px;">Already have an account? <a href="login.php">Back to log in</a></p>
        </div>
    </div>
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

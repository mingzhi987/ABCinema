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
    <link rel="stylesheet" href="all_styles.css">
</head>
<body>
    <div class="header-content" style="text-align: center;">
            <h2>Sign In / Sign Up</h2>

            <!-- Insert logo here -->

            <!-- Insert navbar here -->
            <div class="navbar-header">
            </div>

    </div>
    <div class="body-content">
    <div class="header"><h1>Sign Up</h1></div>
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
    <br/>
    <div class="footer-content">
        <p>ABCinema, &copy; 2021</p>
    </div>
</body>
</html>

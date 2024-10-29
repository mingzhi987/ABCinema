<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sign Up</title>
</head>
<body>
    <h2>Sign Up</h2>
    <form method="post" action="">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required>
        <br>
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>
        <br>
        <button type="submit" name="signup">Sign Up</button>
    </form>
    <br/>
    <a href="login.php">Back to log in</a>

    <?php
    // Process the signup form when submitted
    if (isset($_POST['signup'])) {
        // Database credentials
        $servername = "localhost";
        $username = "your_username";
        $password = "your_password";
        $database = "your_database";

        // Create a connection
        $conn = new mysqli($servername, $username, $password, $database);

        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Retrieve and sanitize form data
        $new_user = $_POST['username'];
        $new_pass = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash the password

        // Check if username already exists
        $check_sql = "SELECT * FROM useraccounts WHERE username = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("s", $new_user);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {
            echo "<p>Username already taken. Please choose a different one.</p>";
        } else {
            // Insert new user into database
            $sql = "INSERT INTO useraccounts (username, password) VALUES (?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $new_user, $new_pass);

            if ($stmt->execute()) {
                echo "<p>Sign up successful! You can now <a href='login.php'>login</a>.</p>";
            } else {
                echo "<p>Error: " . $stmt->error . "</p>";
            }

            // Close the statement
            $stmt->close();
        }

        // Close the check statement and connection
        $check_stmt->close();
        $conn->close();
    }
    ?>
</body>
</html>

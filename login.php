<?php
session_start();

// Database credentials
$servername = "localhost";  // or your server's IP address
$username = "root"; // replace with your MySQL username
$password = ""; // replace with your MySQL password
$database = "abcinema_db"; // replace with your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
echo "Connected successfully";



// SQL query to fetch data from useraccounts
$sql = "SELECT * FROM useraccount";
$result = $conn->query($sql);

// // Check if there are results and display them
// if ($result->num_rows > 0) {
//     // Output data of each row
//     while($row = $result->fetch_assoc()) {
//         echo "<pre>";
//         print_r($row);  // Prints each row for debugging
//         echo "</pre>";
//     }
// } else {
//     echo "No results found.";
// }


// Close the connection when done
$conn->close();


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
</head>
<body>
    <h2>Login</h2>
    <form method="post" action="">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required>
        <br>
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>
        <br>
        <button type="submit" name="login">Login</button>
    </form>
    <br/>
    <a href="sign_up.php">Sign up here</a>

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

            $token_id = bin2hex(random_bytes(16)); // Generate a random token

            echo "<p>Login successful! Welcome, " . htmlspecialchars($user) . ".</p>";
        } else {
            // User not found
            echo "<p>Invalid username or password. Please try again.</p>";
        }

        // Close the statement and connection
        $stmt->close();
        $conn->close();
    }
    ?>
</body>
</html>

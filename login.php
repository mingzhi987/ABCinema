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
    <br/>
    <a href="#" id="forget_password">Forget Password?</a>
    
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
            echo "<li>" . htmlspecialchars($row["UserID"]) . "</li>";
           
            $token_id = $row["UserID"] . ":" . $row["Username"]; // Generate a random token
            #$_SESSION['token_id'] = $token_id; // Store the token in the session

            $_SESSION['token_id'] = $token_id; //can store alr

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

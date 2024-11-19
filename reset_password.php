<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $new_password = $_POST['new_password'];
    $token = $_POST['token'];

    // Decrypt the email
    $decrypted_email = base64_decode($token);

    // Database credentials
    $servername = "localhost";  // or your server's IP address
    $username = "root"; // replace with your MySQL username
    $password = ""; // replace with your MySQL password
    $database = "abcinema_db"; // replace with your database name

    // Database connection
    $conn = new mysqli($servername, $username, $password, $database);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Validate email
    $sql = "SELECT UserID FROM useraccount WHERE Email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $decrypted_email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Update password
        //$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $sql = "UPDATE useraccount SET Password = ? WHERE Email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $new_password, $decrypted_email);
        $stmt->execute();

        echo "<script>
                alert('Password has been reset successfully');
                window.location.href = 'login.php';
              </script>";
    } else {
        echo "<script>alert('Invalid token or email');</script>";
    }

    $stmt->close();
    $conn->close();
} else {
    $token = $_GET['token'];
    echo '
    <form method="post" action="">
        <input type="hidden" name="token" value="' . htmlspecialchars($token) . '">
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required>
        <br>
        <label for="new_password">New Password:</label>
        <input type="password" id="new_password" name="new_password" required>
        <br>
        <button type="submit">Reset Password</button>
    </form>
    ';
}
?>
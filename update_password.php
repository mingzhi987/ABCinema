<?php
session_start(); // Start a session to store error messages if needed

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var(trim($_POST['email']), filter: FILTER_SANITIZE_EMAIL);
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate password confirmation
    if ($new_password !== $confirm_password) {
        echo "<script>alert('Passwords do not match!'); window.location.href='login.php';</script>";
        exit;
    }

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

    // Validate token and email before updating the password
    $sql = "SELECT UserID FROM useraccount WHERE Email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Update password
        //$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $sql = "UPDATE useraccount SET Password = ? WHERE Email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $new_password, $email);
        if ($stmt->execute()) {
            echo "<script>alert('Password has been reset successfully'); window.location.href='login.php';</script>";
        } else {
            echo "Error updating password.";
        }
    } else {
        echo "Invalid token or email.";
    }

    $stmt->close();
    $conn->close();
} else {
    echo "<script>alert('Invalid request method.'); window.location.href='login.php';</script>";
}
?>
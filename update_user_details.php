<?php

require 'dbconnection.php';

session_start();

if (!isset($_SESSION['token_id'])) {
    header("Location: login.php");
    exit;
}

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Retrieve user details using the login token
$login_token = $_SESSION['token_id'];
$sql = "SELECT * FROM useraccount WHERE login_token = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $login_token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $userid = $user['UserID'];
} else {
    // Invalid token, redirect to login page
    header("Location: login.php");
    exit;
}


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $newEmail = isset($_POST['email']) && $_POST['email'] !== "" ? $_POST['email'] : null;
    $newPassword = isset($_POST['password']) && $_POST['password'] !== "" ? $_POST['password'] : null;

    if (!$newEmail && !$newPassword) {
        echo "No changes detected.";
        exit;
    }

    $queryParts = [];
    $params = [];
    

    if ($newEmail) {
        $queryParts[] = "Email = ?";
        $params[] = $newEmail;
    }

    if ($newPassword) {
        #$hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
        $queryParts[] = "Password = ?";
        $params[] = $newPassword;
    }

    $query = "UPDATE useraccount SET " . implode(", ", $queryParts) . " WHERE UserID = ". $userid ."";

    $stmt = $conn->prepare($query);
    $stmt->bind_param(str_repeat("s", count($params)), ...$params);

    if ($stmt->execute()) {
        echo "User details updated successfully";
    } else {
        echo "<script>
            alert('Failed to update, please try again.');
        </script>";
    }

    $stmt->close();
    $conn->close();
}
?>
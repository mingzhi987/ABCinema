<?php
session_start();
if (!isset($_SESSION['token_id'])) {
    echo "User not logged in.";
    exit;
}

$token_id = $_SESSION['token_id'];
// Split the token_id to get userid and username
list($userid, $username) = explode(":", $token_id);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $newEmail = isset($_POST['email']) && $_POST['email'] !== "" ? $_POST['email'] : null;
    $newPassword = isset($_POST['password']) && $_POST['password'] !== "" ? $_POST['password'] : null;

    if (!$newEmail && !$newPassword) {
        echo "No changes detected.";
        exit;
    }

    // Database connection
    $servername = "localhost";
    $username = "root";
    $password = "";
    $database = "abcinema_db";
    $conn = new mysqli($servername, $username, $password, $database);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    

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
        echo "Details updated successfully.";
    } else {
        echo "Failed to update details.";
    }

    $stmt->close();
    $conn->close();
}
?>
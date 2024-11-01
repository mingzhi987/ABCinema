<?php
    session_start();
    require 'dbconnection.php';

    // Check if the user is logged in
    if (isset($_SESSION['token_id'])) {

        // Create connection
        $conn = new mysqli($servername, $username, $password, $database);

        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Retrieve the login token from the session
        $login_token = $_SESSION['token_id'];

        // Update the useraccount table to set the login_token to NULL
        $sql = "UPDATE useraccount SET login_token = NULL WHERE login_token = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $login_token);
        $stmt->execute();

        // Close the statement and connection
        $stmt->close();
        $conn->close();
    }

    session_unset();
    session_destroy();
    header("Location: movies.php"); // Redirect to login page
    exit;
?>
<?php
include 'db_connection.php'; // Include your database connection file

// Create a connection to the MySQL database
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_GET['MovieID'])) {
    $movieID = $_GET['MovieID'];

    // Delete the movie from the database
    $stmt = $conn->prepare("DELETE FROM movies WHERE MovieID = ?");
    $stmt->bind_param("i", $movieID);
    $stmt->execute();

    echo "<script>alert('Movie deleted successfully!'); window.location.href='admin.php';</script>";
}
?>
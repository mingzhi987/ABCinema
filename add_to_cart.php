<?php
session_start();
// Database connection
$servername = "localhost";  // Replace with your MySQL server name
$username = "root";         // Replace with your MySQL username
$password = "";             // Replace with your MySQL password
$dbname = "abcinema";  // Replace with your database name

$movie_id = isset($_GET['movie_id']) ? (int)$_GET['movie_id'] : 0;

// Create new shopping cart ID if it doesn't exist
if (!isset($_SESSION['ShoppingCartID'])) {
    $_SESSION['ShoppingCartID'] = rand(1000, 9999); // Generate unique ID
    setcookie("ShoppingCartID", $_SESSION['ShoppingCartID'], time() + (86400 * 30), "/"); // 30-day cookie
}

// Insert item into shopping cart (example query - adjust as needed)
$query = "INSERT INTO ShoppingCart (ShoppingCartID, MovieID) VALUES ('{$_SESSION['ShoppingCartID']}', $movie_id)";
if ($conn->query($query) === TRUE) {
    echo '<script>alert("Item added to cart successfully!")</script>';
} else {
    echo '<script>alert("Error: " . $conn->error)</script>';
}

$conn->close();

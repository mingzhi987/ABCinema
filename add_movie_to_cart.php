<?php
session_start();
require 'dbconnection.php';

// Check if the user is logged in
if (!isset($_SESSION['token_id'])) {
    // Redirect to login page if not logged in
    header("Location: login.php");
    exit;
}

// Retrieve form data
$screening_date = $_POST['screening_date'];
$selected_seats = isset($_POST['seats']) ? $_POST['seats'] : [];

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Retrieve user details using the login token
$login_token = $_SESSION['token_id'];
$sql = "SELECT UserID FROM useraccount WHERE login_token = ?";
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

// Check if the user has an existing shopping cart, if not create one
$sql = "SELECT ShoppingCartID FROM shoppingcart WHERE UserID = ?";
$stmt = $conn->prepare(query: $sql);
$stmt->bind_param("i", $userid);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $shoppingCart = $result->fetch_assoc();
    $shoppingCartID = $shoppingCart['ShoppingCartID'];
} else {
    $sql = "INSERT INTO shoppingcart (TotalPrice, UserID) VALUES (0.00, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userid);
    $stmt->execute();
    $shoppingCartID = $stmt->insert_id;
}

// // Store the selected screening date in the shoppingscreening table
// $sql = "INSERT INTO shoppingscreening (ShoppingCartID, ScreenTimeID) VALUES (?, ?)";
// $stmt = $conn->prepare($sql);
// $stmt->bind_param("ii", $shoppingCartID, $screening_date);
// $stmt->execute();

// Update the BookingState of the selected seats in the seating table, so that other users will not be able to see it
foreach ($selected_seats as $seatID) {
    $sql = "INSERT INTO shoppingscreening (ShoppingCartID, ScreenTimeID, SeatID) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $shoppingCartID, $screening_date, $seatID);
    $stmt->execute();
}

$stmt->close();
$conn->close();

// Redirect to profile page for now (by right will redirect back to index page which is not here yet)
echo "<script>
        alert('Added to Cart successfully!');
        window.location.href = 'profile.php'; 
      </script>";
?>
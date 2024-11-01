<?php
session_start();
require 'dbconnection.php';

// Check if the user is logged in
if (!isset($_SESSION['token_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'You must be logged in to make a booking.']);
    exit;
}

// Create a connection to the MySQL database
$conn = new mysqli($servername, $username, $password, $database);


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
    echo json_encode(['status' => 'error', 'message' => 'Invalid login token.']);
    exit;
}

// Retrieve the shopping cart linked to the user
$sql = "SELECT ShoppingCartID FROM shoppingcart WHERE UserID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userid);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $shoppingCart = $result->fetch_assoc();
    $shoppingCartID = $shoppingCart['ShoppingCartID'];
} else {
    echo json_encode(['status' => 'error', 'message' => 'No items in the shopping cart.']);
    exit;
}

// Retrieve cart items from the request
$cartItems = json_decode(file_get_contents('php://input'), true);

if (empty($cartItems)) {
    echo json_encode(['status' => 'error', 'message' => 'No cart items provided.']);
    exit;
}

// Insert each cart item into the bookings table
foreach ($cartItems as $item) {
    $sql = "INSERT INTO booking (PaymentDate, UserID, ShoppingCartID, MovieName, Showtime, CinemaHall, SeatNo, Price) 
            VALUES (NOW(), ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iissssd", $userid, $shoppingCartID, $item['MovieName'], $item['ScreenTimeDate'], $item['CinemaHall'], $item['SeatNumber'], $item['ScreenTimeCost']);
    $stmt->execute();
}

$stmt->close();
$conn->close();

echo json_encode(['status' => 'success', 'message' => 'Bookings inserted successfully.']);
?>
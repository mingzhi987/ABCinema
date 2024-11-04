<?php
session_start();
require 'dbconnection.php';

// Check if the user is logged in
if (!isset($_SESSION['token_id'])) {
    header('Location: login.php');
    exit;
}

echo "<script>console.log('you're in insert_bookings.php')</script>";

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
    echo "<script>alert('Invalid login token.'); window.location.href='login.php';</script>";
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
    echo "<script>alert('No items in the shopping cart.'); window.location.href='checkout.php';</script>";
    exit;
}

// Retrieve cart items from the request
$cartItems = json_decode($_POST['cartItems'], true);

if (empty($cartItems)) {
    echo "<script>alert('No cart items provided.'); window.location.href='checkout.php';</script>";
    exit;
}


$newBookingsInserted = false;
$newBookingIDs = [];

// Insert each cart item into the bookings table
foreach ($cartItems as $item) {
    // Check for identical entries in the booking table
    $check_sql = "SELECT * FROM booking WHERE UserID = ? AND ShoppingCartID = ? AND MovieName = ? AND Showtime = ? AND CinemaID = ? AND SeatID = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("iissss", $userid, $shoppingCartID, $item['MovieName'], $item['ScreenTimeID'], $item['CinemaID'], $item['SeatID']);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        // Duplicate entry found, skip insertion
        continue;
    }

    // Insert the booking if no duplicate is found
    $sql = "INSERT INTO booking (PaymentDate, UserID, ShoppingCartID, MovieName, Showtime, CinemaID, SeatID, Price) 
            VALUES (NOW(), ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iissssd", $userid, $shoppingCartID, $item['MovieName'], $item['ScreenTimeID'], $item['CinemaID'], $item['SeatID'], $item['ScreenTimeCost']);
    if (!$stmt->execute()) {
        echo "<script>alert('Error inserting booking: " . $stmt->error . "'); window.location.href='checkout.php';</script>";
        exit;
    }
    $newBookingsInserted = true;
    $newBookingIDs[] = $conn->insert_id; // Store the new booking ID
}

if ($newBookingsInserted) {
    $sql = "DELETE FROM shoppingcart WHERE ShoppingCartID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $shoppingCartID);
    if (!$stmt->execute()) {
        echo "<script>alert('Error deleting shopping cart: " . $stmt->error . "'); window.location.href='checkout.php';</script>";
        exit;
    }

    // Redirect to email_send_tester.php with the new booking IDs
    $_SESSION['newBookingIDs'] = $newBookingIDs;
     echo "<script>alert('Bookings inserted successfully.'); window.location.href='email_send_tester.php';</script>";
    exit;
} else {
    echo "<script>alert('No new bookings were inserted due to duplicates.'); window.location.href='checkout.php';</script>";
}

$stmt->close();
$conn->close();

?>
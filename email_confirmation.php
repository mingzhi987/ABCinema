<?php
session_start();

require 'dbconnection.php';

// Retrieve ShoppingCartID and user details
$shoppingCartID = isset($_SESSION['ShoppingCartID']) ? $_SESSION['ShoppingCartID'] : 0;
$userQuery = "SELECT u.Username, u.Email 
              FROM useraccount AS u 
              JOIN shoppingcart AS sc ON sc.UserID = u.UserID 
              WHERE sc.ShoppingCartID = '$shoppingCartID'";
$userResult = $conn->query($userQuery);
$user = $userResult->fetch_assoc();

// Retrieve shopping cart items
$cartQuery = "SELECT st.ScreeningMovie, st.ScreenTimeID, st.ScreenTimeDate, st.ScreenTimeCost, st.SeatingLocation
              FROM screeningtime2 AS st
              JOIN shoppingcart AS sc ON sc.ShoppingCartID = '$shoppingCartID'
              WHERE st.ScreenTimeID = sc.ScreenTimeID";
$cartResult = $conn->query($cartQuery);

// Email content
$emailTo = $user['Email'];
$emailSubject = "Thank you! Receipt No: " . $shoppingCartID;
$emailMessage = "Dear " . $user['Username'] . ",\n\nYour booking details are as follows:\n\n";

while ($item = $cartResult->fetch_assoc()) {
    $emailMessage .= "ScreeningMovie: " . $item['ScreeningMovie'] . "\n";
    $emailMessage .= "ScreenTimeID: " . $item['ScreenTimeID'] . "\n";
    $emailMessage .= "ScreenTimeDate: " . $item['ScreenTimeDate'] . "\n";
    $emailMessage .= "ScreenTimeCost: $" . $item['ScreenTimeCost'] . "\n";
    $emailMessage .= "SeatingLocation: " . $item['SeatingLocation'] . "\n\n";
}

$emailMessage .= "Total Price: $" . number_format($totalPrice, 2) . "\n\nThank you for booking with us!\n\n";

// Send email
$headers = "From: no-reply@cinema.com\r\n";
if (mail($emailTo, $emailSubject, $emailMessage, $headers)) {
    echo "Confirmation email sent successfully to " . htmlspecialchars($emailTo);
} else {
    echo "Failed to send confirmation email.";
}

$conn->close();

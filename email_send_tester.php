<?php
session_start();

//install "composer", then composer init, composer install, composer require phpmailer/phpmailer
require 'vendor/autoload.php';
require 'dbconnection.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);


if (!isset($_SESSION['token_id'])) {
    header("Location: movies.php");
    exit; // Redirect to login if not logged in
}

// Create a connection to the MySQL database
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Retrieve user details using the login token
$login_token = $_SESSION['token_id'];
$sql = "SELECT UserID, Email, FullName FROM useraccount WHERE login_token = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $login_token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $user_fullName = $user["FullName"];
    $userid = $user['UserID'];
    $userEmail = $user['Email'];
} else {
    echo "Invalid login token.";
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
    echo "No items in the shopping cart.";
    exit;
}

// Query to get movie screening details and seats in the shopping cart
$sql = "
    SELECT 
        st.ScreenTimeID, st.ScreenTimeDate, st.ScreenTimeCost, c.CinemaHall, m.MovieName, s.SeatNumber
    FROM 
        shoppingscreening AS ss
    JOIN 
        screeningtime2 AS st ON ss.ScreenTimeID = st.ScreenTimeID
    JOIN 
        cinema AS c ON st.SeatingLocation = c.CinemaID
    JOIN 
        movies AS m ON st.ScreeningMovie = m.MovieID
    JOIN 
        seating AS s ON ss.SeatID = s.SeatID
    WHERE 
        ss.ShoppingCartID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $shoppingCartID);
$stmt->execute();
$result = $stmt->get_result();

$cartItems = [];
while ($row = $result->fetch_assoc()) {
    $cartItems[] = $row;
}

$stmt->close();
$conn->close();

//TODO: molest here
// Construct the email body
$emailBody = "Hello $user_fullName! Thank you for purchasing tickets from our website, please check them out under your profile page!\n\nDetails are as follows:\n\n";

// Initialize the HTML table for the email body
$emailBody = "<table border='1'>";
$emailBody .= "<tr>
    <th>ScreenTimeID</th>
    <th>Movie</th>
    <th>Date</th>
    <th>Cost</th>
    <th>Cinema Hall</th>
    <th>Seat Number</th>
</tr>";

// Loop through cart items to populate table rows
foreach ($cartItems as $item) {
    $emailBody .= "<tr>";
    $emailBody .= "<td>" . htmlspecialchars($item['ScreenTimeID']) . "</td>";
    $emailBody .= "<td>" . htmlspecialchars($item['MovieName']) . "</td>";
    $emailBody .= "<td>" . htmlspecialchars($item['ScreenTimeDate']) . "</td>";
    $emailBody .= "<td>$" . htmlspecialchars($item['ScreenTimeCost']) . "</td>";
    $emailBody .= "<td>" . htmlspecialchars($item['CinemaHall']) . "</td>";
    $emailBody .= "<td>" . htmlspecialchars($item['SeatNumber']) . "</td>";
    $emailBody .= "</tr>";
}

// Close the HTML table
$emailBody .= "</table>";

$emailBody .= "<br><img src='images/logo/logo.png' alt='Movie Image' width='200' height='150'>";

// Email settings
$to = 'email2@localhost';
$subject = 'Booking tickets successful!';
$headers = "From: ABC Cinemas <email1@localhost>\r\n";
$headers .= "Reply-To: email1@localhost\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

// Send the email
if (mail($to, $subject, $emailBody, $headers)) {
    echo '<script>alert("Booking Success! Please check your email for confirmation.");</script>';
} else {
    echo "Message could not be sent.";
}
